<?php
require_once __DIR__ . '/../config/app.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/propiedades.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Token de seguridad inválido.');
    redirect('admin/propiedades.php');
}

$id = (int) ($_POST['id_propiedad'] ?? 0);
if ($id <= 0) {
    redirect('admin/propiedades.php');
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT codigo_referencia, imagen_principal FROM propiedades WHERE id_propiedad = ?");
$stmt->execute([$id]);
$prop = $stmt->fetch();

if (!$prop) {
    set_flash('error', 'La propiedad no existe.');
    redirect('admin/propiedades.php');
}

$del = $pdo->prepare("DELETE FROM propiedades WHERE id_propiedad = ?");
$del->execute([$id]);

$baseSamples = ['no-image.jpg', 'prop_1.jpg', 'prop_2.jpg', 'prop_3.jpg', 'prop_4.jpg', 'prop_5.jpg', 'prop_6.jpg'];
if (!in_array($prop['imagen_principal'], $baseSamples, true)) {
    $filePath = ROOT_PATH . '/uploads/propiedades/' . basename($prop['imagen_principal']);
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

set_flash('success', 'La propiedad ' . $prop['codigo_referencia'] . ' fue eliminada del sistema.');
redirect('admin/propiedades.php');
