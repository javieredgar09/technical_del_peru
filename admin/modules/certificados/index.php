<?php
/**
 * Technical del Perú — Panel de Certificados (Index / Listado)
 * 
 * Muestra el listado de certificados registrados en el sistema,
 * con soporte para búsqueda, paginación y controles de administración (CRUD).
 * Protegido para Administrador y Vendedor.
 * 
 * @version 1.0.0
 */

// Cargar archivos de configuración y helpers
require_once __DIR__ . '/../../../src/config/db.php';
require_once __DIR__ . '/../../../src/config/session.php';
require_once __DIR__ . '/../../../src/helpers/auth_helper.php';
require_once __DIR__ . '/../../../src/helpers/functions_helper.php';
require_once __DIR__ . '/../../../src/models/CertificadoModel.php';

// Iniciar sesión
initSession();

// Proteger ruta: solo Administrador y Vendedor
requireRole(['Administrador', 'Vendedor']);

$pageTitle = 'Gestión de Certificados';

// Parámetros de búsqueda y paginación
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 8; // Registros por página

try {
    $model = new CertificadoModel();
    
    // Obtener total para calcular páginas
    $totalItems = $model->obtenerTotal($search);
    $paginator = paginate($totalItems, $limit, $currentPage);
    
    // Obtener registros paginados
    $certificados = $model->listarTodos($limit, $paginator['offset'], $search);
} catch (Exception $e) {
    error_log('Error en listado de certificados: ' . $e->getMessage());
    $certificados = [];
    $totalItems = 0;
    $paginator = paginate(0, $limit, 1);
}

// Cargar cabecera del panel admin
require_once __DIR__ . '/../../../src/views/admin_header.php';
?>

<!-- ============================================================= -->
<!-- ENCABEZADO Y ACCIONES RÁPIDAS -->
<!-- ============================================================= -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Módulo de Certificaciones</p>
        <h2 class="text-xl font-['Outfit'] font-bold text-white mt-0.5">Listado General de Certificados</h2>
    </div>
    <div>
        <a href="<?php echo ADMIN_URL; ?>/modules/certificados/crear.php" 
           class="px-5 py-3 rounded-xl font-semibold text-sm bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 transition-all duration-300 hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2".2 d="M12 4v16m8-8H4"/></svg>
            Emitir Certificado
        </a>
    </div>
</div>

