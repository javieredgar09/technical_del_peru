<?php
/**
 * Technical del Perú — Descarga Pública de Certificado (PDF)
 * 
 * Recupera los datos de un certificado específico a través de su
 * código único de verificación, obtiene la firma configurada en
 * la base de datos y genera un documento PDF oficial utilizando Dompdf.
 * 
 * @version 1.0.0
 */

// Cargar archivos de configuración y helpers
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/helpers/functions_helper.php';
require_once __DIR__ . '/../src/helpers/pdf_helper.php';
require_once __DIR__ . '/../src/models/CertificadoModel.php';

// Iniciar sesión
initSession();

$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';

if (empty($codigo)) {
    http_response_code(400);
    die('Error de solicitud: código de verificación ausente.');
}

try {
    // 1. Obtener certificado de la BD
    $model = new CertificadoModel();
    $certificado = $model->obtenerPorCodigo($codigo);
    
    if (!$certificado) {
        http_response_code(404);
        die('Error: Certificado no encontrado en el sistema de validación.');
    }
    
    // 2. Obtener firma digital registrada
    $db = getDB();
    $stmtFirma = $db->query('SELECT * FROM `config_firma` LIMIT 1');
    $firma = $stmtFirma->fetch();
    
    // 3. Generar y transmitir PDF
    generarPDFCertificado($certificado, $firma ?: null);
    
    // Registrar descarga en logs de actividad (opcional)
    if (function_exists('registrarLog')) {
        registrarLog('Descarga de certificado PDF', "Descarga pública del certificado código {$codigo} para RUC {$certificado['ruc']}");
    }
} catch (Exception $e) {
    http_response_code(500);
    die('Error interno del servidor al procesar la descarga de certificado.');
    error_log('Error en descargar-certificado.php: ' . $e->getMessage());
}
