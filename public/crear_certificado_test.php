<?php
/**
 * Script para crear un certificado de prueba real y persistente
 * 
 * Permite al usuario tener datos reales para probar el buscador por RUC,
 * la pantalla de verificación y la descarga del PDF.
 */

require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/models/CertificadoModel.php';

initSession();

try {
    $model = new CertificadoModel();
    
    // RUC de prueba: 20123456789
    $ruc = '20123456789';
    
    // Verificar si ya existe para no duplicar en exceso
    $existentes = $model->obtenerPorRUC($ruc);
    
    if (empty($existentes)) {
        $testData = [
            'ruc'                 => $ruc,
            'razon_social'        => 'INVERSIONES INDUSTRIALES S.A.C.',
            'nombre_participante' => 'CARLOS AUGUSTO ROJAS PEÑA',
            'tipo_certificado'    => 'Operario Especialista de Refugios Mineros Móviles',
            'descripcion'         => 'Aprobado satisfactoriamente con nota 19/20 en el curso teórico-práctico de seguridad, operación extrema y mantenimiento de sistemas integrales de Refugios Mineros Móviles.',
            'fecha_emision'       => '2026-05-26',
            'fecha_vencimiento'   => '2027-05-26',
            'estado'              => 'vigente',
            'creado_por'          => 1 // Administrador inicial
        ];
        
        $id = $model->crear($testData);
        if ($id) {
            $cert = $model->obtenerPorId($id);
            echo "<h1>✅ ¡Certificado de prueba creado con éxito!</h1>";
            echo "<p>Usa los siguientes datos en el buscador:</p>";
            echo "<ul>";
            echo "<li><strong>Número de RUC a buscar:</strong> <code style='font-size:1.2rem;background:#eee;padding:2px 6px;'>{$ruc}</code></li>";
            echo "<li><strong>Participante:</strong> CARLOS AUGUSTO ROJAS PEÑA</li>";
            echo "<li><strong>Código de Verificación:</strong> {$cert['codigo_verificacion']}</li>";
            echo "</ul>";
            echo "<p><a href='" . BASE_URL . "/buscar-certificado.php?ruc={$ruc}'>👉 Haz clic aquí para ir directamente a buscar este RUC</a></p>";
        } else {
            echo "<h1>❌ Error al crear el certificado.</h1>";
        }
    } else {
        $cert = $existentes[0];
        echo "<h1>ℹ️ El certificado de prueba ya existía en la base de datos.</h1>";
        echo "<p>Usa los siguientes datos en el buscador:</p>";
        echo "<ul>";
        echo "<li><strong>Número de RUC a buscar:</strong> <code style='font-size:1.2rem;background:#eee;padding:2px 6px;'>{$ruc}</code></li>";
        echo "<li><strong>Participante:</strong> {$cert['nombre_participante']}</li>";
        echo "<li><strong>Código de Verificación:</strong> {$cert['codigo_verificacion']}</li>";
        echo "</ul>";
        echo "<p><a href='" . BASE_URL . "/buscar-certificado.php?ruc={$ruc}'>👉 Haz clic aquí para ir directamente a buscar este RUC</a></p>";
    }
} catch (Exception $e) {
    echo "<h1>❌ Error de servidor: " . $e->getMessage() . "</h1>";
}
