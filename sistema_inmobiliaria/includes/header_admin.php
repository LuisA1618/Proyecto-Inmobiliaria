<?php
require_auth();
$pageTitle = $pageTitle ?? 'Panel Administrativo';
$currentAdminPage = basename($_SERVER['PHP_SELF']);

$pdoNav = getDBConnection();
$stmtPend = $pdoNav->query("SELECT COUNT(*) FROM consultas_clientes WHERE estado = 'Pendiente'");
$pendientesCount = (int) $stmtPend->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Panel <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="<?= url('admin/index.php') ?>" class="sidebar-brand">
                <span class="brand-diamond">&#9670;</span>
                <div>
                    <strong>ALTAVISTA</strong>
                    <small>Gestión Inmobiliaria</small>
                </div>
            </a>
            <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Cerrar menú">&times;</button>
        </div>

        <div class="sidebar-agent-card">
            <div class="agent-avatar"><?= e(mb_strtoupper(mb_substr($_SESSION['agente_nombre'] ?? 'A', 0, 1))) ?></div>
            <div class="agent-meta">
                <strong><?= e(($_SESSION['agente_nombre'] ?? '') . ' ' . ($_SESSION['agente_apellido'] ?? '')) ?></strong>
                <span><?= e($_SESSION['agente_cargo'] ?? 'Asesor Inmobiliario') ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-section-title">PRINCIPAL</span>
            <a href="<?= url('admin/index.php') ?>" class="sidebar-link <?= $currentAdminPage === 'index.php' ? 'active' : '' ?>">
                <span class="nav-ico">&#9638;</span> Panel Principal
            </a>

            <span class="nav-section-title">CARTERA INMOBILIARIA</span>
            <a href="<?= url('admin/propietarios.php') ?>" class="sidebar-link <?= in_array($currentAdminPage, ['propietarios.php', 'propietario_form.php'], true) ? 'active' : '' ?>">
                <span class="nav-ico">&#9823;</span> Propietarios
            </a>
            <a href="<?= url('admin/propiedades.php') ?>" class="sidebar-link <?= in_array($currentAdminPage, ['propiedades.php', 'propiedad_form.php'], true) ? 'active' : '' ?>">
                <span class="nav-ico">&#8962;</span> Propiedades
            </a>
            <a href="<?= url('admin/publicaciones.php') ?>" class="sidebar-link <?= $currentAdminPage === 'publicaciones.php' ? 'active' : '' ?>">
                <span class="nav-ico">&#9733;</span> Publicaciones Web
            </a>

            <span class="nav-section-title">COMERCIAL</span>
            <a href="<?= url('admin/consultas.php') ?>" class="sidebar-link <?= $currentAdminPage === 'consultas.php' ? 'active' : '' ?>">
                <span class="nav-ico">&#9993;</span> Consultas Clientes
                <?php if ($pendientesCount > 0): ?>
                    <span class="nav-badge"><?= $pendientesCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('admin/perfil.php') ?>" class="sidebar-link <?= $currentAdminPage === 'perfil.php' ? 'active' : '' ?>">
                <span class="nav-ico">&#9881;</span> Mi Perfil de Agente
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= url('index.php') ?>" target="_blank" class="btn-sidebar-web">Ver Sitio Público &#8599;</a>
            <a href="<?= url('admin/logout.php') ?>" class="btn-sidebar-logout">Cerrar Sesión</a>
        </div>
    </aside>

    <div class="admin-wrapper">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Menú">&#9776;</button>
                <h1 class="topbar-title"><?= e($pageTitle) ?></h1>
            </div>
            <div class="topbar-right">
                <a href="<?= url('admin/propiedad_form.php') ?>" class="btn btn-primary btn-sm">+ Registrar Propiedad</a>
                <a href="<?= url('admin/propietario_form.php') ?>" class="btn btn-secondary btn-sm">+ Propietario</a>
            </div>
        </header>

        <main class="admin-content">
            <?= render_flash() ?>