<!-- ============================================================= -->
<!-- FILTROS DE BÚSQUEDA -->
<!-- ============================================================= -->
<div class="glass-card rounded-2xl p-5 border border-white/5 mb-6">
    <form action="" method="GET" class="flex flex-col sm:flex-row gap-4" id="admin-cert-search">
        <div class="flex-1 relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500 pointer-events-none">
                <svg class="w-4.5 h-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" placeholder="Buscar por RUC, razón social, participante o código..." 
                   value="<?php echo e($search); ?>"
                   class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-3 rounded-xl font-semibold text-sm bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-colors cursor-pointer shrink-0">
                Filtrar
            </button>
            <?php if (!empty($search)): ?>
                <a href="?" class="px-4 py-3 rounded-xl font-semibold text-sm border border-red-500/30 bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors flex items-center justify-center shrink-0" title="Limpiar filtro">
                    &times; Limpiar
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ============================================================= -->
<!-- TABLA DE CERTIFICADOS -->
<!-- ============================================================= -->
<div class="glass-card rounded-2xl border border-white/5 overflow-hidden shadow-xl" id="admin-table-container">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-white/[0.02] text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <th class="px-6 py-4">RUC / Razón Social</th>
                    <th class="px-6 py-4">Participante</th>
                    <th class="px-6 py-4">Certificación</th>
                    <th class="px-6 py-4">Vigencia</th>
                    <th class="px-6 py-4 text-center">Estado</th>
                    <th class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm">
                <?php if (empty($certificados)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center mx-auto mb-4 text-gray-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            No se encontraron certificados registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($certificados as $cert): ?>
                        <?php 
                        $status = strtolower($cert['estado']);
                        if ($status === 'vigente') {
                            $statusClass = 'bg-green-500/10 text-green-400 border-green-500/20';
                            $statusText = 'Vigente';
                        } elseif ($status === 'vencido') {
                            $statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                            $statusText = 'Vencido';
                        } else {
                            $statusClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                            $statusText = 'Anulado';
                        }
                        ?>
                        <tr class="hover:bg-white/[0.01] transition-all">
                            <!-- RUC / Razón Social -->
                            <td class="px-6 py-4">
                                <p class="font-mono text-xs text-sky-400 tracking-wider"><?php echo e($cert['ruc']); ?></p>
                                <p class="text-xs text-gray-400 font-medium truncate max-w-[200px] mt-0.5" title="<?php echo e($cert['razon_social']); ?>">
                                    <?php echo e($cert['razon_social']); ?>
                                </p>
                            </td>
                            
                            <!-- Participante -->
                            <td class="px-6 py-4 font-semibold text-white">
                                <?php echo e($cert['nombre_participante']); ?>
                            </td>
                            
                            <!-- Certificación -->
                            <td class="px-6 py-4 text-xs">
                                <p class="text-gray-200 font-medium truncate max-w-[220px]" title="<?php echo e($cert['tipo_certificado']); ?>">
                                    <?php echo e($cert['tipo_certificado']); ?>
                                </p>
                                <p class="text-[10px] text-gray-500 font-mono mt-0.5">Hash: <?php echo substr($cert['codigo_verificacion'], 0, 8); ?>...</p>
                            </td>
                            
                            <!-- Fechas -->
                            <td class="px-6 py-4 text-xs text-gray-400 space-y-0.5">
                                <p>Emisión: <?php echo formatDate($cert['fecha_emision']); ?></p>
                                <p class="text-[10px]">Vence: <?php echo $cert['fecha_vencimiento'] ? formatDate($cert['fecha_vencimiento']) : 'Sin Vence'; ?></p>
                            </td>
                            
                            <!-- Estado -->
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-2xs font-semibold uppercase tracking-wider border <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            
                            <!-- Acciones -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Ver Público -->
                                    <a href="<?php echo BASE_URL; ?>/verificar-certificado.php?codigo=<?php echo urlencode($cert['codigo_verificacion']); ?>" 
                                       target="_blank" 
                                       class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-colors" 
                                       title="Ver verificación pública">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    
                                    <!-- Editar -->
                                    <a href="<?php echo ADMIN_URL; ?>/modules/certificados/editar.php?id=<?php echo $cert['id']; ?>" 
                                       class="p-2 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 hover:text-sky-300 transition-colors" 
                                       title="Editar certificado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    
                                    <!-- Eliminar -->
                                    <form action="<?php echo ADMIN_URL; ?>/modules/certificados/eliminar.php" method="POST" 
                                          onsubmit="return confirm('¿Está completamente seguro de eliminar este certificado? Esta acción borrará el registro de la base de datos y eliminará de forma física la imagen del código QR del servidor permanentemente.')" 
                                          class="inline">
                                        <!-- CSRF Protection Field -->
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo $cert['id']; ?>">
                                        <button type="submit" 
                                                class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-350 transition-colors cursor-pointer" 
                                                title="Eliminar certificado">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ============================================================= -->
    <!-- PAGINACIÓN -->
    <!-- ============================================================= -->
    <?php if ($paginator['totalPages'] > 1): ?>
        <div class="px-6 py-4 bg-white/[0.01] border-t border-white/5 flex items-center justify-between gap-4">
            <span class="text-xs text-gray-500">
                Mostrando página <?php echo $paginator['currentPage']; ?> de <?php echo $paginator['totalPages']; ?> (Total: <?php echo $totalItems; ?> registros)
            </span>
            <div class="flex items-center gap-1.5" id="pagination-controls">
                
                <!-- Prev Link -->
                <?php if ($paginator['hasPrev']): ?>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $paginator['currentPage'] - 1; ?>" 
                       class="px-3 py-2 rounded-lg text-xs font-semibold bg-white/5 border border-white/10 text-gray-400 hover:text-white hover:bg-white/10 transition-all flex items-center gap-1">
                        &larr; Ant
                    </a>
                <?php else: ?>
                    <span class="px-3 py-2 rounded-lg text-xs font-semibold bg-white/0 border border-white/0 text-gray-600 cursor-not-allowed">
                        &larr; Ant
                    </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php 
                $startPage = max(1, $paginator['currentPage'] - 2);
                $endPage = min($paginator['totalPages'], $startPage + 4);
                if ($endPage - $startPage < 4) {
                    $startPage = max(1, $endPage - 4);
                }
                for ($p = $startPage; $p <= $endPage; $p++): 
                ?>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>" 
                       class="w-8.5 h-8.5 rounded-lg text-xs font-semibold flex items-center justify-center transition-all <?php echo $p === $paginator['currentPage'] ? 'bg-sky-500 text-white shadow-lg shadow-sky-500/20' : 'bg-white/5 border border-white/10 text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>

                <!-- Next Link -->
                <?php if ($paginator['hasNext']): ?>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $paginator['currentPage'] + 1; ?>" 
                       class="px-3 py-2 rounded-lg text-xs font-semibold bg-white/5 border border-white/10 text-gray-400 hover:text-white hover:bg-white/10 transition-all flex items-center gap-1">
                        Sig &rarr;
                    </a>
                <?php else: ?>
                    <span class="px-3 py-2 rounded-lg text-xs font-semibold bg-white/0 border border-white/0 text-gray-600 cursor-not-allowed">
                        Sig &rarr;
                    </span>
                <?php endif; ?>

            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Cargar pie del panel admin
require_once __DIR__ . '/../../../src/views/admin_footer.php';
?>
