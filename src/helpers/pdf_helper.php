<?php
/**
 * Technical del Perú — Helper de Generación de PDF
 * 
 * Utiliza Dompdf para generar certificados en formato PDF.
 * 
 * @version 1.0.0
 */

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Genera un PDF de certificado y lo envía al navegador para descarga.
 * 
 * @param array $certificado Datos del certificado (desde BD)
 * @param array|null $firma Datos de la firma digital (desde config_firma)
 */
function generarPDFCertificado(array $certificado, ?array $firma = null): void
{
    // Configurar Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Helvetica');
    $options->set('isFontSubsettingEnabled', true);
    
    $dompdf = new Dompdf($options);

    // Generar HTML del certificado
    $html = buildCertificadoHTML($certificado, $firma);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Nombre del archivo
    $filename = 'certificado_' . ($certificado['codigo_verificacion'] ?? 'unknown') . '.pdf';

    // Enviar al navegador
    $dompdf->stream($filename, [
        'Attachment' => true, // true = descarga, false = vista en navegador
    ]);
}

/**
 * Construye el HTML para el PDF del certificado.
 * 
 * @param array $certificado Datos del certificado
 * @param array|null $firma Datos de la firma
 * @return string HTML completo
 */
function buildCertificadoHTML(array $certificado, ?array $firma = null): string
{
    $e = function(?string $str): string {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    };

    // Datos del certificado
    $ruc = $e($certificado['ruc'] ?? '');
    $razonSocial = $e($certificado['razon_social'] ?? '');
    $participante = $e($certificado['nombre_participante'] ?? '');
    $tipo = $e($certificado['tipo_certificado'] ?? '');
    $fechaEmision = isset($certificado['fecha_emision']) 
        ? date('d/m/Y', strtotime($certificado['fecha_emision'])) : '—';
    $fechaVencimiento = isset($certificado['fecha_vencimiento']) 
        ? date('d/m/Y', strtotime($certificado['fecha_vencimiento'])) : '—';
    $codigo = $e($certificado['codigo_verificacion'] ?? '');

    // QR
    $qrPath = '';
    if (!empty($certificado['ruta_qr'])) {
        $qrFullPath = PUBLIC_PATH . '/' . $certificado['ruta_qr'];
        if (file_exists($qrFullPath)) {
            $qrData = base64_encode(file_get_contents($qrFullPath));
            $qrPath = 'data:image/png;base64,' . $qrData;
        }
    }

    // Firma
    $firmaHTML = '';
    if ($firma && !empty($firma['ruta_imagen'])) {
        $firmaFullPath = PUBLIC_PATH . '/' . $firma['ruta_imagen'];
        if (file_exists($firmaFullPath)) {
            $firmaData = base64_encode(file_get_contents($firmaFullPath));
            $firmaSrc = 'data:image/png;base64,' . $firmaData;
            $nombreFirmante = $e($firma['nombre_firmante'] ?? '');
            $cargo = $e($firma['cargo'] ?? '');
            $firmaHTML = <<<FIRMA
            <div style="text-align: center; margin-top: 30px;">
                <img src="{$firmaSrc}" style="max-height: 80px; margin: 0 auto;" alt="Firma">
                <div style="border-top: 1px solid #333; width: 250px; margin: 10px auto 0;">
                    <p style="margin: 5px 0 0; font-weight: bold;">{$nombreFirmante}</p>
                    <p style="margin: 0; color: #666; font-size: 12px;">{$cargo}</p>
                </div>
            </div>
            FIRMA;
        }
    }

    // QR HTML
    $qrHTML = '';
    if ($qrPath) {
        $qrHTML = '<img src="' . $qrPath . '" style="width: 120px; height: 120px;" alt="QR">';
    }

    return <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            @page { margin: 30px 50px; }
            body {
                font-family: Helvetica, Arial, sans-serif;
                color: #1a1a2e;
                margin: 0;
                padding: 0;
            }
            .container {
                border: 3px solid #d97706;
                border-radius: 12px;
                padding: 40px;
                position: relative;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .header h1 {
                color: #d97706;
                font-size: 28px;
                margin: 0 0 5px;
                letter-spacing: 2px;
            }
            .header h2 {
                color: #333;
                font-size: 16px;
                font-weight: normal;
                margin: 0;
            }
            .divider {
                border: none;
                border-top: 2px solid #d97706;
                margin: 20px 0;
            }
            .content {
                display: table;
                width: 100%;
            }
            .data-section {
                display: table-cell;
                vertical-align: top;
                width: 70%;
            }
            .qr-section {
                display: table-cell;
                vertical-align: top;
                width: 30%;
                text-align: center;
            }
            .field {
                margin-bottom: 12px;
            }
            .field label {
                font-size: 11px;
                color: #888;
                text-transform: uppercase;
                letter-spacing: 1px;
                display: block;
                margin-bottom: 2px;
            }
            .field value {
                font-size: 15px;
                font-weight: bold;
                display: block;
            }
            .participant-name {
                font-size: 24px;
                color: #1a1a2e;
                text-align: center;
                margin: 20px 0;
                padding: 15px 0;
                border-top: 1px solid #eee;
                border-bottom: 1px solid #eee;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 10px;
                color: #999;
            }
            .verification {
                font-size: 10px;
                color: #666;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>TECHNICAL DEL PERÚ</h1>
                <h2>Certificado de {$tipo}</h2>
            </div>
            
            <hr class="divider">
            
            <div class="participant-name">{$participante}</div>
            
            <div class="content">
                <div class="data-section">
                    <div class="field">
                        <label>RUC</label>
                        <value>{$ruc}</value>
                    </div>
                    <div class="field">
                        <label>Razón Social</label>
                        <value>{$razonSocial}</value>
                    </div>
                    <div class="field">
                        <label>Tipo de Certificado</label>
                        <value>{$tipo}</value>
                    </div>
                    <div class="field">
                        <label>Fecha de Emisión</label>
                        <value>{$fechaEmision}</value>
                    </div>
                    <div class="field">
                        <label>Fecha de Vencimiento</label>
                        <value>{$fechaVencimiento}</value>
                    </div>
                </div>
                <div class="qr-section">
                    {$qrHTML}
                    <div class="verification">
                        <p>Código: {$codigo}</p>
                        <p>Escanea el QR para verificar</p>
                    </div>
                </div>
            </div>

            {$firmaHTML}

            <div class="footer">
                <p>Este certificado es válido y verificable en technicaldelperu.pe</p>
            </div>
        </div>
    </body>
    </html>
    HTML;
}
