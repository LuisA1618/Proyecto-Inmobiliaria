<?php
require_once __DIR__ . '/../config/app.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/publicaciones.php');
}

$redirectTo = trim((string) ($_POST['redirect_to'] ?? 'admin/publicaciones.php'));
$allowedRedirects = ['admin/publicaciones.php', 'admin/propiedades.php', 'admin/index.php'];
if (!in_array($redirectTo, $allowedRedirects, true)) {
    $redirectTo = 'admin/publicaciones.php';
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Token de seguridad inválido.');
    redirect($redirectTo);
}

$idPropiedad = (int) ($_POST['id_propiedad'] ?? 0);
$accion      = trim((string) ($_POST['accion'] ?? ''));

if ($idPropiedad <= 0 || !in_array($accion, ['publicar', 'retirar', 'destacar'], true)) {
    set_flash('error', 'Solicitud de publicación inválida.');
    redirect($redirectTo);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id_propiedad, codigo_referencia, titulo, estado_registro FROM propiedades WHERE id_propiedad = ?");
$stmt->execute([$idPropiedad]);
$prop = $stmt->fetch();

if (!$prop) {
    set_flash('error', 'La propiedad seleccionada no existe.');
    redirect($redirectTo);
}

$idAgenteLogueado = (int) $_SESSION['agente_id'];

try {
    $pdo->beginTransaction();

    if ($accion === 'publicar') {
        $updProp = $pdo->prepare("UPDATE propiedades SET estado_registro = 'Publicada' WHERE id_propiedad = ?");
        $updProp->execute([$idPropiedad]);

        $upsertPub = $pdo->prepare("
            INSERT INTO publicaciones (id_propiedad, id_agente_publicador, estado_publicacion, fecha_publicacion, fecha_retiro)
            VALUES (?, ?, 'Publicada', NOW(), NULL)
            ON DUPLICATE KEY UPDATE
                id_agente_publicador = VALUES(id_agente_publicador),
                estado_publicacion = 'Publicada',
                fecha_publicacion = NOW(),
                fecha_retiro = NULL
        ");
        $upsertPub->execute([$idPropiedad, $idAgenteLogueado]);

        $pdo->commit();
        set_flash('success', "La propiedad {$prop['codigo_referencia']} fue PUBLICADA y ya aparece en el catálogo público.");
    } elseif ($accion === 'retirar') {
        $updProp = $pdo->prepare("UPDATE propiedades SET estado_registro = 'No Publicada' WHERE id_propiedad = ?");
        $updProp->execute([$idPropiedad]);

        $upsertRet = $pdo->prepare("
            INSERT INTO publicaciones (id_propiedad, id_agente_publicador, estado_publicacion, fecha_retiro)
            VALUES (?, ?, 'No Publicada', NOW())
            ON DUPLICATE KEY UPDATE
                estado_publicacion = 'No Publicada',
                fecha_retiro = NOW()
        ");
        $upsertRet->execute([$idPropiedad, $idAgenteLogueado]);

        $pdo->commit();
        set_flash('warning', "La publicación de {$prop['codigo_referencia']} fue RETIRADA de la web sin eliminar el registro de la propiedad.");
    } elseif ($accion === 'destacar') {
        $updDest = $pdo->prepare("UPDATE publicaciones SET destacada = 1 - destacada WHERE id_propiedad = ?");
        $updDest->execute([$idPropiedad]);

        $pdo->commit();
        set_flash('success', "Se actualizó el estado destacado de {$prop['codigo_referencia']}.");
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'Error al actualizar el estado de la publicación: ' . $e->getMessage());
}

redirect($redirectTo);
