<?php
require_once __DIR__ . '/../config/app.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/propiedades.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Token de seguridad inválido.');
    redirect('admin/propiedades.php');
}

$id               = (int) ($_POST['id_propiedad'] ?? 0);
$idPropietario    = (int) ($_POST['id_propietario'] ?? 0);
$idAgente         = (int) ($_POST['id_agente'] ?? 0);
$titulo           = trim((string) ($_POST['titulo'] ?? ''));
$tipoInmueble     = trim((string) ($_POST['tipo_inmueble'] ?? ''));
$tipoOperacion    = trim((string) ($_POST['tipo_operacion'] ?? ''));
$zonaCiudad       = trim((string) ($_POST['zona_ciudad'] ?? ''));
$direccion        = trim((string) ($_POST['direccion'] ?? ''));
$precio           = (float) ($_POST['precio'] ?? 0);
$moneda           = trim((string) ($_POST['moneda'] ?? 'USD'));
$superficie       = (float) ($_POST['superficie_m2'] ?? 0);
$habitaciones     = max(0, (int) ($_POST['habitaciones'] ?? 0));
$banos            = max(0, (int) ($_POST['banos'] ?? 0));
$estacionamientos = max(0, (int) ($_POST['estacionamientos'] ?? 0));
$descripcion      = trim((string) ($_POST['descripcion'] ?? ''));
$publicarAhora    = isset($_POST['publicar_ahora']) && $_POST['publicar_ahora'] === '1';

$backUrl = $id > 0 ? 'admin/propiedad_form.php?id=' . $id : 'admin/propiedad_form.php';

$validTipos = ['Casa', 'Departamento', 'Terreno', 'Local Comercial', 'Oficina', 'Quinta'];
$validOps   = ['Venta', 'Alquiler', 'Anticrético'];
$validMon   = ['USD', 'BOB'];

if (
    $idPropietario <= 0 ||
    $idAgente <= 0 ||
    $titulo === '' ||
    !in_array($tipoInmueble, $validTipos, true) ||
    !in_array($tipoOperacion, $validOps, true) ||
    $zonaCiudad === '' ||
    $direccion === '' ||
    $precio <= 0 ||
    !in_array($moneda, $validMon, true) ||
    $superficie <= 0 ||
    $descripcion === ''
) {
    set_flash('error', 'Por favor completa correctamente todos los campos obligatorios (*) con valores válidos.');
    redirect($backUrl);
}

$pdo = getDBConnection();

$chkProp = $pdo->prepare("SELECT COUNT(*) FROM propietarios WHERE id_propietario = ?");
$chkProp->execute([$idPropietario]);
if ((int) $chkProp->fetchColumn() === 0) {
    set_flash('error', 'El propietario seleccionado no es válido.');
    redirect($backUrl);
}

$chkAg = $pdo->prepare("SELECT COUNT(*) FROM agentes WHERE id_agente = ? AND activo = 1");
$chkAg->execute([$idAgente]);
if ((int) $chkAg->fetchColumn() === 0) {
    set_flash('error', 'El agente seleccionado no es válido.');
    redirect($backUrl);
}

$imagenFinal = 'no-image.jpg';
if ($id > 0) {
    $stmtCur = $pdo->prepare("SELECT imagen_principal FROM propiedades WHERE id_propiedad = ?");
    $stmtCur->execute([$id]);
    $curRow = $stmtCur->fetch();
    if (!$curRow) {
        set_flash('error', 'La propiedad a editar no existe.');
        redirect('admin/propiedades.php');
    }
    $imagenFinal = $curRow['imagen_principal'];
}

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'Ocurrió un error al subir la fotografía. Verifica el tamaño del archivo.');
        redirect($backUrl);
    }

    if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
        set_flash('error', 'La fotografía excede el tamaño máximo permitido de 5 MB.');
        redirect($backUrl);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedMimes[$mime])) {
        set_flash('error', 'Formato de imagen no permitido. Solo se aceptan archivos JPG, PNG o WEBP.');
        redirect($backUrl);
    }

    $uploadDir = ROOT_PATH . '/uploads/propiedades';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = 'prop_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $allowedMimes[$mime];
    $destPath    = $uploadDir . '/' . $newFileName;

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destPath)) {
        set_flash('error', 'No se pudo guardar la imagen en el servidor.');
        redirect($backUrl);
    }

    $imagenFinal = $newFileName;
}

