<?php
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('catalogo.php');
}

$idPropiedad = (int) ($_POST['id_propiedad'] ?? 0);

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', 'Token de seguridad inválido. Por favor intenta enviar tu consulta nuevamente.');
    redirect($idPropiedad > 0 ? 'propiedad.php?id=' . $idPropiedad : 'catalogo.php');
}

$nombre   = trim((string) ($_POST['nombre_cliente'] ?? ''));
$telefono = trim((string) ($_POST['telefono_cliente'] ?? ''));
$email    = trim((string) ($_POST['email_cliente'] ?? ''));
$mensaje  = trim((string) ($_POST['mensaje'] ?? ''));

if ($idPropiedad <= 0 || $nombre === '' || $telefono === '' || $email === '' || $mensaje === '') {
    set_flash('error', 'Todos los campos del formulario de contacto son obligatorios.');
    redirect($idPropiedad > 0 ? 'propiedad.php?id=' . $idPropiedad : 'catalogo.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Por favor ingresa un correo electrónico válido.');
    redirect('propiedad.php?id=' . $idPropiedad);
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT p.id_propiedad, p.id_agente
    FROM propiedades p
    INNER JOIN publicaciones pub ON pub.id_propiedad = p.id_propiedad
    WHERE p.id_propiedad = ? AND p.estado_registro = 'Publicada' AND pub.estado_publicacion = 'Publicada'
    LIMIT 1
");
$stmt->execute([$idPropiedad]);
$prop = $stmt->fetch();

if (!$prop) {
    set_flash('error', 'La propiedad seleccionada no se encuentra disponible.');
    redirect('catalogo.php');
}

$ins = $pdo->prepare("
    INSERT INTO consultas_clientes (id_propiedad, id_agente, nombre_cliente, email_cliente, telefono_cliente, mensaje, estado)
    VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')
");
$ins->execute([
    (int) $prop['id_propiedad'],
    (int) $prop['id_agente'],
    mb_substr($nombre, 0, 120),
    mb_substr($email, 0, 150),
    mb_substr($telefono, 0, 30),
    $mensaje
]);

set_flash('success', '¡Tu consulta fue enviada correctamente! El agente responsable se comunicará contigo pronto.');
redirect('propiedad.php?id=' . $idPropiedad);
