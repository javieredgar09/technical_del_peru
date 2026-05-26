<?php
/**
 * Technical del Perú — Buscador Público de Certificados
 * 
 * Permite a los clientes e instituciones verificar y consultar
 * los certificados emitidos mediante la búsqueda por número de RUC.
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

// Variables de control
$searchPerformed = false;
$rucQuery = '';
$error = '';
$certificados = [];

// Procesar solicitud de búsqueda
if (isset($_GET['ruc'])) {
    $rucQuery = trim($_GET['ruc']);
    
    if (empty($rucQuery)) {
        $error = 'Por favor, ingrese un número de RUC.';
    } elseif (!validateRUC($rucQuery)) {
        $error = 'El RUC ingresado no es válido. Debe contener exactamente 11 dígitos numéricos.';
    } else {
        $searchPerformed = true;
        
        try {
            // Instanciar el modelo de certificados
            $model = new CertificadoModel();
            $certificados = $model->obtenerPorRUC($rucQuery);
            
            // Opcional: registrar consulta en logs de auditoría
            if (function_exists('registrarLog')) {
                registrarLog('Consulta pública de RUC', "Se consultaron certificados para el RUC {$rucQuery}. Encontrados: " . count($certificados));
            }
        } catch (Exception $e) {
            $error = 'Ha ocurrido un error al realizar la consulta. Por favor, inténtelo de nuevo más tarde.';
            error_log('Error en búsqueda de RUC: ' . $e->getMessage());
        }
    }
}

// Configurar título y SEO de la página
$pageTitle = 'Buscador de Certificados | Technical del Perú';
$pageDescription = 'Verifique la autenticidad de sus certificados industriales ingresando su número de RUC. Acceso público e instantáneo con firmas digitales.';

// Cargar cabecera pública
require_once __DIR__ . '/../src/views/header.php';
?>

<!-- ============================================================= -->
<!-- BANNER CABECERA -->
<!-- ============================================================= -->
<section class="relative py-20 bg-gray-900 border-b border-white/5 overflow-hidden" id="header-banner">
    <!-- Subtle glow backgrounds -->
    <div class="absolute w-[350px] h-[350px] rounded-full bg-sky-500/5 blur-[100px] top-[-50px] left-[-50px] pointer-events-none"></div>
    <div class="absolute w-[400px] h-[400px] rounded-full bg-blue-600/5 blur-[120px] bottom-[-100px] right-[-100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20 mb-4">
            🛡️ Verificación en Línea
        </span>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-['Outfit'] font-bold text-white tracking-tight">
            Validador de Certificados
        </h1>
        <p class="text-sm sm:text-base text-gray-400 max-w-xl mx-auto mt-4">
            Ingrese el número de RUC de la empresa participante para listar e inspeccionar todos los certificados de aptitud y competencia técnica vigentes.
        </p>
    </div>
</section>

<!-- ============================================================= -->
<!-- BUSCADOR PRINCIPAL -->
<!-- ============================================================= -->
<section class="py-16 relative" id="search-section">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Search Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-10 border border-white/5 shadow-2xl relative overflow-hidden mb-12" data-aos="fade-up">
            <div class="absolute w-[200px] h-[200px] rounded-full bg-sky-500/5 blur-[60px] -bottom-[50px] -left-[50px] pointer-events-none"></div>
            
            <form action="" method="GET" class="space-y-4" id="ruc-search-form" onsubmit="return validateSearchForm()">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <input type="text" id="ruc-input" name="ruc" required maxlength="11" pattern="\d{11}"
                               placeholder="Ingrese RUC de la empresa (11 dígitos)" 
                               value="<?php echo e($rucQuery); ?>"
                               class="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-gray-500 text-base focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300 font-mono tracking-wider">
                    </div>
                    <button type="submit" 
                            class="py-4 px-8 rounded-2xl font-semibold text-base bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-xl shadow-sky-500/20 hover:shadow-sky-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Buscar Certificados
                    </button>
                </div>

                <!-- Client Side error notifications -->
                <div id="js-error-alert" class="hidden bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-sm flex items-start gap-2.5 mt-3 animate-pulse">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span id="js-error-text" class="flex-1"></span>
                </div>

                <!-- Server Side PHP Error alerts -->
                <?php if (!empty($error)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-sm flex items-start gap-2.5 mt-3 animate-pulse" role="alert" id="server-error-alert">
                        <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="flex-1"><?php echo e($error); ?></span>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- ============================================================= -->
        <!-- RESULTADOS DE BÚSQUEDA -->
        <!-- ============================================================= -->
        <?php if ($searchPerformed): ?>
            <div id="results-container" class="space-y-6" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-6">
                    <h2 class="text-xl font-['Outfit'] font-bold text-white">
                        Resultados para el RUC: <span class="font-mono text-sky-400"><?php echo e($rucQuery); ?></span>
                    </h2>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/5 border border-white/10 text-gray-300">
                        <?php echo count($certificados); ?> <?php echo count($certificados) === 1 ? 'encontrado' : 'encontrados'; ?>
                    </span>
                </div>

                <?php if (empty($certificados)): ?>
                    <!-- Clean elegant No Results Card -->
                    <div class="glass-card rounded-3xl p-12 border border-white/5 text-center flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-400 mb-5">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="text-lg font-['Outfit'] font-bold text-white mb-2">No se encontraron registros</h3>
                        <p class="text-sm text-gray-400 max-w-sm leading-relaxed">
                            No poseemos ningún certificado registrado bajo el RUC <span class="font-mono text-sky-400"><?php echo e($rucQuery); ?></span>. Por favor verifique el número ingresado e intente de nuevo.
                        </p>
                    </div>
                <?php else: ?>
                    <!-- Results List Grid -->
                    <div class="grid grid-cols-1 gap-5">
                        <?php foreach ($certificados as $cert): ?>
                            <?php 
                            // Determine glowing badges and state details
                            $status = strtolower($cert['estado']);
                            if ($status === 'vigente') {
                                $statusBadge = 'bg-green-500/10 text-green-400 border-green-500/25 shadow-[0_0_15px_rgba(34,197,94,0.1)]';
                                $statusLabel = 'Vigente';
                            } elseif ($status === 'vencido') {
                                $statusBadge = 'bg-amber-500/10 text-amber-400 border-amber-500/25 shadow-[0_0_15px_rgba(245,158,11,0.1)]';
                                $statusLabel = 'Vencido';
                            } else {
                                $statusBadge = 'bg-red-500/10 text-red-400 border-red-500/25 shadow-[0_0_15px_rgba(239,68,68,0.1)]';
                                $statusLabel = 'Anulado';
                            }
                            ?>
                            <!-- Certificate Premium Card Row -->
                            <div class="glass-card rounded-2xl p-6 border border-white/5 hover:border-sky-500/20 hover:bg-white/[0.04] transition-all duration-300 flex flex-col md:flex-row md:items-center justify-between gap-6 group relative overflow-hidden">
                                
                                <div class="space-y-3 flex-1">
                                    <!-- Participant Name and glowing status -->
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-lg font-['Outfit'] font-bold text-white group-hover:text-sky-300 transition-colors">
                                            <?php echo e($cert['nombre_participante']); ?>
                                        </h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-2xs font-semibold uppercase tracking-wider border <?php echo $statusBadge; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Technical Certificate type details -->
                                    <p class="text-sm font-medium text-gray-300">
                                        <?php echo e($cert['tipo_certificado']); ?>
                                    </p>
                                    
                                    <!-- Company Details & dates info -->
                                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <span class="font-semibold text-gray-400">Razón Social:</span> 
                                            <span><?php echo e($cert['razon_social']); ?></span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>Emisión: <?php echo formatDate($cert['fecha_emision']); ?></span>
                                        </div>
                                        <?php if ($cert['fecha_vencimiento']): ?>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Vence: <?php echo formatDate($cert['fecha_vencimiento']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Verification Link button -->
                                <div class="shrink-0 flex items-center md:justify-end">
                                    <a href="<?php echo BASE_URL; ?>/verificar-certificado.php?codigo=<?php echo urlencode($cert['codigo_verificacion']); ?>" 
                                       class="px-5 py-3 rounded-xl text-sm font-semibold border border-white/10 text-white hover:border-sky-500/50 hover:bg-sky-500/10 hover:text-sky-300 transition-all duration-300 flex items-center gap-2 group-hover:shadow-[0_0_20px_rgba(59,159,231,0.1)]">
                                        Verificar Certificado
                                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                                
                                <!-- Sleek structural gradient line -->
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-sky-500 to-blue-600 transform scale-y-0 group-hover:scale-y-100 transition-transform duration-300 origin-top"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Default Info box welcoming searches -->
            <div class="glass-card rounded-3xl p-10 border border-white/5 text-center flex flex-col items-center justify-center" data-aos="fade-up" data-aos-delay="100">
                <div class="w-14 h-14 rounded-2xl bg-sky-500/10 flex items-center justify-center text-sky-400 mb-5 animate-pulse">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-lg font-['Outfit'] font-bold text-white mb-2">Consulta de Aptitud Técnica</h3>
                <p class="text-sm text-gray-400 max-w-sm leading-relaxed">
                    Nuestros certificados de competencia y aptitud técnica (para operarios de refugios, sistemas contra incendios y estructuras) están completamente digitalizados y firmados. Ingrese el RUC de la empresa contratista para auditar su validez en tiempo real.
                </p>
            </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- ============================================================= -->
<!-- CLIENT SIDE FORM VALIDATIONS JS -->
<!-- ============================================================= -->
<script>
    function validateSearchForm() {
        const rucInput = document.getElementById('ruc-input');
        const jsAlert = document.getElementById('js-error-alert');
        const jsText = document.getElementById('js-error-text');
        
        const ruc = rucInput.value.trim();
        
        // Hide previous errors
        jsAlert.classList.add('hidden');
        
        // Validation check
        if (ruc === '') {
            showError('Por favor, ingrese un número de RUC.');
            return false;
        }
        
        // RUC regex for Peruvian RUC (exactly 11 digits starting with 10, 15, 17, 20, etc.)
        const rucRegex = /^\d{11}$/;
        if (!rucRegex.test(ruc)) {
            showError('El número de RUC no es válido. Debe contener exactamente 11 dígitos numéricos.');
            return false;
        }
        
        return true;
        
        function showError(message) {
            jsText.textContent = message;
            jsAlert.classList.remove('hidden');
            rucInput.focus();
        }
    }
</script>

<?php
// Cargar pie de página público
require_once __DIR__ . '/../src/views/footer.php';
?>
