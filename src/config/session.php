<?php
/**
 * Technical del Perú — Configuración de Sesiones Seguras
 * 
 * Seguridad implementada:
 * - cookie_httponly: previene acceso via JavaScript (XSS)
 * - cookie_samesite: previene CSRF via cookies
 * - use_strict_mode: rechaza IDs de sesión no generados por el servidor
 * - CSRF token automático
 * - Timeout por inactividad (30 minutos)
 * 
 * @version 1.0.0
 */

// ================================================================
// CONFIGURACIÓN DE SESIÓN
// ================================================================

// Tiempo máximo de inactividad (30 minutos)
define('SESSION_TIMEOUT', 1800);

/**
 * Inicializa la sesión con configuración segura.
 * Debe llamarse antes de cualquier output HTML.
 */
function initSession(): void
{
    // Forzar el Content-Type HTTP a UTF-8 para garantizar que todos los caracteres especiales peruanos se rendericen correctamente
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        return; // Ya hay una sesión activa
    }

    // Configurar parámetros de sesión antes de session_start()
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_TIMEOUT);

    // En producción con HTTPS, descomentar:
    // ini_set('session.cookie_secure', '1');

    session_name('TECHNICAL_SESSID');
    session_start();

    // Verificar timeout por inactividad
    if (isset($_SESSION['last_activity'])) {
        $inactivo = time() - $_SESSION['last_activity'];
        if ($inactivo > SESSION_TIMEOUT) {
            // Sesión expirada: destruir y redirigir
            session_unset();
            session_destroy();
            session_start(); // Reiniciar limpia
        }
    }
    $_SESSION['last_activity'] = time();

    // Generar CSRF token si no existe
    if (empty($_SESSION['csrf_token'])) {
        regenerateCSRFToken();
    }
}

// ================================================================
// FUNCIONES CSRF
// ================================================================

/**
 * Genera un nuevo CSRF token.
 */
function regenerateCSRFToken(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Obtiene el CSRF token actual.
 * 
 * @return string Token CSRF
 */
function getCSRFToken(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Genera un campo HTML hidden con el CSRF token.
 * 
 * @return string HTML del input hidden
 */
function csrfField(): string
{
    $token = htmlspecialchars(getCSRFToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verifica que el CSRF token del formulario sea válido.
 * 
 * @param string $token Token enviado en el formulario
 * @return bool true si es válido
 */
function verificarCSRF(string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valida CSRF en una petición POST. Termina la ejecución si falla.
 */
function requireCSRF(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verificarCSRF($token)) {
            http_response_code(403);
            die('Error de seguridad: token CSRF inválido. Recargue la página e intente de nuevo.');
        }
        // Regenerar token después de uso válido (one-time use)
        regenerateCSRFToken();
    }
}

// ================================================================
// FUNCIONES DE SESIÓN DE USUARIO
// ================================================================

/**
 * Regenera el ID de sesión (usar después del login).
 * Previene Session Fixation attacks.
 */
function regenerateSession(): void
{
    session_regenerate_id(true);
}

/**
 * Destruye la sesión completamente (logout).
 */
function destroySession(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

// ================================================================
// MENSAJES FLASH
// ================================================================

/**
 * Establece un mensaje flash en la sesión.
 * 
 * @param string $type Tipo: 'success', 'error', 'warning', 'info'
 * @param string $message Mensaje a mostrar
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Obtiene y elimina el mensaje flash actual.
 * 
 * @return array|null Datos del flash o null
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Renderiza el mensaje flash como HTML con Tailwind CSS.
 * 
 * @return string HTML del mensaje flash
 */
function renderFlash(): string
{
    $flash = getFlash();
    if (!$flash) {
        return '';
    }

    $colors = [
        'success' => 'bg-green-100 border-green-500 text-green-800',
        'error'   => 'bg-red-100 border-red-500 text-red-800',
        'warning' => 'bg-yellow-100 border-yellow-500 text-yellow-800',
        'info'    => 'bg-blue-100 border-blue-500 text-blue-800',
    ];

    $icons = [
        'success' => '✓',
        'error'   => '✕',
        'warning' => '⚠',
        'info'    => 'ℹ',
    ];

    $type = $flash['type'];
    $colorClass = $colors[$type] ?? $colors['info'];
    $icon = $icons[$type] ?? $icons['info'];
    $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');

    return <<<HTML
    <div class="border-l-4 p-4 mb-6 rounded-r-lg {$colorClass} flex items-center gap-3" role="alert" id="flash-message">
        <span class="text-xl font-bold">{$icon}</span>
        <p class="flex-1">{$message}</p>
        <button onclick="this.parentElement.remove()" class="text-lg font-bold opacity-60 hover:opacity-100 cursor-pointer">&times;</button>
    </div>
    HTML;
}
