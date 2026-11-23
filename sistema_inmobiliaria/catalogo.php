<?php
require_once __DIR__ . '/config/app.php';

$pdo = getDBConnection();

$q            = trim((string) ($_GET['q'] ?? ''));
$operacion    = trim((string) ($_GET['operacion'] ?? ''));
$tipo         = trim((string) ($_GET['tipo'] ?? ''));
$habitaciones = (int) ($_GET['habitaciones'] ?? 0);
$banos        = (int) ($_GET['banos'] ?? 0);
$precioMin    = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? (float) $_GET['precio_min'] : null;
$precioMax    = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? (float) $_GET['precio_max'] : null;
$orden        = trim((string) ($_GET['orden'] ?? 'recientes'));

$where = [
    "p.estado_registro = 'Publicada'",
    "pub.estado_publicacion = 'Publicada'"
];
$params = [];

if ($q !== '') {
    $where[] = "(p.titulo LIKE ? OR p.direccion LIKE ? OR p.zona_ciudad LIKE ? OR p.codigo_referencia LIKE ? OR p.descripcion LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}

$validOperaciones = ['Venta', 'Alquiler', 'Anticrético'];
if ($operacion !== '' && in_array($operacion, $validOperaciones, true)) {
    $where[] = "p.tipo_operacion = ?";
    $params[] = $operacion;
}

$validTipos = ['Casa', 'Departamento', 'Terreno', 'Local Comercial', 'Oficina', 'Quinta'];
if ($tipo !== '' && in_array($tipo, $validTipos, true)) {
    $where[] = "p.tipo_inmueble = ?";
    $params[] = $tipo;
}

if ($habitaciones > 0) {
    $where[] = "p.habitaciones >= ?";
    $params[] = $habitaciones;
}

if ($banos > 0) {
    $where[] = "p.banos >= ?";
    $params[] = $banos;
}

if ($precioMin !== null && $precioMin >= 0) {
    $where[] = "p.precio >= ?";
    $params[] = $precioMin;
}

if ($precioMax !== null && $precioMax > 0) {
    $where[] = "p.precio <= ?";
    $params[] = $precioMax;
}

$orderSql = match ($orden) {
    'precio_asc'      => 'p.precio ASC',
    'precio_desc'     => 'p.precio DESC',
    'superficie_desc' => 'p.superficie_m2 DESC',
    default           => 'pub.destacada DESC, pub.fecha_publicacion DESC',
};

$sql = "
    SELECT p.*, pub.fecha_publicacion, pub.destacada,
           a.nombre AS agente_nombre, a.apellido AS agente_apellido, a.telefono AS agente_telefono
    FROM propiedades p
    INNER JOIN publicaciones pub ON pub.id_propiedad = p.id_propiedad
    INNER JOIN agentes a ON a.id_agente = p.id_agente
    WHERE " . implode(' AND ', $where) . "
    ORDER BY {$orderSql}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$propiedades = $stmt->fetchAll();

$hayFiltros = ($q !== '' || $operacion !== '' || $tipo !== '' || $habitaciones > 0 || $banos > 0 || $precioMin !== null || $precioMax !== null);

$pageTitle = 'Catálogo de Propiedades Publicadas';
require_once __DIR__ . '/includes/header_publico.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-kicker">EXPLORA NUESTRA CARTERA</span>
        <h1>Catálogo de Propiedades Publicadas</h1>
        <p>Filtra por tipo de operación, inmueble, ubicación o presupuesto y contacta directamente al agente asignado.</p>
    </div>
</section>

<section class="section">
    <div class="container catalog-layout">
        <aside class="catalog-sidebar">
            <form action="<?= url('catalogo.php') ?>" method="GET" class="filter-card">
                <div class="filter-header">
                    <h3>Filtrar Propiedades</h3>
                    <?php if ($hayFiltros): ?>
                        <a href="<?= url('catalogo.php') ?>" class="clear-filters">Limpiar</a>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="f_q">Palabra clave o zona</label>
                    <input type="text" id="f_q" name="q" value="<?= e($q) ?>" placeholder="Ej. Equipetrol, Casa, PROP-001">
                </div>

                <div class="form-group">
                    <label for="f_operacion">Tipo de Operación</label>
                    <select id="f_operacion" name="operacion">
                        <option value="">Todas las operaciones</option>
                        <?php foreach ($validOperaciones as $op): ?>
                            <option value="<?= e($op) ?>" <?= $operacion === $op ? 'selected' : '' ?>><?= e($op) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="f_tipo">Tipo de Inmueble</label>
                    <select id="f_tipo" name="tipo">
                        <option value="">Todos los inmuebles</option>
                        <?php foreach ($validTipos as $tp): ?>
                            <option value="<?= e($tp) ?>" <?= $tipo === $tp ? 'selected' : '' ?>><?= e($tp) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="f_hab">Habitaciones</label>
                        <select id="f_hab" name="habitaciones">
                            <option value="0">Cualquiera</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= $habitaciones === $i ? 'selected' : '' ?>><?= $i ?>+ hab.</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="f_banos">Baños</label>
                        <select id="f_banos" name="banos">
                            <option value="0">Cualquiera</option>
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="<?= $i ?>" <?= $banos === $i ? 'selected' : '' ?>><?= $i ?>+ baños</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="f_pmin">Precio Mínimo</label>
                        <input type="number" id="f_pmin" name="precio_min" min="0" step="100" value="<?= $precioMin !== null ? e((string) $precioMin) : '' ?>" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="f_pmax">Precio Máximo</label>
                        <input type="number" id="f_pmax" name="precio_max" min="0" step="100" value="<?= $precioMax !== null ? e((string) $precioMax) : '' ?>" placeholder="Sin límite">
                    </div>
                </div>

                <div class="form-group">
                    <label for="f_orden">Ordenar resultados</label>
                    <select id="f_orden" name="orden">
                        <option value="recientes" <?= $orden === 'recientes' ? 'selected' : '' ?>>Más recientes y destacadas</option>
                        <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
                        <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                        <option value="superficie_desc" <?= $orden === 'superficie_desc' ? 'selected' : '' ?>>Mayor superficie (m²)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Aplicar Filtros</button>
            </form>
        </aside>

        <div class="catalog-results">
            <div class="results-bar">
                <p>
                    Se encontraron <strong><?= count($propiedades) ?></strong> propiedad(es) publicada(s)
                    <?= $hayFiltros ? ' según los criterios seleccionados.' : ' disponibles actualmente.' ?>
                </p>
            </div>

            <?php if (empty($propiedades)): ?>
                <div class="empty-state">
                    <h3>No se encontraron propiedades con esos filtros</h3>
                    <p>Intenta ampliar el rango de precios o restablecer los filtros de búsqueda para ver toda la cartera disponible.</p>
                    <a href="<?= url('catalogo.php') ?>" class="btn btn-primary">Ver todas las propiedades</a>
                </div>
            <?php else: ?>
                <div class="properties-grid properties-grid-2">
                    <?php foreach ($propiedades as $prop): ?>
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
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_publico.php'; ?>
