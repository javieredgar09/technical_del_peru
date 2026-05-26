<?php
/**
 * Technical del Perú — Panel de Certificados (Eliminar)
 * 
 * Controlador que procesa las solicitudes de eliminación de certificados.
 * Valida de forma estricta el token CSRF y el rol del usuario,
 * remueve el registro de la BD y borra físicamente la imagen del QR del disco.
 * 
 * @version 1.0.0
 */

// Cargar configuraciones y helpers
require_once __DIR__ . '/../../../src/config/db.php';
require_once __DIR__ . '/../../../src/config/session.php';
require_once __DIR__ . '/../../../src/helpers/auth_helper.php';
require_once __DIR__ . '/../../../src/helpers/functions_helper.php';
require_once __DIR__ . '/../../../src/models/CertificadoModel.php';

// Iniciar sesión de forma segura
initSession();

// Proteger ruta: solo Administrador y Vendedor pueden borrar
requireRole(['Administrador', 'Vendedor']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        requireCSRF();
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            setFlash('error', 'ID de certificado no válido.');
        } else {
            $model = new CertificadoModel();
            
            // Eliminar de forma atómica (BD + QR físico en disco)
            $success = $model->eliminar($id);
            
            if ($success) {
                setFlash('success', '¡Certificado y código QR eliminados permanentemente con éxito!');
            } else {
                setFlash('error', 'No se pudo eliminar el certificado. Es posible que el registro ya no exista.');
            }
        }
    } catch (Exception $e) {
        setFlash('error', 'Error interno al procesar la eliminación del certificado.');
        error_log('Error al eliminar certificado en admin: ' . $e->getMessage());
    }
} else {
    // Si acceden directamente por GET, denegar y mandar al listado
    setFlash('error', 'Método de solicitud no permitido.');
}

// Redirigir siempre de vuelta al listado
redirect(ADMIN_URL . '/modules/certificados/index.php');
