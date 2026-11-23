<?php
/**
 * Configuración y conexión PDO a la base de datos MySQL (XAMPP)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'inmobiliaria_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Conexión a la Base de Datos</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .box { background: #fff; border-radius: 12px; max-width: 620px; width: 100%; padding: 32px; box-shadow: 0 10px 25px rgba(15,23,42,0.08); border-top: 5px solid #dc2626; }
        h1 { margin-top: 0; font-size: 1.4rem; color: #0f172a; }
        ol { line-height: 1.7; padding-left: 20px; }
        code { background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
        .tech { margin-top: 18px; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; font-size: 0.85rem; color: #991b1b; }
    </style>
</head>
<body>
    <div class="box">
        <h1>No se pudo conectar con MySQL (inmobiliaria_db)</h1>
        <p>Para ejecutar el <strong>Sistema Web de Gestión Inmobiliaria</strong> en XAMPP, verifica lo siguiente:</p>
        <ol>
            <li>Abre el <strong>Panel de Control de XAMPP</strong> e inicia los módulos <strong>Apache</strong> y <strong>MySQL</strong>.</li>
            <li>Ingresa a <code>http://localhost/phpmyadmin</code>.</li>
            <li>Haz clic en la pestaña <strong>Importar</strong> e importa el archivo <code>database/inmobiliaria_db.sql</code> incluido en el proyecto.</li>
            <li>Verifica que los datos en <code>config/database.php</code> coincidan con tu servidor local (usuario <code>root</code> sin contraseña por defecto).</li>
        </ol>
        <div class="tech"><strong>Detalle técnico:</strong> {$errorMsg}</div>
    </div>
</body>
</html>
HTML;
            exit;
        }
    }

    return $pdo;
}
