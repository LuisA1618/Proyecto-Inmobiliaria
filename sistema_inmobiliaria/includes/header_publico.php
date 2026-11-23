<?php
$pageTitle = $pageTitle ?? APP_NAME;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= url('index.php') ?>" class="brand">
                <span class="brand-icon" aria-hidden="true">&#9670;</span>
                <div class="brand-text">
                    <strong>ALTAVISTA</strong>
                    <span>Inmobiliaria &amp; Bienes Raíces</span>
                </div>
            </a>

            <button class="nav-toggle" id="navToggle" type="button" aria-label="Abrir menú" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>

            <nav class="main-nav" id="mainNav">
                <a href="<?= url('index.php') ?>" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">Inicio</a>
                <a href="<?= url('catalogo.php') ?>" class="nav-link <?= ($currentPage === 'catalogo.php' || $currentPage === 'propiedad.php') ? 'active' : '' ?>">Catálogo de Propiedades</a>
                <a href="<?= url('index.php#nosotros') ?>" class="nav-link">Servicios</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= url('admin/index.php') ?>" class="btn btn-gold btn-sm">Panel Agente (<?= e($_SESSION['agente_nombre'] ?? 'Admin') ?>)</a>
                <?php else: ?>
                    <a href="<?= url('admin/login.php') ?>" class="btn btn-outline-light btn-sm">Acceso Agentes</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="site-main">
