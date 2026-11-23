<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Gestión de Propiedades';
require_once __DIR__ . '/../includes/header_admin.php';

$pdo = getDBConnection();

$estado = trim((string) ($_GET['estado'] ?? ''));
$tipo   = trim((string) ($_GET['tipo'] ?? ''));
$q      = trim((string) ($_GET['q'] ?? ''));

$where = [];
$params = [];

$validEstados = ['Registrada', 'Publicada', 'No Publicada'];
if ($estado !== '' && in_array($estado, $validEstados, true)) {
    $where[] = "p.estado_registro = ?";
    $params[] = $estado;
}

$validTipos = ['Casa', 'Departamento', 'Terreno', 'Local Comercial', 'Oficina', 'Quinta'];
if ($tipo !== '' && in_array($tipo, $validTipos, true)) {
    $where[] = "p.tipo_inmueble = ?";
    $params[] = $tipo;
}

if ($q !== '') {
    $where[] = "(p.codigo_referencia LIKE ? OR p.titulo LIKE ? OR p.direccion LIKE ? OR pr.nombre LIKE ? OR pr.apellido LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}

$sql = "
    SELECT p.*,
           pr.nombre AS prop_nombre, pr.apellido AS prop_apellido, pr.telefono AS prop_telefono,
           a.nombre AS ag_nombre, a.apellido AS ag_apellido
    FROM propiedades p
    INNER JOIN propietarios pr ON pr.id_propietario = p.id_propietario
    INNER JOIN agentes a ON a.id_agente = p.id_agente
";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY p.fecha_registro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$propiedades = $stmt->fetchAll();
?>

<div class="admin-card">
    <div class="card-header">
        <div>
            <h2>Cartera de Propiedades (<?= count($propiedades) ?>)</h2>
            <p class="card-subtitle">Visualiza, edita, publica o retira los inmuebles registrados en el sistema.</p>
        </div>
        <a href="<?= url('admin/propiedad_form.php') ?>" class="btn btn-primary">+ Registrar Nueva Propiedad</a>
    </div>

    <form action="<?= url('admin/propiedades.php') ?>" method="GET" class="filter-bar">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Buscar por código, título, dirección o propietario...">
        <select name="estado">
            <option value="">Todos los estados</option>
            <?php foreach ($validEstados as $est): ?>
                <option value="<?= e($est) ?>" <?= $estado === $est ? 'selected' : '' ?>><?= e($est) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="tipo">
            <option value="">Todos los tipos</option>
            <?php foreach ($validTipos as $tp): ?>
                <option value="<?= e($tp) ?>" <?= $tipo === $tp ? 'selected' : '' ?>><?= e($tp) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filtrar</button>
        <?php if ($q !== '' || $estado !== '' || $tipo !== ''): ?>
            <a href="<?= url('admin/propiedades.php') ?>" class="btn btn-outline">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Propiedad</th>
                    <th>Características</th>
                    <th>Propietario Asociado</th>
                    <th>Agente</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($propiedades)): ?>
                    <tr><td colspan="8" class="text-center">No se encontraron propiedades con los filtros aplicados.</td></tr>
                <?php else: ?>
                    <?php foreach ($propiedades as $p): ?>
                        <?php
                        $badgeClass = match ($p['estado_registro']) {
                            'Publicada'    => 'status-published',
                            'No Publicada' => 'status-unpublished',
                            default        => 'status-registered',
                        };
                        ?>
                        <tr>
                            <td><strong><?= e($p['codigo_referencia']) ?></strong></td>
                            <td>
                                <div class="table-prop-cell">
                                    <img src="<?= property_image_url($p['imagen_principal']) ?>" alt="" class="table-thumb">
                                    <div>
                                        <strong><?= e($p['titulo']) ?></strong>
                                        <small><?= e($p['tipo_inmueble']) ?> &bull; <?= e($p['tipo_operacion']) ?> &bull; <?= e($p['direccion']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>
                                    <?= (int) $p['habitaciones'] ?> hab. | <?= (int) $p['banos'] ?> baños<br>
                                    <?= number_format((float) $p['superficie_m2'], 0) ?> m² | <?= (int) $p['estacionamientos'] ?> estac.
                                </small>
                            </td>
                            <td>
                                <strong><?= e($p['prop_nombre'] . ' ' . $p['prop_apellido']) ?></strong><br>
                                <small><?= e($p['prop_telefono']) ?></small>
                            </td>
                            <td><?= e($p['ag_nombre'] . ' ' . $p['ag_apellido']) ?></td>
                            <td><strong><?= format_price((float) $p['precio'], $p['moneda']) ?></strong></td>
                            <td><span class="status-badge <?= $badgeClass ?>"><?= e($p['estado_registro']) ?></span></td>
                            <td class="actions-cell">
                                <?php if ($p['estado_registro'] !== 'Publicada'): ?>
                                    <form action="<?= url('actions/publicacion_estado.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propiedad" value="<?= (int) $p['id_propiedad'] ?>">
                                        <input type="hidden" name="accion" value="publicar">
                                        <input type="hidden" name="redirect_to" value="admin/propiedades.php">
                                        <button type="submit" class="btn btn-success btn-xs">Publicar</button>
                                    </form>
                                <?php else: ?>
                                    <a href="<?= url('propiedad.php?id=' . (int) $p['id_propiedad']) ?>" target="_blank" class="btn btn-outline btn-xs" title="Ver en web">Ver Web</a>
                                    <form action="<?= url('actions/publicacion_estado.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propiedad" value="<?= (int) $p['id_propiedad'] ?>">
                                        <input type="hidden" name="accion" value="retirar">
                                        <input type="hidden" name="redirect_to" value="admin/propiedades.php">
                                        <button type="submit" class="btn btn-warning btn-xs">Retirar</button>
                                    </form>
                                <?php endif; ?>

                                <a href="<?= url('admin/propiedad_form.php?id=' . (int) $p['id_propiedad']) ?>" class="btn btn-secondary btn-xs">Editar</a>

                                <form action="<?= url('actions/propiedad_eliminar.php') ?>" method="POST" class="inline-form" onsubmit="return confirm('¿Estás seguro de eliminar permanentemente esta propiedad? Si solo deseas ocultarla del catálogo, usa el botón Retirar.');">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id_propiedad" value="<?= (int) $p['id_propiedad'] ?>">
                                    <button type="submit" class="btn btn-danger btn-xs">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
