<?php
require_once __DIR__ . '/../config/app.php';

$pageTitle = 'Panel Principal';
require_once __DIR__ . '/../includes/header_admin.php';

$pdo = getDBConnection();

$totalPropiedades  = (int) $pdo->query("SELECT COUNT(*) FROM propiedades")->fetchColumn();
$totalPublicadas   = (int) $pdo->query("SELECT COUNT(*) FROM propiedades WHERE estado_registro = 'Publicada'")->fetchColumn();
$totalRegistradas  = (int) $pdo->query("SELECT COUNT(*) FROM propiedades WHERE estado_registro = 'Registrada'")->fetchColumn();
$totalNoPublicadas = (int) $pdo->query("SELECT COUNT(*) FROM propiedades WHERE estado_registro = 'No Publicada'")->fetchColumn();
$totalPropietarios = (int) $pdo->query("SELECT COUNT(*) FROM propietarios")->fetchColumn();
$totalConsultas    = (int) $pdo->query("SELECT COUNT(*) FROM consultas_clientes WHERE estado = 'Pendiente'")->fetchColumn();

$ultimasPropiedades = $pdo->query("
    SELECT p.*,
           pr.nombre AS prop_nombre, pr.apellido AS prop_apellido,
           a.nombre AS ag_nombre, a.apellido AS ag_apellido
    FROM propiedades p
    INNER JOIN propietarios pr ON pr.id_propietario = p.id_propietario
    INNER JOIN agentes a ON a.id_agente = p.id_agente
    ORDER BY p.fecha_registro DESC
    LIMIT 6
")->fetchAll();

$ultimasConsultas = $pdo->query("
    SELECT c.*, p.codigo_referencia, p.titulo AS prop_titulo
    FROM consultas_clientes c
    INNER JOIN propiedades p ON p.id_propiedad = c.id_propiedad
    ORDER BY c.fecha_consulta DESC
    LIMIT 5
")->fetchAll();
?>

<div class="kpi-grid">
    <div class="kpi-card">
        <span class="kpi-label">Total Propiedades</span>
        <strong class="kpi-value"><?= $totalPropiedades ?></strong>
        <span class="kpi-sub"><?= $totalRegistradas ?> recién registradas</span>
    </div>
    <div class="kpi-card kpi-success">
        <span class="kpi-label">Publicadas en Web</span>
        <strong class="kpi-value"><?= $totalPublicadas ?></strong>
        <span class="kpi-sub">Visibles en catálogo público</span>
    </div>
    <div class="kpi-card kpi-warning">
        <span class="kpi-label">Sin Publicar / Retiradas</span>
        <strong class="kpi-value"><?= $totalRegistradas + $totalNoPublicadas ?></strong>
        <span class="kpi-sub"><?= $totalNoPublicadas ?> retiradas temporalmente</span>
    </div>
    <div class="kpi-card kpi-info">
        <span class="kpi-label">Propietarios</span>
        <strong class="kpi-value"><?= $totalPropietarios ?></strong>
        <span class="kpi-sub">Clientes propietarios activos</span>
    </div>
    <div class="kpi-card kpi-accent">
        <span class="kpi-label">Consultas Pendientes</span>
        <strong class="kpi-value"><?= $totalConsultas ?></strong>
        <span class="kpi-sub">Interesados por contactar</span>
    </div>
</div>

<div class="quick-actions-bar">
    <div>
        <h3>Flujo Rápido de Gestión Inmobiliaria</h3>
        <p>Ejecuta las operaciones principales del sistema desde un solo lugar.</p>
    </div>
    <div class="quick-actions-buttons">
        <a href="<?= url('admin/propietario_form.php') ?>" class="btn btn-secondary">1. Registrar Propietario</a>
        <a href="<?= url('admin/propiedad_form.php') ?>" class="btn btn-primary">2. Registrar Propiedad</a>
        <a href="<?= url('admin/publicaciones.php') ?>" class="btn btn-gold">3. Administrar Publicaciones</a>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h2>Propiedades Recientes en Cartera</h2>
        <a href="<?= url('admin/propiedades.php') ?>" class="btn btn-outline btn-sm">Ver todas &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Inmueble</th>
                    <th>Propietario</th>
                    <th>Agente</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimasPropiedades)): ?>
                    <tr><td colspan="7" class="text-center">No hay propiedades registradas todavía.</td></tr>
                <?php else: ?>
                    <?php foreach ($ultimasPropiedades as $p): ?>
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
                                        <small><?= e($p['tipo_inmueble']) ?> en <?= e($p['tipo_operacion']) ?> &bull; <?= e($p['zona_ciudad']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($p['prop_nombre'] . ' ' . $p['prop_apellido']) ?></td>
                            <td><?= e($p['ag_nombre'] . ' ' . $p['ag_apellido']) ?></td>
                            <td><strong><?= format_price((float) $p['precio'], $p['moneda']) ?></strong></td>
                            <td><span class="status-badge <?= $badgeClass ?>"><?= e($p['estado_registro']) ?></span></td>
                            <td class="actions-cell">
                                <a href="<?= url('admin/propiedad_form.php?id=' . (int) $p['id_propiedad']) ?>" class="btn btn-outline btn-xs">Editar</a>
                                <?php if ($p['estado_registro'] !== 'Publicada'): ?>
                                    <form action="<?= url('actions/publicacion_estado.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propiedad" value="<?= (int) $p['id_propiedad'] ?>">
                                        <input type="hidden" name="accion" value="publicar">
                                        <input type="hidden" name="redirect_to" value="admin/index.php">
                                        <button type="submit" class="btn btn-success btn-xs">Publicar</button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('actions/publicacion_estado.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_propiedad" value="<?= (int) $p['id_propiedad'] ?>">
                                        <input type="hidden" name="accion" value="retirar">
                                        <input type="hidden" name="redirect_to" value="admin/index.php">
                                        <button type="submit" class="btn btn-warning btn-xs">Retirar</button>
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

<div class="admin-card">
    <div class="card-header">
        <h2>Últimas Consultas de Compradores / Interesados</h2>
        <a href="<?= url('admin/consultas.php') ?>" class="btn btn-outline btn-sm">Ver todas &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Interesado</th>
                    <th>Propiedad</th>
                    <th>Mensaje</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimasConsultas)): ?>
                    <tr><td colspan="5" class="text-center">Aún no se han recibido consultas desde la web pública.</td></tr>
                <?php else: ?>
                    <?php foreach ($ultimasConsultas as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_consulta'])) ?></td>
                            <td>
                                <strong><?= e($c['nombre_cliente']) ?></strong><br>
                                <small><?= e($c['telefono_cliente']) ?> &bull; <?= e($c['email_cliente']) ?></small>
                            </td>
                            <td><strong><?= e($c['codigo_referencia']) ?></strong> &mdash; <?= e($c['prop_titulo']) ?></td>
                            <td><?= e(mb_strimwidth($c['mensaje'], 0, 85, '...')) ?></td>
                            <td>
                                <span class="status-badge <?= $c['estado'] === 'Pendiente' ? 'status-unpublished' : 'status-published' ?>">
                                    <?= e($c['estado']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
