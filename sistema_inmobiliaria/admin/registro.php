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
    <title>Registro de Agente | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-brand-panel">
            <div class="auth-brand-top">
                <a href="<?= url('admin/login.php') ?>" class="auth-back-link">&larr; Volver al inicio de sesión</a>
                <div class="auth-logo">
                    <span>&#9670;</span>
                    <div>
                        <strong>ALTAVISTA</strong>
                        <small>Alta de Asesores Comerciales</small>
                    </div>
                </div>
                <h1>Registro de Nuevo Agente</h1>
                <p>Crea tu perfil profesional para registrar propiedades, asociar propietarios y publicar inmuebles en el portal.</p>
            </div>
        </div>

        <div class="auth-form-panel">
            <h2>Crear Cuenta de Agente</h2>
            <p class="auth-subtitle">Completa todos los campos obligatorios.</p>

            <?= render_flash() ?>

            <form action="<?= url('actions/auth_registro.php') ?>" method="POST" id="registroAgenteForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej. Mariana">
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input type="text" id="apellido" name="apellido" required placeholder="Ej. Suarez">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" required placeholder="mariana.suarez@altavista.com">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="telefono">Teléfono de contacto *</label>
                        <input type="text" id="telefono" name="telefono" required placeholder="+591 70011223">
                    </div>
                    <div class="form-group">
                        <label for="whatsapp">WhatsApp (solo números con código)</label>
                        <input type="text" id="whatsapp" name="whatsapp" placeholder="59170011223">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cargo">Cargo en la inmobiliaria</label>
                    <input type="text" id="cargo" name="cargo" value="Asesor Inmobiliario" placeholder="Asesor Inmobiliario">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="password">Contraseña (mín. 6 caracteres) *</label>
                        <input type="password" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmar contraseña *</label>
                        <input type="password" id="password_confirm" name="password_confirm" minlength="6" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Registrar Agente</button>
            </form>

            <div class="auth-footer-links">
                <p>¿Ya tienes una cuenta? <a href="<?= url('admin/login.php') ?>">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>
</body>
</html>
