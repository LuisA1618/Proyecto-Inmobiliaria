<?php
require_once __DIR__ . '/../config/app.php';

$pdo = getDBConnection();
$id = (int) ($_GET['id'] ?? 0);
$propietario = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM propietarios WHERE id_propietario = ?");
    $stmt->execute([$id]);
    $propietario = $stmt->fetch();
    if (!$propietario) {
        set_flash('error', 'El propietario solicitado no existe.');
        redirect('admin/propietarios.php');
    }
}

$pageTitle = $propietario ? 'Editar Propietario' : 'Registrar Nuevo Propietario';
require_once __DIR__ . '/../includes/header_admin.php';
?>

<div class="admin-card form-card-container">
    <div class="card-header">
        <div>
            <h2><?= e($pageTitle) ?></h2>
            <p class="card-subtitle">Completa los datos personales y de contacto del dueño del inmueble (Caso de Uso 1).</p>
        </div>
        <a href="<?= url('admin/propietarios.php') ?>" class="btn btn-outline btn-sm">&larr; Volver a Propietarios</a>
    </div>

    <form action="<?= url('actions/propietario_guardar.php') ?>" method="POST" id="propietarioForm" class="admin-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id_propietario" value="<?= (int) ($propietario['id_propietario'] ?? 0) ?>">

        <div class="form-grid-2">
            <div class="form-group">
                <label for="nombre">Nombre(s) *</label>
                <input type="text" id="nombre" name="nombre" required value="<?= e($propietario['nombre'] ?? '') ?>" placeholder="Ej. Roberto">
            </div>

            <div class="form-group">
                <label for="apellido">Apellido(s) *</label>
                <input type="text" id="apellido" name="apellido" required value="<?= e($propietario['apellido'] ?? '') ?>" placeholder="Ej. Salazar">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="documento_identidad">Documento de Identidad (CI / DNI) *</label>
                <input type="text" id="documento_identidad" name="documento_identidad" required value="<?= e($propietario['documento_identidad'] ?? '') ?>" placeholder="Ej. 4829104 SC">
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono / Celular *</label>
                <input type="text" id="telefono" name="telefono" required value="<?= e($propietario['telefono'] ?? '') ?>" placeholder="Ej. +591 70811223">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="<?= e($propietario['email'] ?? '') ?>" placeholder="propietario@correo.com">
            </div>

            <div class="form-group">
                <label for="direccion_residencia">Dirección de residencia</label>
                <input type="text" id="direccion_residencia" name="direccion_residencia" value="<?= e($propietario['direccion_residencia'] ?? '') ?>" placeholder="Av. Principal #123">
            </div>
        </div>

        <div class="form-group">
            <label for="notas">Observaciones adicionales</label>
            <textarea id="notas" name="notas" rows="3" placeholder="Horarios preferidos de contacto, observaciones legales o comerciales..."><?= e($propietario['notas'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $propietario ? 'Guardar Cambios' : 'Registrar Propietario' ?></button>
            <a href="<?= url('admin/propietarios.php') ?>" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
