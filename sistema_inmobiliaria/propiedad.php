<?php
require_once __DIR__ . '/config/app.php';

$idPropiedad = (int) ($_GET['id'] ?? 0);
if ($idPropiedad <= 0) {
    redirect('catalogo.php');
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT p.*, pub.id_publicacion, pub.fecha_publicacion, pub.destacada, pub.visitas,
           a.id_agente, a.nombre AS agente_nombre, a.apellido AS agente_apellido,
           a.email AS agente_email, a.telefono AS agente_telefono, a.whatsapp AS agente_whatsapp, a.cargo AS agente_cargo
    FROM propiedades p
    INNER JOIN publicaciones pub ON pub.id_propiedad = p.id_propiedad
    INNER JOIN agentes a ON a.id_agente = p.id_agente
    WHERE p.id_propiedad = ?
      AND p.estado_registro = 'Publicada'
      AND pub.estado_publicacion = 'Publicada'
    LIMIT 1
");
$stmt->execute([$idPropiedad]);
$prop = $stmt->fetch();

if (!$prop) {
    $pageTitle = 'Propiedad no disponible';
    require_once __DIR__ . '/includes/header_publico.php';
    ?>
    <section class="section">
        <div class="container">
            <div class="empty-state">
                <h2>Esta propiedad no se encuentra disponible</h2>
                <p>Es posible que el inmueble haya sido retirado de publicación o el enlace ya no esté activo.</p>
                <a href="<?= url('catalogo.php') ?>" class="btn btn-primary">Volver al Catálogo</a>
            </div>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer_publico.php';
    exit;
}

$updVisitas = $pdo->prepare("UPDATE publicaciones SET visitas = visitas + 1 WHERE id_publicacion = ?");
$updVisitas->execute([(int) $prop['id_publicacion']]);

$stmtSim = $pdo->prepare("
    SELECT p.*, a.nombre AS agente_nombre, a.apellido AS agente_apellido
    FROM propiedades p
    INNER JOIN publicaciones pub ON pub.id_propiedad = p.id_propiedad
    INNER JOIN agentes a ON a.id_agente = p.id_agente
    WHERE p.id_propiedad != ?
      AND p.estado_registro = 'Publicada'
      AND pub.estado_publicacion = 'Publicada'
    ORDER BY (p.tipo_inmueble = ?) DESC, pub.fecha_publicacion DESC
    LIMIT 3
");
$stmtSim->execute([$idPropiedad, $prop['tipo_inmueble']]);
$similares = $stmtSim->fetchAll();

$agenteFull = $prop['agente_nombre'] . ' ' . $prop['agente_apellido'];
$waMensaje = "Hola {$agenteFull}, me interesa la propiedad {$prop['codigo_referencia']} ({$prop['titulo']}) publicada en " . APP_NAME . ". ¿Podría brindarme más información?";
$waHref = whatsapp_url($prop['agente_whatsapp'], $prop['agente_telefono'], $waMensaje);

$pageTitle = $prop['titulo'];
require_once __DIR__ . '/includes/header_publico.php';
?>

<section class="property-detail-section">
    <div class="container">
        <nav class="breadcrumb" aria-label="Ruta de navegación">
            <a href="<?= url('index.php') ?>">Inicio</a>
            <span>/</span>
            <a href="<?= url('catalogo.php') ?>">Catálogo</a>
            <span>/</span>
            <span><?= e($prop['codigo_referencia']) ?></span>
        </nav>

        <?= render_flash() ?>

        <div class="detail-header">
            <div>
                <div class="detail-badges">
                    <span class="badge badge-op badge-<?= strtolower(str_replace('é', 'e', $prop['tipo_operacion'])) ?>">
                        En <?= e($prop['tipo_operacion']) ?>
                    </span>
                    <span class="badge badge-type"><?= e($prop['tipo_inmueble']) ?></span>
                    <span class="badge badge-code-light">Ref: <?= e($prop['codigo_referencia']) ?></span>
                </div>
                <h1><?= e($prop['titulo']) ?></h1>
                <p class="detail-address">&#9906; <?= e($prop['direccion']) ?> &mdash; <strong><?= e($prop['zona_ciudad']) ?></strong></p>
            </div>
            <div class="detail-price-box">
                <small>Precio de <?= e($prop['tipo_operacion']) ?></small>
                <div class="detail-price"><?= format_price((float) $prop['precio'], $prop['moneda']) ?></div>
            </div>
        </div>

        <div class="detail-layout">
            <div class="detail-main">
                <div class="detail-gallery">
                    <img src="<?= property_image_url($prop['imagen_principal']) ?>" alt="<?= e($prop['titulo']) ?>">
                </div>

                <div class="specs-cards">
                    <div class="spec-box">
                        <span class="spec-label">Tipo</span>
                        <strong><?= e($prop['tipo_inmueble']) ?></strong>
                    </div>
                    <div class="spec-box">
                        <span class="spec-label">Superficie</span>
                        <strong><?= number_format((float) $prop['superficie_m2'], 2) ?> m²</strong>
                    </div>
                    <div class="spec-box">
                        <span class="spec-label">Habitaciones</span>
                        <strong><?= (int) $prop['habitaciones'] > 0 ? (int) $prop['habitaciones'] : 'N/A' ?></strong>
                    </div>
                    <div class="spec-box">
                        <span class="spec-label">Baños</span>
                        <strong><?= (int) $prop['banos'] > 0 ? (int) $prop['banos'] : 'N/A' ?></strong>
                    </div>
                    <div class="spec-box">
                        <span class="spec-label">Estacionamientos</span>
                        <strong><?= (int) $prop['estacionamientos'] ?></strong>
                    </div>
                </div>

                <div class="detail-card">
                    <h2>Descripción del Inmueble</h2>
                    <p class="detail-description"><?= nl2br(e($prop['descripcion'])) ?></p>
                    <div class="detail-meta-footer">
                        <span>Publicado el: <strong><?= date('d/m/Y', strtotime($prop['fecha_publicacion'])) ?></strong></span>
                        <span>Visualizaciones: <strong><?= (int) $prop['visitas'] + 1 ?></strong></span>
                    </div>
                </div>
            </div>

            <aside class="detail-sidebar">
                <div class="agent-contact-card">
                    <span class="agent-card-kicker">AGENTE RESPONSABLE DE LA VENTA</span>

                    <div class="agent-profile-row">
                        <div class="agent-avatar-lg">
                            <?= e(mb_strtoupper(mb_substr($prop['agente_nombre'], 0, 1) . mb_substr($prop['agente_apellido'], 0, 1))) ?>
                        </div>
                        <div>
                            <h3><?= e($agenteFull) ?></h3>
                            <p class="agent-role"><?= e($prop['agente_cargo']) ?></p>
                        </div>
                    </div>

                    <div class="agent-direct-info">
                        <div class="contact-item">
                            <span>Teléfono directo:</span>
                            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $prop['agente_telefono'])) ?>">
                                <strong><?= e($prop['agente_telefono']) ?></strong>
                            </a>
                        </div>
                        <div class="contact-item">
                            <span>Correo electrónico:</span>
                            <a href="mailto:<?= e($prop['agente_email']) ?>?subject=<?= rawurlencode('Consulta Propiedad ' . $prop['codigo_referencia']) ?>">
                                <strong><?= e($prop['agente_email']) ?></strong>
                            </a>
                        </div>
                    </div>

                    <div class="agent-quick-buttons">
                        <a href="<?= e($waHref) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp btn-block">
                            Contactar por WhatsApp
                        </a>
                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $prop['agente_telefono'])) ?>" class="btn btn-outline-primary btn-block">
                            Llamar al Agente
                        </a>
                    </div>

                    <hr class="divider">

                    <h4>Enviar consulta al agente</h4>
                    <p class="form-hint">Completa tus datos y el asesor se comunicará contigo a la brevedad.</p>

                    <form action="<?= url('actions/consulta_enviar.php') ?>" method="POST" id="consultaForm" class="inquiry-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id_propiedad" value="<?= (int) $prop['id_propiedad'] ?>">

                        <div class="form-group">
                            <label for="nombre_cliente">Tu nombre completo *</label>
                            <input type="text" id="nombre_cliente" name="nombre_cliente" required placeholder="Ej. Laura Méndez">
                        </div>

                        <div class="form-group">
                            <label for="telefono_cliente">Teléfono / WhatsApp *</label>
                            <input type="tel" id="telefono_cliente" name="telefono_cliente" required placeholder="Ej. +591 70012345">
                        </div>

                        <div class="form-group">
                            <label for="email_cliente">Correo electrónico *</label>
                            <input type="email" id="email_cliente" name="email_cliente" required placeholder="tu.correo@ejemplo.com">
                        </div>

                        <div class="form-group">
                            <label for="mensaje">Mensaje *</label>
                            <textarea id="mensaje" name="mensaje" rows="3" required>Hola, estoy interesado en la propiedad <?= e($prop['codigo_referencia']) ?> (<?= e($prop['titulo']) ?>) y me gustaría coordinar una visita.</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Solicitar Información</button>
                    </form>
                </div>
            </aside>
        </div>

        <?php if (!empty($similares)): ?>
            <div class="similar-section">
                <h2>Otras Propiedades Disponibles</h2>
                <div class="properties-grid">
                    <?php foreach ($similares as $sim): ?>
                        <article class="property-card">
                            <div class="property-media">
                                <a href="<?= url('propiedad.php?id=' . (int) $sim['id_propiedad']) ?>">
                                    <img src="<?= property_image_url($sim['imagen_principal']) ?>" alt="<?= e($sim['titulo']) ?>" loading="lazy">
                                </a>
                                <div class="property-badges">
                                    <span class="badge badge-op badge-<?= strtolower(str_replace('é', 'e', $sim['tipo_operacion'])) ?>">
                                        <?= e($sim['tipo_operacion']) ?>
                                    </span>
                                    <span class="badge badge-type"><?= e($sim['tipo_inmueble']) ?></span>
                                </div>
                            </div>
                            <div class="property-body">
                                <div class="property-price"><?= format_price((float) $sim['precio'], $sim['moneda']) ?></div>
                                <h3 class="property-title">
                                    <a href="<?= url('propiedad.php?id=' . (int) $sim['id_propiedad']) ?>"><?= e($sim['titulo']) ?></a>
                                </h3>
                                <p class="property-location">&#9906; <?= e($sim['zona_ciudad']) ?></p>
                                <div class="property-footer">
                                    <span class="text-muted">Agente: <?= e($sim['agente_nombre'] . ' ' . $sim['agente_apellido']) ?></span>
                                    <a href="<?= url('propiedad.php?id=' . (int) $sim['id_propiedad']) ?>" class="btn btn-primary btn-sm">Ver</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_publico.php'; ?>
