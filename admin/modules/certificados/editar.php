<?php
/**
 * Technical del Perú — Panel de Certificados (Editar)
 * 
 * Permite a los usuarios autorizados (Administrador y Vendedor)
 * editar la información de un certificado registrado previamente.
 * 
 * @version 1.0.0
 */

// Cargar configuraciones y helpers
require_once __DIR__ . '/../../../src/config/db.php';
require_once __DIR__ . '/../../../src/config/session.php';
require_once __DIR__ . '/../../../src/helpers/auth_helper.php';
require_once __DIR__ . '/../../../src/helpers/functions_helper.php';
require_once __DIR__ . '/../../../src/models/CertificadoModel.php';

// Iniciar sesión
initSession();

// Proteger ruta: requiere estar logueado y tener rol apropiado
requireRole(['Administrador', 'Vendedor']);

$pageTitle = 'Editar Certificado';
$error = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('error', 'ID de certificado no válido.');
    redirect(ADMIN_URL . '/modules/certificados/index.php');
}

try {
    $model = new CertificadoModel();
    $cert = $model->obtenerPorId($id);
    
    if (!$cert) {
        setFlash('error', 'El certificado solicitado no existe en el sistema.');
        redirect(ADMIN_URL . '/modules/certificados/index.php');
    }
    
    // Rellenar variables con los valores actuales de la BD
    $ruc = $cert['ruc'];
    $razonSocial = $cert['razon_social'];
    $participante = $cert['nombre_participante'];
    $tipoCertificado = $cert['tipo_certificado'];
    $descripcion = $cert['descripcion'];
    $fechaEmision = $cert['fecha_emision'];
    $fechaVencimiento = $cert['fecha_vencimiento'] ?? '';
    $estado = $cert['estado'];
} catch (Exception $e) {
    error_log('Error al cargar certificado para edición: ' . $e->getMessage());
    setFlash('error', 'Error al cargar los datos del certificado.');
    redirect(ADMIN_URL . '/modules/certificados/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        requireCSRF();
        
        // Recoger y sanitizar datos de entrada
        $ruc = trim($_POST['ruc'] ?? '');
        $razonSocial = sanitizeInput($_POST['razon_social'] ?? '');
        $participante = sanitizeInput($_POST['nombre_participante'] ?? '');
        $tipoCertificado = sanitizeInput($_POST['tipo_certificado'] ?? '');
        $descripcion = sanitizeInput($_POST['descripcion'] ?? '');
        $fechaEmision = $_POST['fecha_emision'] ?? '';
        $fechaVencimiento = $_POST['fecha_vencimiento'] ?? '';
        $estado = $_POST['estado'] ?? 'vigente';
        
        // Validaciones del servidor
        if (empty($ruc) || empty($razonSocial) || empty($participante) || empty($tipoCertificado) || empty($fechaEmision)) {
            $error = 'Por favor, complete todos los campos marcados como obligatorios.';
        } elseif (!validateRUC($ruc)) {
            $error = 'El número de RUC no es válido. Debe contener exactamente 11 dígitos numéricos.';
        } elseif (!empty($fechaVencimiento) && strtotime($fechaVencimiento) < strtotime($fechaEmision)) {
            $error = 'La fecha de vencimiento no puede ser anterior a la fecha de emisión.';
        } else {
            // Asamblear datos
            $datos = [
                'ruc'                 => $ruc,
                'razon_social'        => $razonSocial,
                'nombre_participante' => $participante,
                'tipo_certificado'    => $tipoCertificado,
                'descripcion'         => $descripcion,
                'fecha_emision'       => $fechaEmision,
                'fecha_vencimiento'   => !empty($fechaVencimiento) ? $fechaVencimiento : null,
                'estado'              => $estado
            ];
            
            // Guardar cambios llamando a editar en el modelo
            $editSuccess = $model->editar($id, $datos);
            
            if ($editSuccess) {
                setFlash('success', '¡Excelente! Los cambios en el certificado han sido guardados con éxito.');
                redirect(ADMIN_URL . '/modules/certificados/index.php');
            } else {
                $error = 'Ocurrió un error al actualizar los datos en la base de datos. Intente nuevamente.';
            }
        }
    } catch (Exception $e) {
        $error = 'Ocurrió un error inesperado al procesar la solicitud.';
        error_log('Error al editar certificado en admin: ' . $e->getMessage());
    }
}

// Cargar cabecera del panel admin
require_once __DIR__ . '/../../../src/views/admin_header.php';
?>

<!-- Volver al listado -->
<div class="mb-5">
    <a href="<?php echo ADMIN_URL; ?>/modules/certificados/index.php" class="text-xs text-gray-500 hover:text-sky-400 flex items-center gap-1.5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Volver al Listado General
    </a>
</div>