try {
    $pdo->beginTransaction();

    if ($id > 0) {
        $upd = $pdo->prepare("
            UPDATE propiedades
            SET titulo = ?, tipo_inmueble = ?, tipo_operacion = ?, direccion = ?, zona_ciudad = ?,
                descripcion = ?, precio = ?, moneda = ?, habitaciones = ?, banos = ?,
                superficie_m2 = ?, estacionamientos = ?, imagen_principal = ?,
                id_propietario = ?, id_agente = ?
            WHERE id_propiedad = ?
        ");
        $upd->execute([
            mb_substr($titulo, 0, 180),
            $tipoInmueble,
            $tipoOperacion,
            mb_substr($direccion, 0, 255),
            mb_substr($zonaCiudad, 0, 120),
            $descripcion,
            $precio,
            $moneda,
            $habitaciones,
            $banos,
            $superficie,
            $estacionamientos,
            $imagenFinal,
            $idPropietario,
            $idAgente,
            $id
        ]);

        $pdo->commit();
        set_flash('success', 'La propiedad se actualizó correctamente.');
    } else {
        $nextNum = (int) $pdo->query("SELECT COALESCE(MAX(id_propiedad), 0) + 1 FROM propiedades")->fetchColumn();
        $codigoRef = 'PROP-' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
        $chkCode = $pdo->prepare("SELECT COUNT(*) FROM propiedades WHERE codigo_referencia = ?");
        $chkCode->execute([$codigoRef]);
        if ((int) $chkCode->fetchColumn() > 0) {
            $codigoRef = 'PROP-' . strtoupper(bin2hex(random_bytes(3)));
        }

        $estadoInicial = $publicarAhora ? 'Publicada' : 'Registrada';

        $ins = $pdo->prepare("
            INSERT INTO propiedades (
                codigo_referencia, titulo, tipo_inmueble, tipo_operacion, direccion, zona_ciudad,
                descripcion, precio, moneda, habitaciones, banos, superficie_m2, estacionamientos,
                imagen_principal, estado_registro, id_propietario, id_agente
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $codigoRef,
            mb_substr($titulo, 0, 180),
            $tipoInmueble,
            $tipoOperacion,
            mb_substr($direccion, 0, 255),
            mb_substr($zonaCiudad, 0, 120),
            $descripcion,
            $precio,
            $moneda,
            $habitaciones,
            $banos,
            $superficie,
            $estacionamientos,
            $imagenFinal,
            $estadoInicial,
            $idPropietario,
            $idAgente
        ]);

        $nuevoPropId = (int) $pdo->lastInsertId();

        if ($publicarAhora) {
            $insPub = $pdo->prepare("
                INSERT INTO publicaciones (id_propiedad, id_agente_publicador, estado_publicacion, destacada, fecha_publicacion)
                VALUES (?, ?, 'Publicada', 0, NOW())
            ");
            $insPub->execute([$nuevoPropId, (int) $_SESSION['agente_id']]);
            set_flash('success', "Propiedad {$codigoRef} registrada, asociada al propietario y publicada en la web.");
        } else {
            set_flash('success', "Propiedad {$codigoRef} registrada en estado 'Registrada' y asociada al propietario. Puedes publicarla desde el módulo Publicaciones.");
        }

        $pdo->commit();
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'Error al guardar la propiedad: ' . $e->getMessage());
    redirect($backUrl);
}

redirect('admin/propiedades.php');
