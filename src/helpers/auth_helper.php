<?php
/**
 * Technical del Perú — Helpers de Autenticación y Autorización
 * 
 * Funciones para control de acceso, verificación de roles y
 * utilidades de seguridad de output.
 * 
 * @version 1.0.0
 */

// ================================================================
// VERIFICACIÓN DE SESIÓN
// ================================================================

/**
 * Verifica si hay un usuario logueado.
 * 
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Obtiene los datos del usuario actual desde la sesión.
 * 
 * @return array|null Datos del usuario o null si no hay sesión
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id'     => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'] ?? '',
        'email'  => $_SESSION['usuario_email'] ?? '',
        'rol'    => $_SESSION['usuario_rol'] ?? '',
        'rol_id' => $_SESSION['usuario_rol_id'] ?? 0,
    ];
}

/**
 * Requiere que el usuario esté logueado.
 * Redirige a login si no hay sesión activa.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        // Guardar URL actual para redirigir después del login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Requiere que el usuario tenga uno de los roles especificados.
 * 
 * @param string|array $roles Rol o array de roles permitidos
 * @throws Si el usuario no tiene el rol, muestra 403
 */
function requireRole(string|array $roles): void
{
    requireLogin();

    if (is_string($roles)) {
        $roles = [$roles];
    }

    $userRole = $_SESSION['usuario_rol'] ?? '';

    if (!in_array($userRole, $roles, true)) {
        http_response_code(403);
        die('
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"><title>Acceso Denegado</title></head>
        <body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#1a1a2e;color:#fff;">
            <div style="text-align:center;">
                <h1 style="font-size:4rem;margin:0;">403</h1>
                <p style="font-size:1.2rem;opacity:0.8;">No tienes permisos para acceder a esta página.</p>
                <a href="' . ADMIN_URL . '/index.php" style="color:#e94560;text-decoration:none;">← Volver al panel</a>
            </div>
        </body>
        </html>');
    }
}

/**
 * Verifica si el usuario actual tiene un rol específico.
 * 
 * @param string|array $roles Rol o array de roles
 * @return bool
 */
function hasRole(string|array $roles): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    if (is_string($roles)) {
        $roles = [$roles];
    }

    return in_array($_SESSION['usuario_rol'] ?? '', $roles, true);
}

// ================================================================
// UTILIDADES DE SEGURIDAD (OUTPUT)
// ================================================================

/**
 * Escapa una cadena para output HTML seguro.
 * Shorthand para htmlspecialchars().
 * 
 * @param string|null $string Cadena a escapar
 * @return string Cadena escapada
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ================================================================
// LOGGING DE ACTIVIDAD
// ================================================================

/**
 * Registra una acción en la tabla de logs.
 * 
 * @param string $accion Descripción de la acción
 * @param string|null $detalle Detalle adicional
 * @param string|null $tabla Tabla afectada
 * @param int|null $registroId ID del registro afectado
 */
function registrarLog(
    string $accion,
    ?string $detalle = null,
    ?string $tabla = null,
    ?int $registroId = null
): void {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO `logs` (`usuario_id`, `accion`, `detalle`, `tabla_afectada`, `registro_id`, `ip_address`, `user_agent`)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $_SESSION['usuario_id'] ?? null,
            $accion,
            $detalle,
            $tabla,
            $registroId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    } catch (\PDOException $e) {
        // No interrumpir la ejecución si falla el log
        error_log('Error al registrar log: ' . $e->getMessage());
    }
}
