<?php
require_once __DIR__ . '/../config/app.php';
require_auth();

$pdo = getDBConnection();
$idAgente = (int) $_SESSION['agente_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Token de seguridad inválido.');
        redirect('admin/perfil.php');
    }

    $nombre   = trim((string) ($_POST['nombre'] ?? ''));
    $apellido = trim((string) ($_POST['apellido'] ?? ''));
    $telefono = trim((string) ($_POST['telefono'] ?? ''));
    $whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
    $cargo    = trim((string) ($_POST['cargo'] ?? 'Asesor Inmobiliario'));
    $newPass  = (string) ($_POST['new_password'] ?? '');

    if ($nombre === '' || $apellido === '' || $telefono === '') {
        set_flash('error', 'Nombre, apellido y teléfono son obligatorios.');
        redirect('admin/perfil.php');
    }

    if ($newPass !== '') {
        if (mb_strlen($newPass) < 6) {
            set_flash('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
            redirect('admin/perfil.php');
        }
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE agentes SET nombre = ?, apellido = ?, telefono = ?, whatsapp = ?, cargo = ?, password_hash = ? WHERE id_agente = ?");
        $upd->execute([$nombre, $apellido, $telefono, $whatsapp, $cargo, $hash, $idAgente]);
    } else {
        $upd = $pdo->prepare("UPDATE agentes SET nombre = ?, apellido = ?, telefono = ?, whatsapp = ?, cargo = ? WHERE id_agente = ?");
        $upd->execute([$nombre, $apellido, $telefono, $whatsapp, $cargo, $idAgente]);
    }

    $_SESSION['agente_nombre']   = $nombre;
    $_SESSION['agente_apellido'] = $apellido;
    $_SESSION['agente_cargo']    = $cargo;

    set_flash('success', 'Tus datos de agente se actualizaron correctamente.');
    redirect('admin/perfil.php');
}

$stmt = $pdo->prepare("SELECT * FROM agentes WHERE id_agente = ?");
$stmt->execute([$idAgente]);
$agente = $stmt->fetch();

$pageTitle = 'Mi Perfil de Agente';
require_once __DIR__ . '/../includes/header_admin.php';
?>

<div class="admin-card form-card-container">
    <div class="card-header">
        <div>
            <h2>Datos Públicos de Contacto del Agente</h2>
            <p class="card-subtitle">Esta información se muestra a los compradores en las propiedades que tienes asignadas.</p>
        </div>
    </div>

    <form action="<?= url('admin/perfil.php') ?>" method="POST" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-grid-2">
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" required value="<?= e($agente['nombre']) ?>">
            </div>
            <div class="form-group">
                <label for="apellido">Apellido *</label>
                <input type="text" id="apellido" name="apellido" required value="<?= e($agente['apellido']) ?>">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="email">Correo electrónico (no editable)</label>
                <input type="email" id="email" value="<?= e($agente['email']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="cargo">Cargo comercial *</label>
                <input type="text" id="cargo" name="cargo" required value="<?= e($agente['cargo']) ?>">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="telefono">Teléfono directo *</label>
                <input type="text" id="telefono" name="telefono" required value="<?= e($agente['telefono']) ?>">
            </div>
            <div class="form-group">
                <label for="whatsapp">WhatsApp (solo números con código de país)</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= e($agente['whatsapp'] ?? '') ?>" placeholder="59171045821">
            </div>
        </div>

        <div class="form-group">
            <label for="new_password">Nueva contraseña (dejar en blanco para mantener la actual)</label>
            <input type="password" id="new_password" name="new_password" minlength="6" placeholder="Mínimo 6 caracteres">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Perfil</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
