<?php
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/registro.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Token de seguridad inválido.');
    redirect('admin/registro.php');
}

$nombre   = trim((string) ($_POST['nombre'] ?? ''));
$apellido = trim((string) ($_POST['apellido'] ?? ''));
$email    = trim((string) ($_POST['email'] ?? ''));
$telefono = trim((string) ($_POST['telefono'] ?? ''));
$whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
$cargo    = trim((string) ($_POST['cargo'] ?? 'Asesor Inmobiliario'));
$password = (string) ($_POST['password'] ?? '');
$confirm  = (string) ($_POST['password_confirm'] ?? '');

if ($nombre === '' || $apellido === '' || $email === '' || $telefono === '' || $password === '') {
    set_flash('error', 'Todos los campos marcados con (*) son obligatorios.');
    redirect('admin/registro.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'El formato del correo electrónico no es válido.');
    redirect('admin/registro.php');
}

if (mb_strlen($password) < 6) {
    set_flash('error', 'La contraseña debe tener al menos 6 caracteres.');
    redirect('admin/registro.php');
}

if ($password !== $confirm) {
    set_flash('error', 'Las contraseñas no coinciden.');
    redirect('admin/registro.php');
}

$pdo = getDBConnection();

$chk = $pdo->prepare("SELECT COUNT(*) FROM agentes WHERE email = ?");
$chk->execute([$email]);
if ((int) $chk->fetchColumn() > 0) {
    set_flash('error', 'Ya existe un agente registrado con ese correo electrónico.');
    redirect('admin/registro.php');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($cargo === '') {
    $cargo = 'Asesor Inmobiliario';
}

$ins = $pdo->prepare("
    INSERT INTO agentes (nombre, apellido, email, telefono, whatsapp, cargo, password_hash, activo)
    VALUES (?, ?, ?, ?, ?, ?, ?, 1)
");
$ins->execute([
    mb_substr($nombre, 0, 100),
    mb_substr($apellido, 0, 100),
    mb_substr($email, 0, 150),
    mb_substr($telefono, 0, 30),
    $whatsapp !== '' ? mb_substr($whatsapp, 0, 30) : null,
    mb_substr($cargo, 0, 80),
    $hash
]);

$nuevoId = (int) $pdo->lastInsertId();

session_regenerate_id(true);
$_SESSION['agente_id']       = $nuevoId;
$_SESSION['agente_nombre']   = $nombre;
$_SESSION['agente_apellido'] = $apellido;
$_SESSION['agente_email']    = $email;
$_SESSION['agente_cargo']    = $cargo;

set_flash('success', '¡Cuenta de agente creada con éxito! Ya puedes gestionar propietarios y propiedades.');
redirect('admin/index.php');
