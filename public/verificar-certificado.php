<?php
/**
 * Technical del Perú — Verificación Pública de Certificado
 * 
 * Permite validar la autenticidad e integridad de un certificado 
 * de forma individual. Muestra el estado actual, el código QR de
 * validación, firmas registradas y un botón de descarga en PDF.
 * 
 * @version 1.0.0
 */

// Cargar archivos de configuración y helpers
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/helpers/functions_helper.php';
require_once __DIR__ . '/../src/models/CertificadoModel.php';

// Iniciar sesión
initSession();

$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
$certificado = false;
$firma = null;
$error = '';

if (empty($codigo)) {
    $error = 'No se ha especificado ningún código de verificación para realizar la consulta.';
} else {
    try {
        $model = new CertificadoModel();
        $certificado = $model->obtenerPorCodigo($codigo);
        
        if ($certificado) {
            // Obtener configuración de firma digital activa
            $db = getDB();
            $stmtFirma = $db->query('SELECT * FROM `config_firma` LIMIT 1');
            $firma = $stmtFirma->fetch();
        } else {
            $error = 'El código de verificación ingresado no corresponde a ningún certificado válido de Technical del Perú.';
        }
    } catch (Exception $e) {
        $error = 'Ocurrió un error al procesar la verificación. Inténtelo más tarde.';
        error_log('Error en verificación de código: ' . $e->getMessage());
    }
}

// Configurar título y SEO
$pageTitle = 'Verificación de Certificado | Technical del Perú';
$pageDescription = 'Validación e inspección pública de competencia técnica. Compruebe la vigencia del certificado y descargue el PDF original con firmas digitales.';

// Cargar cabecera pública
require_once __DIR__ . '/../src/views/header.php';
?>

