<?php
/**
 * Technical del Perú — Cierre de Sesión del Panel
 * 
 * Limpia y destruye de forma segura la sesión activa del usuario
 * y registra la acción en los logs de auditoría.
 * 
 * @version 1.0.0
 */

// Cargar configuración y helpers
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/helpers/functions_helper.php';

// Iniciar sesión
initSession();

// Si el usuario estaba logueado, registrar la acción en logs
if (isLoggedIn()) {
    registrarLog('Cierre de sesión', 'El usuario ha cerrado la sesión de forma voluntaria');
}

// Destruir la sesión limpiando cookies de forma segura
destroySession();

// Iniciar sesión temporalmente limpia para poder guardar un mensaje flash de confirmación
initSession();
setFlash('info', 'Ha cerrado sesión correctamente. ¡Hasta pronto!');

// Redirigir al login
redirect(ADMIN_URL . '/login.php');
