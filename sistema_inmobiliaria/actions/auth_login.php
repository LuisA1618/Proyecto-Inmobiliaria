<?php
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/login.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Token de seguridad inválido. Intenta iniciar sesión nuevamente.');
    redirect('admin/login.php');
}

$email    = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    set_flash('error', 'Por favor ingresa tu correo electrónico y contraseña.');
    redirect('admin/login.php');
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM agentes WHERE email = ? AND activo = 1 LIMIT 1");
$stmt->execute([$email]);
$agente = $stmt->fetch();

if (!$agente || !password_verify($password, $agente['password_hash'])) {
    set_flash('error', 'Credenciales incorrectas. Verifica tu correo y contraseña.');
    redirect('admin/login.php');
}

session_regenerate_id(true);
$_SESSION['agente_id']       = (int) $agente['id_agente'];
$_SESSION['agente_nombre']   = $agente['nombre'];
$_SESSION['agente_apellido'] = $agente['apellido'];
$_SESSION['agente_email']    = $agente['email'];
$_SESSION['agente_cargo']    = $agente['cargo'];

set_flash('success', 'Bienvenido(a) nuevamente, ' . $agente['nombre'] . '.');
redirect('admin/index.php');
