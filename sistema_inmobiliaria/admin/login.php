<?php
require_once __DIR__ . '/../config/app.php';

if (is_logged_in()) {
    redirect('admin/index.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso de Agentes | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-brand-panel">
            <div class="auth-brand-top">
                <a href="<?= url('index.php') ?>" class="auth-back-link">&larr; Volver al sitio público</a>
                <div class="auth-logo">
                    <span>&#9670;</span>
                    <div>
                        <strong>ALTAVISTA</strong>
                        <small>Sistema de Gestión Inmobiliaria</small>
                    </div>
                </div>
                <h1>Panel Administrativo para Agentes</h1>
                <p>Gestiona propietarios, registra inmuebles, controla las publicaciones en el catálogo web y atiende consultas de clientes interesados.</p>
            </div>

            <div class="demo-credentials-box">
                <strong>Credenciales de prueba (XAMPP):</strong>
                <p>Correo: <code>carlos.mendoza@altavista.com</code></p>
                <p>Contraseña: <code>Admin123*</code></p>
                <button type="button" id="fillDemoBtn" class="btn btn-gold btn-sm">Autocompletar acceso de prueba</button>
            </div>
        </div>

        <div class="auth-form-panel">
            <h2>Iniciar Sesión</h2>
            <p class="auth-subtitle">Ingresa tus credenciales de asesor inmobiliario.</p>

            <?= render_flash() ?>

            <form action="<?= url('actions/auth_login.php') ?>" method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="form-group">
                    <label for="email">Correo electrónico corporativo *</label>
                    <input type="email" id="email" name="email" required placeholder="agente@altavista.com" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Ingresar al Panel</button>
            </form>

            <div class="auth-footer-links">
                <p>¿Eres un nuevo agente en la inmobiliaria? <a href="<?= url('admin/registro.php') ?>">Crear cuenta de agente</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('fillDemoBtn')?.addEventListener('click', function () {
            document.getElementById('email').value = 'carlos.mendoza@altavista.com';
            document.getElementById('password').value = 'Admin123*';
        });
    </script>
</body>
</html>
