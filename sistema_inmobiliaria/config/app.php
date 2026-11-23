<?php
/**
 * Configuración global de la aplicación, sesiones, rutas dinámicas y helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/La_Paz');

define('APP_NAME', 'Inmobiliaria Altavista');
define('APP_TAGLINE', 'Gestión y Comercialización de Bienes Raíces');

$docRoot  = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'])) : '';
$projRoot = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));

if ($docRoot !== '' && strpos($projRoot, $docRoot) === 0) {
    $basePath = substr($projRoot, strlen($docRoot));
} else {
    $basePath = '';
}
$basePath = '/' . trim($basePath, '/');
if ($basePath === '/') {
    $basePath = '';
}
define('BASE_URL', $basePath);
define('ROOT_PATH', $projRoot);

require_once __DIR__ . '/database.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $clean = ltrim($path, '/');
    return BASE_URL . ($clean !== '' ? '/' . $clean : '/');
}

function property_image_url(?string $filename): string
{
    $safeName = basename((string) $filename);
    if ($safeName !== '' && $safeName !== 'no-image.jpg') {
        $diskPath = ROOT_PATH . '/uploads/propiedades/' . $safeName;
        if (is_file($diskPath)) {
            return url('uploads/propiedades/' . rawurlencode($safeName));
        }
    }
    return url('assets/img/no-image.jpg');
}

function format_price(float $amount, string $currency = 'USD'): string
{
    $symbol = ($currency === 'BOB') ? 'Bs ' : '$ ';
    return $symbol . number_format($amount, 2, '.', ',') . ' ' . e($currency);
}

function whatsapp_url(?string $whatsapp, ?string $telefono, string $message = ''): string
{
    $raw = trim((string) ($whatsapp ?: $telefono));
    $digits = preg_replace('/[^0-9]/', '', $raw);
    if ($digits === '') {
        return '#';
    }
    $query = $message !== '' ? '?text=' . rawurlencode($message) : '';
    return 'https://wa.me/' . $digits . $query;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function render_flash(): string
{
    $flash = get_flash();
    if (!$flash) {
        return '';
    }
    $type = e($flash['type']);
    $msg  = e($flash['message']);
    return "<div class=\"alert alert-{$type}\" role=\"alert\"><span>{$msg}</span><button type=\"button\" class=\"alert-close\" onclick=\"this.parentElement.remove()\" aria-label=\"Cerrar\">&times;</button></div>";
}

function is_logged_in(): bool
{
    return !empty($_SESSION['agente_id']);
}

function require_auth(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Debes iniciar sesión como agente inmobiliario para acceder al panel administrativo.');
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}
