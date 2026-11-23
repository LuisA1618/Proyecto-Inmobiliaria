<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Administración de Publicaciones Web';
require_once __DIR__ . '/../includes/header_admin.php';

$pdo = getDBConnection();

$filtroEstado = trim((string) ($_GET['estado'] ?? ''));

$where = [];
$params = [];
if (in_array($filtroEstado, ['Publicada', 'Registrada', 'No Publicada'], true)) {
    $where[] = "p.estado_registro = ?";
    $params[] = $filtroEstado;
}

$sql = "
    SELECT p.*,
           pub.id_publicacion, pub.estado_publicacion, pub.destacada, pub.visitas,
           pub.fecha_publicacion, pub.fecha_retiro, pub.observaciones,
           pr.nombre AS prop_nombre, pr.apellido AS prop_apellido,
           a.nombre AS ag_nombre, a.apellido AS ag_apellido
    FROM propiedades p
    LEFT JOIN publicaciones pub ON pub.id_propiedad = p.id_propiedad
    INNER JOIN propietarios pr ON pr.id_propietario = p.id_propietario
    INNER JOIN agentes a ON a.id_agente = p.id_agente
";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY FIELD(p.estado_registro, 'Publicada', 'Registrada', 'No Publicada'), p.fecha_registro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();

$counts = [
    'Todas'        => 0,
    'Publicada'    => 0,
    'Registrada'   => 0,
    'No Publicada' => 0,
];
$stmtCounts = $pdo->query("SELECT estado_registro, COUNT(*) AS total FROM propiedades GROUP BY estado_registro");
foreach ($stmtCounts->fetchAll() as $row) {
    $counts[$row['estado_registro']] = (int) $row['total'];
    $counts['Todas'] += (int) $row['total'];
}
?>

<div class="admin-card">
    <div class="card-header">
        <div>
            <h2>Control de Publicaciones en el Portal Web (Caso de Uso 2)</h2>
            <p class="card-subtitle">Publica propiedades registradas para hacerlas visibles al público o retíralas temporalmente sin eliminar su registro.</p>
        </div>
        <a href="<?= url('catalogo.php') ?>" target="_blank" class="btn btn-outline btn-sm">Abrir Catálogo Público &#8599;</a>
    </div>

    <div class="status-tabs">
        <a href="<?= url('admin/publicaciones.php') ?>" class="status-tab <?= $filtroEstado === '' ? 'active' : '' ?>">
            Todas (<?= $counts['Todas'] ?>)
        </a>
        <a href="<?= url('admin/publicaciones.php?estado=Publicada') ?>" class="status-tab <?= $filtroEstado === 'Publicada' ? 'active' : '' ?>">
            Publicadas en Web (<?= $counts['Publicada'] ?>)
        </a>
        <a href="<?= url('admin/publicaciones.php?estado=Registrada') ?>" class="status-tab <?= $filtroEstado === 'Registrada' ? 'active' : '' ?>">
            Registradas Sin Publicar (<?= $counts['Registrada'] ?>)
        </a>
        <a href="<?= url('admin/publicaciones.php?estado=No+Publicada') ?>" class="status-tab <?= $filtroEstado === 'No Publicada' ? 'active' : '' ?>">
            No Publicadas / Retiradas (<?= $counts['No Publicada'] ?>)
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Inmueble</th>
                    <th>Propietario / Agente</th>
                    <th>Estado Actual</th>
                    <th>Historial de Publicación</th>
                    <th>Visitas</th>
                    <th>Control de Publicación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="7" class="text-center">No hay propiedades en esta categoría.</td></tr>
                <?php else: ?>
                    <?php foreach ($registros as $r): ?>
                        <?php
                        $badgeClass = match ($r['estado_registro']) {
                            'Publicada'    => 'status-published',
                            'No Publicada' => 'status-unpublished',
                            default        => 'status-registered',
                        };
                        ?>
                        <tr>
                            <td><strong><?= e($r['codigo_referencia']) ?></strong></td>
                            <td>
                                <div class="table-prop-cell">
                                    <img src="<?= property_image_url($r['imagen_principal']) ?>" alt="" class="table-thumb">
                                    <div>
                                        <strong><?= e($r['titulo']) ?></strong>
                                        <small><?= e($r['tipo_inmueble']) ?> en <?= e($r['tipo_operacion']) ?> &bull; <strong><?= format_price((float) $r['precio'], $r['moneda']) ?></strong></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>
                                    <strong>Prop.:</strong> <?= e($r['prop_nombre'] . ' ' . $r['prop_apellido']) ?><br>
                                    <strong>Agente:</strong> <?= e($r['ag_nombre'] . ' ' . $r['ag_apellido']) ?>
                                </small>
                            </td>
                            <td>
                                <span class="status-badge <?= $badgeClass ?>"><?= e($r['estado_registro']) ?></span>
                                <?php if ((int) ($r['destacada'] ?? 0) === 1 && $r['estado_registro'] === 'Publicada'): ?>
                                    <br><span class="featured-tag">&#9733; Destacada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <?php if (!empty($r['fecha_publicacion'])): ?>
                                        Publicada: <?= date('d/m/Y H:i', strtotime($r['fecha_publicacion'])) ?><br>
                                    <?php else: ?>
                                        Nunca publicada aún<br>
                                    <?php endif; ?>
                                    <?php if (!empty($r['fecha_retiro']) && $r['estado_registro'] === 'No Publicada'): ?>
                                        <span class="text-warning">Retirada: <?= date('d/m/Y H:i', strtotime($r['fecha_retiro'])) ?></span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td><strong><?= (int) ($r['visitas'] ?? 0) ?></strong></td>
                            <td class="actions-cell">
                                <?php if ($r['estado_registro'] !== 'Publicada'): ?>
                                    <form action="<?= url('actions/publicacion_estado.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propiedad" value="<?= (int) $r['id_propiedad'] ?>">
                                        <input type="hidden" name="accion" value="publicar">
                                        <input type="hidden" name="redirect_to" value="admin/publicaciones.php">
                                        <button type="submit" class="btn btn-success btn-xs">Publicar en Web</button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('actions/publicacion_estado.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propiedad" value="<?= (int) $r['id_propiedad'] ?>">
                                        <input type="hidden" name="accion" value="destacar">
                                        <input type="hidden" name="redirect_to" value="admin/publicaciones.php">
                                        <button type="submit" class="btn btn-outline btn-xs" title="Alternar destacado en portada">
                                            <?= (int) ($r['destacada'] ?? 0) === 1 ? 'Quitar ★' : 'Destacar ★' ?>
                                        </button>
                                    </form>

                                    <form action="<?= url('actions/publicacion_estado.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propiedad" value="<?= (int) $r['id_propiedad'] ?>">
                                        <input type="hidden" name="accion" value="retirar">
                                        <input type="hidden" name="redirect_to" value="admin/publicaciones.php">
                                        <button type="submit" class="btn btn-warning btn-xs">Retirar Publicación</button>
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
