<?php
require_once __DIR__ . '/../config/app.php';

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_contactado'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $idConsulta = (int) ($_POST['id_consulta'] ?? 0);
        if ($idConsulta > 0) {
            $upd = $pdo->prepare("UPDATE consultas_clientes SET estado = 'Contactado' WHERE id_consulta = ?");
            $upd->execute([$idConsulta]);
            set_flash('success', 'La consulta fue marcada como Contactada.');
        }
    }
    redirect('admin/consultas.php');
}

$pageTitle = 'Consultas de Clientes Interesados';
require_once __DIR__ . '/../includes/header_admin.php';

$consultas = $pdo->query("
    SELECT c.*,
           p.codigo_referencia, p.titulo AS prop_titulo,
           a.nombre AS ag_nombre, a.apellido AS ag_apellido
    FROM consultas_clientes c
    INNER JOIN propiedades p ON p.id_propiedad = c.id_propiedad
    INNER JOIN agentes a ON a.id_agente = c.id_agente
    ORDER BY FIELD(c.estado, 'Pendiente', 'Contactado'), c.fecha_consulta DESC
")->fetchAll();
?>

<div class="admin-card">
    <div class="card-header">
        <div>
            <h2>Bandeja de Consultas Recibidas (<?= count($consultas) ?>)</h2>
            <p class="card-subtitle">Solicitudes enviadas por posibles compradores desde las fichas públicas de propiedades.</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente Interesado</th>
                    <th>Propiedad Consultada</th>
                    <th>Agente Asignado</th>
                    <th>Mensaje</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($consultas)): ?>
                    <tr><td colspan="7" class="text-center">No hay consultas registradas aún.</td></tr>
                <?php else: ?>
                    <?php foreach ($consultas as $c): ?>
                        <?php
                        $replyWa = whatsapp_url($c['telefono_cliente'], $c['telefono_cliente'], "Hola {$c['nombre_cliente']}, te saluda {$c['ag_nombre']} {$c['ag_apellido']} de " . APP_NAME . " respecto a tu consulta sobre {$c['codigo_referencia']}.");
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_consulta'])) ?></td>
                            <td>
                                <strong><?= e($c['nombre_cliente']) ?></strong><br>
                                <small>Tel: <?= e($c['telefono_cliente']) ?></small><br>
                                <small>Email: <?= e($c['email_cliente']) ?></small>
                            </td>
                            <td>
                                <strong><?= e($c['codigo_referencia']) ?></strong><br>
                                <small><?= e($c['prop_titulo']) ?></small>
                            </td>
                            <td><?= e($c['ag_nombre'] . ' ' . $c['ag_apellido']) ?></td>
                            <td class="msg-cell"><?= e($c['mensaje']) ?></td>
                            <td>
                                <span class="status-badge <?= $c['estado'] === 'Pendiente' ? 'status-unpublished' : 'status-published' ?>">
                                    <?= e($c['estado']) ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="<?= e($replyWa) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-xs">WhatsApp</a>
                                <?php if ($c['estado'] === 'Pendiente'): ?>
                                    <form action="<?= url('admin/consultas.php') ?>" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id_consulta" value="<?= (int) $c['id_consulta'] ?>">
                                        <button type="submit" name="marcar_contactado" value="1" class="btn btn-secondary btn-xs">Marcar Contactado</button>
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
