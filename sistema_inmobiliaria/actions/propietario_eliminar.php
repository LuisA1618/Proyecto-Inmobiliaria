<?php
require_once __DIR__ . '/../config/app.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/propietarios.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Token de seguridad inválido.');
    redirect('admin/propietarios.php');
}

$id = (int) ($_POST['id_propietario'] ?? 0);
if ($id <= 0) {
    redirect('admin/propietarios.php');
}

$pdo = getDBConnection();

$chk = $pdo->prepare("SELECT COUNT(*) FROM propiedades WHERE id_propietario = ?");
$chk->execute([$id]);
if ((int) $chk->fetchColumn() > 0) {
    set_flash('error', 'No se puede eliminar este propietario porque tiene propiedades asociadas en el sistema.');
    redirect('admin/propietarios.php');
}

$del = $pdo->prepare("DELETE FROM propietarios WHERE id_propietario = ?");
$del->execute([$id]);

set_flash('success', 'El propietario fue eliminado correctamente.');
redirect('admin/propietarios.php');
