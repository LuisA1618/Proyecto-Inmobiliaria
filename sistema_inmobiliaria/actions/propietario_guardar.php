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

$id        = (int) ($_POST['id_propietario'] ?? 0);
$nombre    = trim((string) ($_POST['nombre'] ?? ''));
$apellido  = trim((string) ($_POST['apellido'] ?? ''));
$documento = trim((string) ($_POST['documento_identidad'] ?? ''));
$telefono  = trim((string) ($_POST['telefono'] ?? ''));
$email     = trim((string) ($_POST['email'] ?? ''));
$direccion = trim((string) ($_POST['direccion_residencia'] ?? ''));
$notas     = trim((string) ($_POST['notas'] ?? ''));

$backUrl = $id > 0 ? 'admin/propietario_form.php?id=' . $id : 'admin/propietario_form.php';

if ($nombre === '' || $apellido === '' || $documento === '' || $telefono === '') {
    set_flash('error', 'Nombre, apellido, documento de identidad y teléfono son campos obligatorios.');
    redirect($backUrl);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'El correo electrónico ingresado no es válido.');
    redirect($backUrl);
}

$pdo = getDBConnection();

$chk = $pdo->prepare("SELECT COUNT(*) FROM propietarios WHERE documento_identidad = ? AND id_propietario != ?");
$chk->execute([$documento, $id]);
if ((int) $chk->fetchColumn() > 0) {
    set_flash('error', 'Ya existe otro propietario registrado con el documento ' . $documento . '.');
    redirect($backUrl);
}

if ($id > 0) {
    $upd = $pdo->prepare("
        UPDATE propietarios
        SET nombre = ?, apellido = ?, documento_identidad = ?, telefono = ?, email = ?, direccion_residencia = ?, notas = ?
        WHERE id_propietario = ?
    ");
    $upd->execute([
        mb_substr($nombre, 0, 100),
        mb_substr($apellido, 0, 100),
        mb_substr($documento, 0, 30),
        mb_substr($telefono, 0, 30),
        $email !== '' ? mb_substr($email, 0, 150) : null,
        $direccion !== '' ? mb_substr($direccion, 0, 255) : null,
        $notas !== '' ? $notas : null,
        $id
    ]);
    set_flash('success', 'Los datos del propietario se actualizaron correctamente.');
} else {
    $ins = $pdo->prepare("
        INSERT INTO propietarios (nombre, apellido, documento_identidad, telefono, email, direccion_residencia, notas)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([
        mb_substr($nombre, 0, 100),
        mb_substr($apellido, 0, 100),
        mb_substr($documento, 0, 30),
        mb_substr($telefono, 0, 30),
        $email !== '' ? mb_substr($email, 0, 150) : null,
        $direccion !== '' ? mb_substr($direccion, 0, 255) : null,
        $notas !== '' ? $notas : null
    ]);
    set_flash('success', 'Propietario registrado correctamente. Ahora puedes asociarle propiedades.');
}

redirect('admin/propietarios.php');