<!-- Subtle radial glows -->
<div class="absolute w-[400px] h-[400px] rounded-full bg-sky-500/5 blur-[120px] top-[-100px] left-[-100px] pointer-events-none"></div>
<div class="absolute w-[450px] h-[450px] rounded-full bg-blue-600/5 blur-[150px] bottom-[-150px] right-[-150px] pointer-events-none"></div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10" id="verification-main-container">

    <?php if ($certificado): ?>
        <?php 
        // Determinar colores y estilos según el estado
        $status = strtolower($certificado['estado']);
        if ($status === 'vigente') {
            $colorClass = 'text-green-400 border-green-500/30';
            $glowBg = 'bg-green-500/5 border border-green-500/20 shadow-[0_0_40px_rgba(34,197,94,0.06)]';
            $iconColor = 'text-green-500 bg-green-500/10 border-green-500/20';
            $statusLabel = 'Vigente y Verificado';
            $sealIcon = '<svg class="w-14 h-14 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
        } elseif ($status === 'vencido') {
            $colorClass = 'text-amber-400 border-amber-500/30';
            $glowBg = 'bg-amber-500/5 border border-amber-500/20 shadow-[0_0_40px_rgba(245,158,11,0.06)]';
            $iconColor = 'text-amber-500 bg-amber-500/10 border-amber-500/20';
            $statusLabel = 'Certificado Vencido';
            $sealIcon = '<svg class="w-14 h-14 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
        } else {
            $colorClass = 'text-red-400 border-red-500/30';
            $glowBg = 'bg-red-500/5 border border-red-500/20 shadow-[0_0_40px_rgba(239,68,68,0.06)]';
            $iconColor = 'text-red-500 bg-red-500/10 border-red-500/20';
            $statusLabel = 'Certificado Anulado';
            $sealIcon = '<svg class="w-14 h-14 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>';
        }
        ?>

        <!-- Certificate View Header -->
        <div class="text-center mb-10" data-aos="fade-up">
            <div class="w-20 h-20 rounded-3xl <?php echo $iconColor; ?> border flex items-center justify-center mx-auto mb-5 shadow-lg">
                <?php echo $sealIcon; ?>
            </div>
            <h1 class="text-2xl sm:text-3xl font-['Outfit'] font-bold text-white tracking-tight">
                <?php echo $statusLabel; ?>
            </h1>
            <p class="text-xs text-gray-500 mt-2 font-mono tracking-widest uppercase">Código Único: <?php echo e($certificado['codigo_verificacion']); ?></p>
        </div>

        <!-- Glassmorphism Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8" data-aos="fade-up" data-aos-delay="100">
            
            <!-- Column 1 & 2: Certificate details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/5 shadow-xl relative overflow-hidden">
                    <div class="absolute w-[200px] h-[200px] rounded-full bg-sky-500/5 blur-[50px] top-[-50px] left-[-50px] pointer-events-none"></div>
                    
                    <h2 class="text-base font-['Outfit'] font-bold text-sky-400 uppercase tracking-wider mb-6 pb-3 border-b border-white/5">Datos del Participante</h2>
                    
                    <div class="grid grid-cols-1 gap-5">
                        <!-- Participant Name -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Nombre del Certificado / Participante</label>
                            <p class="text-xl font-bold text-white"><?php echo e($certificado['nombre_participante']); ?></p>
                        </div>
                        
                        <!-- Training / Skill Type -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Capacitación / Competencia Técnica</label>
                            <p class="text-base font-semibold text-gray-200"><?php echo e($certificado['tipo_certificado']); ?></p>
                        </div>

                        <!-- Company Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-3 border-t border-white/5">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Número de RUC</label>
                                <p class="text-sm font-bold font-mono text-white tracking-wider"><?php echo e($certificado['ruc']); ?></p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Razón Social</label>
                                <p class="text-sm font-semibold text-gray-300"><?php echo e($certificado['razon_social']); ?></p>
                            </div>
                        </div>

                        <!-- Date range info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-3 border-t border-white/5">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Fecha de Emisión</label>
                                <p class="text-sm font-semibold text-gray-300"><?php echo formatDate($certificado['fecha_emision']); ?></p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Fecha de Vencimiento</label>
                                <p class="text-sm font-semibold text-gray-300">
                                    <?php echo $certificado['fecha_vencimiento'] ? formatDate($certificado['fecha_vencimiento']) : 'Sin Vencimiento'; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if (!empty($certificado['descripcion'])): ?>
                        <div class="space-y-1.5 pt-3 border-t border-white/5">
                            <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Detalles Adicionales</label>
                            <p class="text-sm text-gray-400 leading-relaxed"><?php echo e($certificado['descripcion']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Column 3: Safety QR and Signature -->
            <div class="lg:col-span-1 space-y-6">
                <!-- QR validation box -->
                <div class="glass-card rounded-3xl p-6 border border-white/5 text-center flex flex-col items-center justify-center">
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Sello QR de Autenticidad</label>
                    <?php if (!empty($certificado['ruta_qr'])): ?>
                        <div class="p-3 bg-white rounded-2xl shadow-lg shadow-black/20 hover:scale-[1.02] transition-transform duration-300">
                            <img src="<?php echo BASE_URL . '/' . e($certificado['ruta_qr']); ?>" alt="Código QR de verificación" class="w-36 h-36 object-contain">
                        </div>
                    <?php endif; ?>
                    <p class="text-[10px] text-gray-400 mt-4 leading-relaxed max-w-[160px] mx-auto">
                        Este código QR apunta permanentemente a este portal público de auditoría.
                    </p>
                </div>

                <!-- Digital Signature box -->
                <?php if ($firma && !empty($firma['ruta_imagen'])): ?>
                <div class="glass-card rounded-3xl p-6 border border-white/5 text-center flex flex-col items-center justify-center">
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Firma Autorizada</label>
                    
                    <div class="py-2 flex items-center justify-center">
                        <img src="<?php echo BASE_URL . '/' . e($firma['ruta_imagen']); ?>" alt="Firma Digital Autorizada" class="max-h-16 w-auto object-contain filter brightness-110">
                    </div>
                    
                    <div class="border-t border-white/5 pt-2 w-full">
                        <p class="text-xs font-bold text-white"><?php echo e($firma['nombre_firmante']); ?></p>
                        <p class="text-[10px] text-gray-500 mt-0.5"><?php echo e($firma['cargo']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Download CTA bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-6 rounded-2xl bg-white/[0.02] border border-white/5" data-aos="fade-up" data-aos-delay="200">
            <div class="text-center sm:text-left">
                <h4 class="text-sm font-semibold text-white">¿Requiere una copia impresa del documento?</h4>
                <p class="text-xs text-gray-400 mt-0.5">Descargue el certificado original con todas sus firmas incorporadas en formato PDF.</p>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/descargar-certificado.php?codigo=<?php echo urlencode($certificado['codigo_verificacion']); ?>"
               class="px-6 py-3 rounded-xl font-semibold text-sm bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-xl shadow-sky-500/20 hover:shadow-sky-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center gap-2 cursor-pointer w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Descargar Certificado (PDF)
            </a>
        </div>

        <!-- Volver Link -->
        <div class="text-center mt-8" data-aos="fade-up" data-aos-delay="300">
            <a href="<?php echo BASE_URL; ?>/buscar-certificado.php" class="text-xs text-gray-500 hover:text-sky-400 flex items-center justify-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al Validador por RUC
            </a>
        </div>

    <?php else: ?>
        <!-- Error / Invalid code alert -->
        <div class="glass-card rounded-3xl p-10 border border-white/5 text-center flex flex-col items-center justify-center max-w-lg mx-auto" data-aos="fade-up">
            <div class="w-16 h-16 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-400 mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-['Outfit'] font-bold text-white mb-2">Consulta no procesada</h3>
            <p class="text-sm text-gray-400 leading-relaxed mb-6">
                <?php echo e($error); ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-3 w-full">
                <a href="<?php echo BASE_URL; ?>/buscar-certificado.php" class="flex-1 px-5 py-3 rounded-xl font-semibold text-sm bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all flex items-center justify-center gap-1.5">
                    Ir al Buscador
                </a>
                <a href="<?php echo BASE_URL; ?>/" class="flex-1 px-5 py-3 rounded-xl font-semibold text-sm bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-xl shadow-sky-500/20 transition-all flex items-center justify-center">
                    Inicio
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
// Cargar pie de página público
require_once __DIR__ . '/../src/views/footer.php';
?>
