<?php
/**
 * Technical del Perú — Helper de Generación de Códigos QR
 * 
 * Utiliza chillerlan/php-qrcode (instalado vía Composer).
 * Genera imágenes QR PNG y las guarda en el directorio de uploads.
 * 
 * @version 1.0.0
 */

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Genera una imagen QR y la guarda en disco.
 * 
 * @param string $data Datos a codificar (URL de verificación)
 * @param string $filename Nombre del archivo sin extensión
 * @param string|null $outputDir Directorio de salida (default: uploads/qrcodes/)
 * @return array ['success' => bool, 'path' => string, 'relative_path' => string, 'error' => string|null]
 */
function generateQRCode(string $data, string $filename, ?string $outputDir = null): array
{
    try {
        // Directorio de salida
        $outputDir = $outputDir ?? UPLOADS_PATH . '/qrcodes';
        
        // Crear directorio si no existe
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $filepath = rtrim($outputDir, '/\\') . '/' . $filename . '.png';

        // Configuración del QR
        $options = new QROptions([
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_M,     // Corrección de errores media
            'scale'        => 10,                  // Tamaño del módulo
            'imageBase64'  => false,
            'quietzoneSize' => 2,                  // Borde blanco
        ]);

        $qrcode = new QRCode($options);
        
        // Generar y guardar
        $imageData = $qrcode->render($data);
        file_put_contents($filepath, $imageData);

        // Ruta relativa para guardar en BD
        $relativePath = 'assets/uploads/qrcodes/' . $filename . '.png';

        return [
            'success'       => true,
            'path'          => $filepath,
            'relative_path' => $relativePath,
            'error'         => null,
        ];
    } catch (\Exception $e) {
        error_log('Error generando QR: ' . $e->getMessage());
        return [
            'success'       => false,
            'path'          => '',
            'relative_path' => '',
            'error'         => 'Error al generar código QR: ' . $e->getMessage(),
        ];
    }
}

/**
 * Genera la URL de verificación de un certificado.
 * 
 * @param string $codigoVerificacion Código único del certificado
 * @return string URL completa de verificación
 */
function getVerificationURL(string $codigoVerificacion): string
{
    return BASE_URL . '/verificar-certificado.php?codigo=' . urlencode($codigoVerificacion);
}
