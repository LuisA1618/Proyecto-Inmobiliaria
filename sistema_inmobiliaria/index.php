<?php
require_once __DIR__ . '/config/app.php';

$pdo = getDBConnection();

$totalPublicadas = (int) $pdo->query("
    SELECT COUNT(*)
    FROM propiedades p
    INNER JOIN publicaciones pub ON pub.id_propiedad = p.id_propiedad
    WHERE p.estado_registro = 'Publicada' AND pub.estado_publicacion = 'Publicada'
")->fetchColumn();

$totalAgentes = (int) $pdo->query("SELECT COUNT(*) FROM agentes WHERE activo = 1")->fetchColumn();

$stmtDest = $pdo->query("
    SELECT p.*, pub.fecha_publicacion, pub.destacada,
           a.nombre AS agente_nombre, a.apellido AS agente_apellido, a.telefono AS agente_telefono
    FROM propiedades p
    INNER JOIN publicaciones pub ON pub.id_propiedad = p.id_propiedad
    INNER JOIN agentes a ON a.id_agente = p.id_agente
    WHERE p.estado_registro = 'Publicada' AND pub.estado_publicacion = 'Publicada'
    ORDER BY pub.destacada DESC, pub.fecha_publicacion DESC
    LIMIT 6
");
$propiedadesDestacadas = $stmtDest->fetchAll();

$pageTitle = 'Inicio - Propiedades en Venta y Alquiler';
require_once __DIR__ . '/includes/header_publico.php';
?>

<section class="hero-section" style="background-image: linear-gradient(135deg, rgba(11, 28, 54, 0.88), rgba(20, 52, 94, 0.80)), url('<?= url('assets/img/hero-bg.jpg') ?>');">
    <div class="container hero-content">
        <span class="hero-badge">PORTAL INMOBILIARIO OFICIAL</span>
        <h1>Encuentra tu próxima propiedad con asesoramiento experto</h1>
        <p class="hero-subtitle">
            Explora casas, departamentos, oficinas, locales comerciales y terrenos verificados. Contacta directamente con el agente encargado de cada inmueble.
        </p>

        <form action="<?= url('catalogo.php') ?>" method="GET" class="hero-search-form">
            <div class="search-field">
                <label for="q">Ubicación o palabra clave</label>
                <input type="text" id="q" name="q" placeholder="Ej. Equipetrol, Urubó, piscina, PROP-001...">
            </div>
            <div class="search-field">
                <label for="operacion">Operación</label>
                <select id="operacion" name="operacion">
                    <option value="">Todas</option>
                    <option value="Venta">Venta</option>
                    <option value="Alquiler">Alquiler</option>
                    <option value="Anticrético">Anticrético</option>
                </select>
            </div>
            <div class="search-field">
                <label for="tipo">Tipo de inmueble</label>
                <select id="tipo" name="tipo">
                    <option value="">Todos los tipos</option>
                    <option value="Casa">Casa</option>
                    <option value="Departamento">Departamento</option>
                    <option value="Local Comercial">Local Comercial</option>
                    <option value="Oficina">Oficina</option>
                    <option value="Terreno">Terreno</option>
                    <option value="Quinta">Quinta</option>
                </select>
            </div>
            <div class="search-submit">
                <button type="submit" class="btn btn-gold btn-block">Buscar Inmuebles</button>
            </div>
        </form>

        <div class="hero-metrics">
            <div class="metric-item">
                <strong><?= $totalPublicadas ?></strong>
                <span>Propiedades Publicadas</span>
            </div>
            <div class="metric-item">
                <strong><?= $totalAgentes ?></strong>
                <span>Agentes Especializados</span>
            </div>
            <div class="metric-item">
                <strong>100%</strong>
                <span>Inmuebles Verificados</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-kicker">CATÁLOGO SELECCIONADO</span>
                <h2>Propiedades Publicadas Disponibles</h2>
            </div>
            <a href="<?= url('catalogo.php') ?>" class="btn btn-outline-primary">Ver todo el catálogo &rarr;</a>
        </div>

        <?php if (empty($propiedadesDestacadas)): ?>
            <div class="empty-state">
                <h3>Aún no hay propiedades publicadas</h3>
                <p>Nuestros agentes están preparando nuevas oportunidades inmobiliarias. Vuelve pronto o ingresa al panel administrativo para publicar inmuebles.</p>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($propiedadesDestacadas as $prop): ?>
                    <article class="property-card">
                        <div class="property-media">
                            <a href="<?= url('propiedad.php?id=' . (int) $prop['id_propiedad']) ?>">
                                <img src="<?= property_image_url($prop['imagen_principal']) ?>" alt="<?= e($prop['titulo']) ?>" loading="lazy">
                            </a>
                            <div class="property-badges">
                                <span class="badge badge-op badge-<?= strtolower(str_replace('é', 'e', $prop['tipo_operacion'])) ?>">
                                    <?= e($prop['tipo_operacion']) ?>
                                </span>
                                <span class="badge badge-type"><?= e($prop['tipo_inmueble']) ?></span>
                                <?php if ((int) $prop['destacada'] === 1): ?>
                                    <span class="badge badge-featured">&#9733; Destacada</span>
                                <?php endif; ?>
                            </div>
                            <span class="property-code"><?= e($prop['codigo_referencia']) ?></span>
                        </div>

                        <div class="property-body">
                            <div class="property-price"><?= format_price((float) $prop['precio'], $prop['moneda']) ?></div>
                            <h3 class="property-title">
                                <a href="<?= url('propiedad.php?id=' . (int) $prop['id_propiedad']) ?>">
                                    <?= e($prop['titulo']) ?>
                                </a>
                            </h3>
                            <p class="property-location">&#9906; <?= e($prop['zona_ciudad']) ?> &mdash; <?= e($prop['direccion']) ?></p>

                            <div class="property-specs">
                                <?php if ((int) $prop['habitaciones'] > 0): ?>
                                    <span><strong><?= (int) $prop['habitaciones'] ?></strong> Hab.</span>
                                <?php endif; ?>
                                <?php if ((int) $prop['banos'] > 0): ?>
                                    <span><strong><?= (int) $prop['banos'] ?></strong> Baños</span>
                                <?php endif; ?>
                                <span><strong><?= number_format((float) $prop['superficie_m2'], 0) ?></strong> m²</span>
                                <?php if ((int) $prop['estacionamientos'] > 0): ?>
                                    <span><strong><?= (int) $prop['estacionamientos'] ?></strong> Estac.</span>
                                <?php endif; ?>
                            </div>

                            <div class="property-footer">
                                <div class="agent-mini">
                                    <span class="agent-dot"><?= e(mb_strtoupper(mb_substr($prop['agente_nombre'], 0, 1))) ?></span>
                                    <div>
                                        <small>Agente encargado</small>
                                        <strong><?= e($prop['agente_nombre'] . ' ' . $prop['agente_apellido']) ?></strong>
                                    </div>
                                </div>
                                <a href="<?= url('propiedad.php?id=' . (int) $prop['id_propiedad']) ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt" id="nosotros">
    <div class="container">
        <div class="section-header centered">
            <span class="section-kicker">PROCESO INTEGRAL</span>
            <h2>¿Cómo gestionamos cada operación inmobiliaria?</h2>
            <p>Un flujo transparente diseñado para propietarios y compradores.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">01</div>
                <h3>Registro y Valuación</h3>
                <p>Los propietarios confían su inmueble a nuestros asesores, quienes documentan cada detalle técnico, legal y fotográfico en cartera.</p>
            </div>
            <div class="step-card">
                <div class="step-number">02</div>
                <h3>Publicación Verificada</h3>
                <p>Tras validar la información, el agente publica la propiedad en nuestro catálogo digital con visibilidad inmediata para interesados.</p>
            </div>
            <div class="step-card">
                <div class="step-number">03</div>
                <h3>Contacto Directo</h3>
                <p>Los compradores consultan especificaciones completas y se comunican sin intermediarios con el agente responsable de la venta.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_publico.php'; ?>
