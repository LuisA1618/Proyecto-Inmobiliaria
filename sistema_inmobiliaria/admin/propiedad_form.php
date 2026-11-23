<?php
require_once __DIR__ . '/../config/app.php';

$pdo = getDBConnection();
$id = (int) ($_GET['id'] ?? 0);
$preselectPropietario = (int) ($_GET['id_propietario'] ?? 0);
$propiedad = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM propiedades WHERE id_propiedad = ?");
    $stmt->execute([$id]);
    $propiedad = $stmt->fetch();
    if (!$propiedad) {
        set_flash('error', 'La propiedad solicitada no existe.');
        redirect('admin/propiedades.php');
    }
}

$propietarios = $pdo->query("SELECT id_propietario, nombre, apellido, documento_identidad FROM propietarios ORDER BY nombre ASC, apellido ASC")->fetchAll();
$agentes      = $pdo->query("SELECT id_agente, nombre, apellido, cargo FROM agentes WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();

$selectedProp = $propiedad ? (int) $propiedad['id_propietario'] : $preselectPropietario;
$selectedAg   = $propiedad ? (int) $propiedad['id_agente'] : (int) ($_SESSION['agente_id'] ?? 0);

$pageTitle = $propiedad ? 'Editar Propiedad (' . $propiedad['codigo_referencia'] . ')' : 'Registrar Nueva Propiedad';
require_once __DIR__ . '/../includes/header_admin.php';
?>

<div class="admin-card form-card-container">
    <div class="card-header">
        <div>
            <h2><?= e($pageTitle) ?></h2>
            <p class="card-subtitle">Registra las características del inmueble y asócialo a su propietario y agente encargado (Caso de Uso 1).</p>
        </div>
        <a href="<?= url('admin/propiedades.php') ?>" class="btn btn-outline btn-sm">&larr; Volver a Propiedades</a>
    </div>

    <?php if (empty($propietarios)): ?>
        <div class="alert alert-warning">
            <span>Primero debes registrar al menos un propietario antes de crear una propiedad.</span>
            <a href="<?= url('admin/propietario_form.php') ?>" class="btn btn-primary btn-xs">Registrar Propietario Ahora</a>
        </div>
    <?php endif; ?>

    <form action="<?= url('actions/propiedad_guardar.php') ?>" method="POST" enctype="multipart/form-data" id="propiedadForm" class="admin-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id_propiedad" value="<?= (int) ($propiedad['id_propiedad'] ?? 0) ?>">

        <h3 class="form-section-heading">1. Asociación de Propietario y Agente Encargado</h3>
        <div class="form-grid-2">
            <div class="form-group">
                <label for="id_propietario">
                    Propietario del Inmueble *
                    <a href="<?= url('admin/propietario_form.php') ?>" class="label-action-link">+ Registrar nuevo propietario</a>
                </label>
                <select id="id_propietario" name="id_propietario" required>
                    <option value="">-- Selecciona un propietario --</option>
                    <?php foreach ($propietarios as $pr): ?>
                        <option value="<?= (int) $pr['id_propietario'] ?>" <?= $selectedProp === (int) $pr['id_propietario'] ? 'selected' : '' ?>>
                            <?= e($pr['nombre'] . ' ' . $pr['apellido'] . ' (CI: ' . $pr['documento_identidad'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_agente">Agente Encargado de la Comercialización *</label>
                <select id="id_agente" name="id_agente" required>
                    <option value="">-- Selecciona un agente --</option>
                    <?php foreach ($agentes as $ag): ?>
                        <option value="<?= (int) $ag['id_agente'] ?>" <?= $selectedAg === (int) $ag['id_agente'] ? 'selected' : '' ?>>
                            <?= e($ag['nombre'] . ' ' . $ag['apellido'] . ' — ' . $ag['cargo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h3 class="form-section-heading">2. Información General y Ubicación</h3>
        <div class="form-group">
            <label for="titulo">Título comercial de la propiedad *</label>
            <input type="text" id="titulo" name="titulo" required value="<?= e($propiedad['titulo'] ?? '') ?>" placeholder="Ej. Casa Residencial Moderna con Jardín en Equipetrol">
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label for="tipo_inmueble">Tipo de Inmueble *</label>
                <select id="tipo_inmueble" name="tipo_inmueble" required>
                    <?php
                    $tipos = ['Casa', 'Departamento', 'Terreno', 'Local Comercial', 'Oficina', 'Quinta'];
                    $curTipo = $propiedad['tipo_inmueble'] ?? 'Casa';
                    foreach ($tipos as $t):
                    ?>
                        <option value="<?= e($t) ?>" <?= $curTipo === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tipo_operacion">Tipo de Operación *</label>
                <select id="tipo_operacion" name="tipo_operacion" required>
                    <?php
                    $ops = ['Venta', 'Alquiler', 'Anticrético'];
                    $curOp = $propiedad['tipo_operacion'] ?? 'Venta';
                    foreach ($ops as $o):
                    ?>
                        <option value="<?= e($o) ?>" <?= $curOp === $o ? 'selected' : '' ?>><?= e($o) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="zona_ciudad">Zona / Ciudad *</label>
                <input type="text" id="zona_ciudad" name="zona_ciudad" required value="<?= e($propiedad['zona_ciudad'] ?? '') ?>" placeholder="Ej. Equipetrol, Santa Cruz">
            </div>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección exacta del inmueble *</label>
            <input type="text" id="direccion" name="direccion" required value="<?= e($propiedad['direccion'] ?? '') ?>" placeholder="Ej. Calle Los Pinos #320 entre 2do y 3er Anillo">
        </div>

        <h3 class="form-section-heading">3. Precio y Características Técnicas</h3>
        <div class="form-grid-3">
            <div class="form-group">
                <label for="precio">Precio *</label>
                <input type="number" id="precio" name="precio" step="0.01" min="1" required value="<?= e((string) ($propiedad['precio'] ?? '')) ?>" placeholder="150000.00">
            </div>

            <div class="form-group">
                <label for="moneda">Moneda *</label>
                <select id="moneda" name="moneda" required>
                    <option value="USD" <?= ($propiedad['moneda'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>Dólares Americanos (USD)</option>
                    <option value="BOB" <?= ($propiedad['moneda'] ?? '') === 'BOB' ? 'selected' : '' ?>>Bolivianos (BOB)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="superficie_m2">Superficie Total (m²) *</label>
                <input type="number" id="superficie_m2" name="superficie_m2" step="0.01" min="1" required value="<?= e((string) ($propiedad['superficie_m2'] ?? '')) ?>" placeholder="240.00">
            </div>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label for="habitaciones">Cantidad de Habitaciones *</label>
                <input type="number" id="habitaciones" name="habitaciones" min="0" max="50" required value="<?= e((string) ($propiedad['habitaciones'] ?? '0')) ?>">
            </div>

            <div class="form-group">
                <label for="banos">Cantidad de Baños *</label>
                <input type="number" id="banos" name="banos" min="0" max="30" required value="<?= e((string) ($propiedad['banos'] ?? '0')) ?>">
            </div>

            <div class="form-group">
                <label for="estacionamientos">Estacionamientos / Garajes</label>
                <input type="number" id="estacionamientos" name="estacionamientos" min="0" max="30" value="<?= e((string) ($propiedad['estacionamientos'] ?? '0')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción detallada de la propiedad *</label>
            <textarea id="descripcion" name="descripcion" rows="5" required placeholder="Describe la distribución, acabados, entorno, servicios cercanos y ventajas del inmueble..."><?= e($propiedad['descripcion'] ?? '') ?></textarea>
        </div>

        <h3 class="form-section-heading">4. Fotografía del Inmueble y Estado Inicial</h3>
        <div class="form-grid-2">
            <div class="form-group">
                <label for="imagen">Fotografía principal (JPG, PNG o WEBP - Máx. 5MB)</label>
                <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp">
                <small class="help-text">Si no seleccionas una imagen, se conservará la actual o se asignará una imagen predeterminada.</small>
            </div>

            <div class="image-preview-box">
                <img id="imagePreview" src="<?= property_image_url($propiedad['imagen_principal'] ?? 'no-image.jpg') ?>" alt="Vista previa">
            </div>
        </div>

        <?php if (!$propiedad): ?>
            <div class="checkbox-card">
                <label class="checkbox-label">
                    <input type="checkbox" name="publicar_ahora" value="1">
                    <span>
                        <strong>Publicar inmediatamente en la página web pública</strong><br>
                        <small>Si dejas esta casilla sin marcar, la propiedad se guardará en estado <strong>Registrada</strong> para que puedas publicarla luego desde el módulo de Publicaciones.</small>
                    </span>
                </label>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $propiedad ? 'Actualizar Propiedad' : 'Guardar Propiedad' ?></button>
            <a href="<?= url('admin/propiedades.php') ?>" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer_admin.php'; ?>
