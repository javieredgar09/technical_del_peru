<?php
/**
 * Technical del Perú — Panel de Administración (Dashboard)
 * 
 * Página de inicio del panel de control protegida por login. Muestra
 * estadísticas, accesos directos por rol y logs de actividad.
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

// Proteger ruta: requiere estar logueado
requireLogin();

// Título de página
$pageTitle = 'Dashboard';

// Obtener datos reales de estadísticas con manejo robusto de excepciones
$db = getDB();

$totalCertificados = 0;
$vigentesCertificados = 0;
$totalProductos = 0;
$contactosNoLeidos = 0;

try {
    $stmt = $db->query('SELECT COUNT(*) FROM `certificados`');
    $totalCertificados = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    error_log('Error counting certificados: ' . $e->getMessage());
}

try {
    $stmt = $db->query("SELECT COUNT(*) FROM `certificados` WHERE `estado` = 'vigente'");
    $vigentesCertificados = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    error_log('Error counting vigentes: ' . $e->getMessage());
}

try {
    $stmt = $db->query('SELECT COUNT(*) FROM `productos`');
    $totalProductos = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    error_log('Error counting productos: ' . $e->getMessage());
}

try {
    $stmt = $db->query('SELECT COUNT(*) FROM `contactos` WHERE `leido` = 0');
    $contactosNoLeidos = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    error_log('Error counting contactos: ' . $e->getMessage());
}

// Obtener logs de actividad recientes (solo para Administrador)
$latestLogs = [];
if (hasRole('Administrador')) {
    try {
        $stmt = $db->prepare('
            SELECT l.*, u.nombre AS usuario_nombre 
            FROM `logs` l 
            LEFT JOIN `usuarios` u ON l.usuario_id = u.id 
            ORDER BY l.created_at DESC 
            LIMIT 8
        ');
        $stmt->execute();
        $latestLogs = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('Error fetching system logs: ' . $e->getMessage());
    }
}

// Cargar cabecera del panel de administración
require_once __DIR__ . '/../src/views/admin_header.php';
?>

<!-- ============================================================= -->
<!-- WELCOME BANNER -->
<!-- ============================================================= -->
<div class="glass-card rounded-2xl p-6 md:p-8 border border-white/5 mb-8 relative overflow-hidden">
    <div class="absolute w-[300px] h-[300px] rounded-full bg-sky-500/5 blur-[80px] -top-[100px] -right-[100px] z-0 pointer-events-none"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-['Outfit'] font-bold text-white mb-2">
                ¡Hola, <?php echo e($currentUser['nombre']); ?>!
            </h2>
            <p class="text-gray-400 text-sm md:text-base max-w-xl">
                Bienvenido al centro de administración de Technical del Perú. Tu nivel de acceso actual es: 
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20">
                    <?php echo e($currentUser['rol']); ?>
                </span>
            </p>
        </div>
        <div>
            <p class="text-xs text-gray-500 text-right md:text-right uppercase tracking-wider font-semibold">Último acceso registrado</p>
            <p class="text-sm font-medium text-white mt-1">
                <?php 
                // Obtener fecha del último acceso del usuario
                try {
                    $stmtUser = $db->prepare('SELECT `ultimo_acceso` FROM `usuarios` WHERE `id` = ?');
                    $stmtUser->execute([$currentUser['id']]);
                    $lastAccess = $stmtUser->fetchColumn();
                    echo $lastAccess ? formatDateTime($lastAccess) : 'Primer ingreso';
                } catch(Exception $ex) {
                    echo '—';
                }
                ?>
            </p>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- STATS WIDGETS -->
<!-- ============================================================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Stat 1: Total Certificados (Vendedor, Admin) -->
    <?php if (hasRole(['Administrador', 'Vendedor'])): ?>
    <div class="glass-card rounded-2xl p-6 border border-white/5 flex items-center gap-5 hover:border-sky-500/20 transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-sky-500/10 flex items-center justify-center text-sky-400 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Certificados</p>
            <p class="text-2xl font-bold text-white mt-1"><?php echo $totalCertificados; ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stat 2: Certificados Vigentes (Vendedor, Admin) -->
    <?php if (hasRole(['Administrador', 'Vendedor'])): ?>
    <div class="glass-card rounded-2xl p-6 border border-white/5 flex items-center gap-5 hover:border-emerald-500/20 transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Vigentes / Activos</p>
            <p class="text-2xl font-bold text-white mt-1"><?php echo $vigentesCertificados; ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stat 3: Productos Activos (Admin, Vendedor, Marketing) -->
    <?php if (hasRole(['Administrador', 'Vendedor', 'Marketing'])): ?>
    <div class="glass-card rounded-2xl p-6 border border-white/5 flex items-center gap-5 hover:border-blue-500/20 transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Productos Catálogo</p>
            <p class="text-2xl font-bold text-white mt-1"><?php echo $totalProductos; ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stat 4: Contactos No Leídos (Admin, Marketing) -->
    <?php if (hasRole(['Administrador', 'Marketing'])): ?>
    <div class="glass-card rounded-2xl p-6 border border-white/5 flex items-center gap-5 hover:border-amber-500/20 transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 shrink-0 relative">
            <?php if ($contactosNoLeidos > 0): ?>
                <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
            <?php endif; ?>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Consultas Pendientes</p>
            <p class="text-2xl font-bold text-white mt-1"><?php echo $contactosNoLeidos; ?></p>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ============================================================= -->
<!-- QUICK ACTIONS & SYSTEM LOGS -->
<!-- ============================================================= -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Column 1 & 2: Quick Modules Grid -->
    <div class="lg:col-span-2 space-y-6">
        <div class="glass-card rounded-2xl p-6 border border-white/5">
            <h3 class="text-lg font-['Outfit'] font-bold text-white mb-6">Módulos Administrativos</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Certificados Card -->
                <?php if (hasRole(['Administrador', 'Vendedor'])): ?>
                <a href="<?php echo ADMIN_URL; ?>/modules/certificados/index.php" class="p-5 rounded-xl bg-white/5 border border-white/5 hover:border-sky-500/30 hover:bg-white/10 flex items-center justify-between transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm">Certificados</p>
                            <p class="text-xs text-gray-500 mt-0.5">Crear y validar certificados RUC</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>

                <!-- Productos Card -->
                <?php if (hasRole(['Administrador', 'Vendedor', 'Marketing'])): ?>
                <a href="#" class="p-5 rounded-xl bg-white/5 border border-white/5 hover:border-blue-500/30 hover:bg-white/10 flex items-center justify-between transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm">Productos</p>
                            <p class="text-xs text-gray-500 mt-0.5">Catálogo y modelos en 3D</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>

                <!-- Blog Card -->
                <?php if (hasRole(['Administrador', 'Marketing', 'Gestor de contenido'])): ?>
                <a href="#" class="p-5 rounded-xl bg-white/5 border border-white/5 hover:border-indigo-500/30 hover:bg-white/10 flex items-center justify-between transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm">Blog & Noticias</p>
                            <p class="text-xs text-gray-500 mt-0.5">Artículos de prensa y SEO</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>

                <!-- Banners Slider Card -->
                <?php if (hasRole(['Administrador', 'Marketing'])): ?>
                <a href="#" class="p-5 rounded-xl bg-white/5 border border-white/5 hover:border-amber-500/30 hover:bg-white/10 flex items-center justify-between transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm">Banners Web</p>
                            <p class="text-xs text-gray-500 mt-0.5">Controlar carrusel público</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>

                <!-- Firma Digital Card -->
                <?php if (hasRole('Administrador')): ?>
                <a href="<?php echo ADMIN_URL; ?>/modules/configuracion/firma.php" class="p-5 rounded-xl bg-white/5 border border-white/5 hover:border-emerald-500/30 hover:bg-white/10 flex items-center justify-between transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm">Firma Digital</p>
                            <p class="text-xs text-gray-500 mt-0.5">Subir firma PNG para PDF</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>

                <!-- Secciones Editables -->
                <?php if (hasRole(['Administrador', 'Gestor de contenido'])): ?>
                <a href="#" class="p-5 rounded-xl bg-white/5 border border-white/5 hover:border-teal-500/30 hover:bg-white/10 flex items-center justify-between transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-teal-500/10 flex items-center justify-center text-teal-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white text-sm">Secciones Editables</p>
                            <p class="text-xs text-gray-500 mt-0.5">Editar textos e imágenes de la web</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
    
    <!-- Column 3: System Logs Audit (Only visible to Administrators) -->
    <div class="lg:col-span-1">
        <?php if (hasRole('Administrador')): ?>
        <div class="glass-card rounded-2xl p-6 border border-white/5 flex flex-col h-full">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-['Outfit'] font-bold text-white">Auditoría de Actividad</h3>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" title="Sistema en línea"></span>
            </div>
            
            <div class="flex-1 overflow-y-auto space-y-4 max-h-[380px] pr-2">
                <?php if (empty($latestLogs)): ?>
                    <p class="text-gray-500 text-xs text-center py-8">No hay registros de actividad recientes.</p>
                <?php else: ?>
                    <?php foreach ($latestLogs as $log): ?>
                        <div class="border-b border-white/5 pb-3 last:border-b-0 last:pb-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-sky-400">
                                    <?php echo e($log['usuario_nombre'] ?? 'Sistema / Visitante'); ?>
                                </span>
                                <span class="text-[10px] text-gray-500">
                                    <?php echo formatDate($log['created_at'], 'd/m H:i'); ?>
                                </span>
                            </div>
                            <p class="text-xs text-white font-medium mt-1">
                                <?php echo e($log['accion']); ?>
                            </p>
                            <?php if ($log['detalle']): ?>
                                <p class="text-[10px] text-gray-400 mt-0.5 truncate" title="<?php echo e($log['detalle']); ?>">
                                    <?php echo e($log['detalle']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[9px] px-1.5 py-0.2 bg-white/5 rounded text-gray-500 font-mono">
                                    IP: <?php echo e($log['ip_address'] ?? '127.0.0.1'); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- For non-admin roles, show a helpful profile card widget -->
        <div class="glass-card rounded-2xl p-6 border border-white/5 text-center flex flex-col items-center justify-center h-full">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center text-white text-3xl font-bold shadow-xl shadow-sky-500/20 mb-4 select-none">
                <?php echo strtoupper(substr($currentUser['nombre'], 0, 1)); ?>
            </div>
            <h3 class="text-lg font-['Outfit'] font-bold text-white"><?php echo e($currentUser['nombre']); ?></h3>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold"><?php echo e($currentUser['rol']); ?></p>
            
            <p class="text-sm text-gray-400 mt-4 max-w-xs">
                Usa el menú lateral o las tarjetas de la izquierda para gestionar el contenido autorizado para tu cuenta.
            </p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php
// Cargar pie del panel de administración
require_once __DIR__ . '/../src/views/admin_footer.php';
?>
