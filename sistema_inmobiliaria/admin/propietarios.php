<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Administración de Propietarios';
require_once __DIR__ . '/../includes/header_admin.php';

$pdo = getDBConnection();
$q = trim((string) ($_GET['q'] ?? ''));

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT pr.*, COUNT(p.id_propiedad) AS total_propiedades
        FROM propietarios pr
        LEFT JOIN propiedades p ON p.id_propietario = pr.id_propietario
        WHERE pr.nombre LIKE ? OR pr.apellido LIKE ? OR pr.documento_identidad LIKE ? OR pr.telefono LIKE ? OR pr.email LIKE ?
        GROUP BY pr.id_propietario
        ORDER BY pr.fecha_registro DESC
    ");
    $stmt->execute([$like, $like, $like, $like, $like]);
} else {
    $stmt = $pdo->query("
        SELECT pr.*, COUNT(p.id_propiedad) AS total_propiedades
        FROM propietarios pr
        LEFT JOIN propiedades p ON p.id_propietario = pr.id_propietario
        GROUP BY pr.id_propietario
        ORDER BY pr.fecha_registro DESC
    ");
}
$propietarios = $stmt->fetchAll();
?>

<div class="admin-card">
    <div class="card-header">
        <div>
            <h2>Propietarios Registrados (<?= count($propietarios) ?>)</h2>
            <p class="card-subtitle">Gestiona los dueños de inmuebles registrados en la inmobiliaria.</p>
        </div>
        <a href="<?= url('admin/propietario_form.php') ?>" class="btn btn-primary">+ Nuevo Propietario</a>
    </div>

    <form action="<?= url('admin/propietarios.php') ?>" method="GET" class="filter-bar">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre, apellido, CI/DNI, teléfono o correo...">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <?php if ($q !== ''): ?>
            <a href="<?= url('admin/propietarios.php') ?>" class="btn btn-outline">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Documento (CI/DNI)</th>
                    <th>Teléfono</th>
                    <th>Correo Electrónico</th>
                    <th>Propiedades</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($propietarios)): ?>
                    <tr><td colspan="7" class="text-center">No se encontraron propietarios registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($propietarios as $pr): ?>
                        <tr>
                            <td>#<?= (int) $pr['id_propietario'] ?></td>
                            <td>
                                <strong><?= e($pr['nombre'] . ' ' . $pr['apellido']) ?></strong>
                                <?php if (!empty($pr['direccion_residencia'])): ?>
                                    <br><small><?= e($pr['direccion_residencia']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><code><?= e($pr['documento_identidad']) ?></code></td>
                            <td><?= e($pr['telefono']) ?></td>
                            <td><?= e($pr['email'] ?: '—') ?></td>
                            <td>
                                <span class="status-badge status-registered">
                                    <?= (int) $pr['total_propiedades'] ?> inmueble(s)
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="<?= url('admin/propiedad_form.php?id_propietario=' . (int) $pr['id_propietario']) ?>" class="btn btn-primary btn-xs" title="Registrar propiedad a este propietario">+ Propiedad</a>
                                <a href="<?= url('admin/propietario_form.php?id=' . (int) $pr['id_propietario']) ?>" class="btn btn-outline btn-xs">Editar</a>
                                <?php if ((int) $pr['total_propiedades'] === 0): ?>
                                    <form action="<?= url('actions/propietario_eliminar.php') ?>" method="POST" class="inline-form" onsubmit="return confirm('¿Deseas eliminar este propietario?');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propietario" value="<?= (int) $pr['id_propietario'] ?>">
                                        <button type="submit" class="btn btn-danger btn-xs">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
