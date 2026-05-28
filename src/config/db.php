<?php
/**
 * Technical del Perú — Conexión a Base de Datos (PDO)
 * 
 * Singleton PDO con configuración de seguridad:
 * - ERRMODE_EXCEPTION para capturar errores
 * - EMULATE_PREPARES = false (prepared statements reales)
 * - FETCH_ASSOC por defecto
 * - Charset UTF8MB4
 * 
 * @version 1.0.0
 */

// Cargar autoloader de Composer globalmente
if (file_exists(dirname(__DIR__, 2) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
}

// ================================================================
// CONSTANTES DE CONFIGURACIÓN
// ================================================================

// Base de datos
// Lee variables de entorno para Railway.app, fallback a localhost para desarrollo
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'technical_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');        // XAMPP default — CAMBIAR EN PRODUCCIÓN
define('DB_CHARSET', 'utf8mb4');

// Rutas del proyecto
define('ROOT_PATH', dirname(__DIR__, 2));     // c:\xampp\htdocs\technical_del_peru
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/assets/uploads');

// URL base dinámica (detecta automáticamente desarrollo local y producción Hostinger)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($scriptName, '/public') !== false) {
    $pos = strpos($scriptName, '/public');
    $basePath = substr($scriptName, 0, $pos + 7);
} else {
    $basePath = '';
}
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Forzar HTTPS en producción para evitar bloqueos por Contenido Mixto (Mixed Content) en Hostinger
$isLocal = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, '192.168.') === 0 || strpos($host, '10.') === 0 || strpos($host, '172.') === 0);
$protocol = $isLocal ? 'http' : 'https';

define('BASE_URL', $protocol . '://' . $host . $basePath);
define('ADMIN_URL', BASE_URL . '/admin');

// Configuración de archivos
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
define('ALLOWED_VIDEO_EXT', ['mp4']);
define('ALLOWED_MODEL_EXT', ['glb', 'gltf']);
define('ALLOWED_DOC_EXT', ['pdf']);
define('ALLOWED_ALL_EXT', array_merge(
    ALLOWED_IMAGE_EXT,
    ALLOWED_VIDEO_EXT,
    ALLOWED_MODEL_EXT,
    ALLOWED_DOC_EXT
));

// ================================================================
// CONEXIÓN PDO (SINGLETON)
// ================================================================

/**
 * Obtiene la conexión PDO singleton.
 * 
 * @return PDO Instancia de conexión a la base de datos
 * @throws PDOException Si la conexión falla
 */
function getDB(): PDO
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
            PDO::ATTR_EMULATE_PREPARES   => false, // Prepared statements reales
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . DB_CHARSET . "' COLLATE 'utf8mb4_unicode_ci'",
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En producción: loggear error y mostrar mensaje genérico
            error_log('Error de conexión a BD: ' . $e->getMessage());
            die('Error de conexión a la base de datos. Por favor, contacte al administrador.');
        }
    }

    return $pdo;
}