<!-- ============================================================= -->
<!-- FORMULARIO DE EDICIÓN -->
<!-- ============================================================= -->
<div class="glass-card rounded-2xl p-6 sm:p-8 border border-white/5 shadow-2xl relative overflow-hidden max-w-3xl mx-auto" id="edit-cert-container">
    <div class="absolute w-[200px] h-[200px] rounded-full bg-sky-500/5 blur-[50px] top-[-50px] left-[-50px] pointer-events-none"></div>

    <div class="border-b border-white/5 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-['Outfit'] font-bold text-white">Editar Certificado</h2>
            <p class="text-xs text-gray-400 mt-0.5">Modifique la información registrada del certificado.</p>
        </div>
        <div class="text-left sm:text-right">
            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-mono font-semibold">Hash de Validación</span>
            <p class="text-xs text-sky-400 font-mono mt-0.5" title="<?php echo e($cert['codigo_verificacion']); ?>">
                <?php echo substr($cert['codigo_verificacion'], 0, 16); ?>...
            </p>
        </div>
    </div>

    <!-- Alert para errores -->
    <?php if (!empty($error)): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-sm flex items-start gap-2.5 mb-6 animate-pulse" role="alert" id="edit-error-alert">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="flex-1"><?php echo e($error); ?></span>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6" id="edit-certificado-form" onsubmit="return validateForm()">
        <!-- CSRF Token Field -->
        <?php echo csrfField(); ?>

        <!-- Sección RUC y Razón Social -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <!-- RUC -->
            <div class="space-y-1.5 sm:col-span-1">
                <label for="ruc" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Número de RUC <span class="text-red-500">*</span></label>
                <input type="text" id="ruc" name="ruc" required maxlength="11" pattern="\d{11}"
                       placeholder="20123456789" 
                       value="<?php echo e($ruc); ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300 font-mono tracking-wider">
            </div>
            
            <!-- Razón Social -->
            <div class="space-y-1.5 sm:col-span-2">
                <label for="razon_social" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Razón Social <span class="text-red-500">*</span></label>
                <input type="text" id="razon_social" name="razon_social" required
                       placeholder="INVERSIONES GENERALES S.A.C." 
                       value="<?php echo e($razonSocial); ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
            </div>
        </div>

        <!-- Participante -->
        <div class="space-y-1.5">
            <label for="nombre_participante" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Nombre Completo del Participante <span class="text-red-500">*</span></label>
            <input type="text" id="nombre_participante" name="nombre_participante" required
                   placeholder="MARIO ALFREDO CASTRO AGUILAR" 
                   value="<?php echo e($participante); ?>"
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
        </div>

        <!-- Tipo de Certificado -->
        <div class="space-y-1.5">
            <label for="tipo_certificado" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Tipo de Certificado / Competencia <span class="text-red-500">*</span></label>
            <input type="text" id="tipo_certificado" name="tipo_certificado" required list="common-courses"
                   placeholder="Ej: Operario de Refugios Mineros Móviles" 
                   value="<?php echo e($tipoCertificado); ?>"
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
            
            <datalist id="common-courses">
                <option value="Operario Especialista de Refugios Mineros Móviles">
                <option value="Técnico Supervisor de Sistemas Contra Incendios">
                <option value="Especialista en Seguridad y Almacén de Polvorines">
                <option value="Fabricador y Montador de Estructuras Metálicas Pesadas">
            </datalist>
        </div>

        <!-- Descripción -->
        <div class="space-y-1.5">
            <label for="descripcion" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Descripción del Logro / Nota (Opcional)</label>
            <textarea id="descripcion" name="descripcion" rows="3"
                      placeholder="Indique detalles adicionales de la competencia técnica, puntaje obtenido o temarios validados." 
                      class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300 resize-none"><?php echo e($descripcion); ?></textarea>
        </div>

        <!-- Sección de fechas y estado -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <!-- Fecha Emisión -->
            <div class="space-y-1.5">
                <label for="fecha_emision" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Fecha Emisión <span class="text-red-500">*</span></label>
                <input type="date" id="fecha_emision" name="fecha_emision" required
                       value="<?php echo e($fechaEmision); ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
            </div>

            <!-- Fecha Vencimiento -->
            <div class="space-y-1.5">
                <label for="fecha_vencimiento" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Fecha Vence (Opcional)</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                       value="<?php echo e($fechaVencimiento); ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
            </div>

            <!-- Estado -->
            <div class="space-y-1.5">
                <label for="estado" class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">Estado del Certificado</label>
                <select id="estado" name="estado" 
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300 appearance-none cursor-pointer">
                    <option value="vigente" <?php echo $estado === 'vigente' ? 'selected' : ''; ?> class="bg-gray-900 text-white">Vigente</option>
                    <option value="vencido" <?php echo $estado === 'vencido' ? 'selected' : ''; ?> class="bg-gray-900 text-white">Vencido</option>
                    <option value="anulado" <?php echo $estado === 'anulado' ? 'selected' : ''; ?> class="bg-gray-900 text-white">Anulado</option>
                </select>
            </div>
        </div>

        <!-- Botón de Envío -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
            <a href="<?php echo ADMIN_URL; ?>/modules/certificados/index.php" class="px-5 py-3.5 rounded-xl font-semibold text-sm border border-white/10 text-white hover:bg-white/5 transition-colors shrink-0">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-7 py-3.5 rounded-xl font-semibold text-sm bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-xl shadow-sky-500/20 hover:shadow-sky-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 cursor-pointer">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>

<!-- ============================================================= -->
<!-- CLIENT SIDE FORM VALIDATIONS JS -->
<!-- ============================================================= -->
<script>
    function validateForm() {
        const rucInput = document.getElementById('ruc');
        const emisionInput = document.getElementById('fecha_emision');
        const vencimientoInput = document.getElementById('fecha_vencimiento');
        
        const ruc = rucInput.value.trim();
        const emision = emisionInput.value;
        const vencimiento = vencimientoInput.value;
        
        // 1. RUC Validation
        const rucRegex = /^\d{11}$/;
        if (!rucRegex.test(ruc)) {
            alert('El número de RUC ingresado no es válido. Debe contener exactamente 11 dígitos numéricos.');
            rucInput.focus();
            return false;
        }
        
        // 2. Date checking
        if (vencimiento !== '') {
            if (new Date(vencimiento) < new Date(emision)) {
                alert('La fecha de vencimiento no puede ser anterior a la fecha de emisión del certificado.');
                vencimientoInput.focus();
                return false;
            }
        }
        
        return true;
    }
</script>

<?php
// Cargar pie del panel admin
require_once __DIR__ . '/../../../src/views/admin_footer.php';
?>
