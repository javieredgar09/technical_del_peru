<?php
/**
 * Technical del Perú — Header del Panel de Administración
 * 
 * Variables disponibles:
 * @var string $pageTitle Título de la página
 */
$pageTitle = $pageTitle ?? 'Panel de Administración';
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo e($pageTitle); ?> | Technical del Perú Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
</head>
<body class="bg-gray-950 text-gray-100 font-['Inter',sans-serif] antialiased min-h-screen">

    <!-- ============================================================= -->
    <!-- ADMIN TOP BAR -->
    <!-- ============================================================= -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-gray-900/95 backdrop-blur-xl border-b border-white/5 h-16">
        <div class="flex items-center justify-between h-full px-6">
            
            <!-- Logo + Sidebar Toggle -->
            <div class="flex items-center gap-4">
                <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors" aria-label="Toggle sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="<?php echo ADMIN_URL; ?>/index.php" class="flex items-center gap-3">
                    <?php echo renderBrandLogo('h-9', true); ?>
                    <span class="px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded bg-sky-500/10 text-sky-400 border border-sky-500/25 hidden md:inline-block">Admin</span>
                </a>
            </div>

            <!-- Right Section -->
            <div class="flex items-center gap-4">
                <a href="<?php echo BASE_URL; ?>/" target="_blank" class="text-gray-400 hover:text-sky-400 text-sm hidden sm:flex items-center gap-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Ver sitio
                </a>
                
                <!-- User Menu -->
                <div class="relative" id="user-menu-container">
                    <button class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 transition-colors" id="user-menu-btn">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                            <?php echo strtoupper(substr($currentUser['nombre'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm text-white font-medium leading-tight"><?php echo e($currentUser['nombre'] ?? ''); ?></p>
                            <p class="text-xs text-gray-500 leading-tight"><?php echo e($currentUser['rol'] ?? ''); ?></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    
                    <!-- Dropdown -->
                    <div class="absolute right-0 top-full mt-2 w-48 bg-gray-800 border border-white/10 rounded-xl shadow-2xl hidden" id="user-dropdown">
                        <div class="p-2">
                            <a href="<?php echo ADMIN_URL; ?>/logout.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-red-400 hover:bg-red-500/10 text-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Cerrar sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ============================================================= -->
    <!-- ADMIN SIDEBAR -->
    <!-- ============================================================= -->
    <aside class="fixed top-16 left-0 bottom-0 w-64 bg-gray-900/80 backdrop-blur-xl border-r border-white/5 z-40 transition-transform duration-300 -translate-x-full lg:translate-x-0" id="admin-sidebar">
        <nav class="p-4 space-y-1 overflow-y-auto h-full">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider px-3 mb-3">Menú Principal</p>
            
            <a href="<?php echo ADMIN_URL; ?>/index.php" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 <?php echo $currentPage === 'index.php' ? 'bg-sky-500/10 text-sky-400' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <?php if (hasRole(['Administrador', 'Vendedor'])): ?>
            <a href="<?php echo ADMIN_URL; ?>/modules/certificados/index.php" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Certificados
            </a>
            <?php endif; ?>

            <?php if (hasRole(['Administrador', 'Vendedor', 'Marketing'])): ?>
            <a href="#" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Productos
            </a>
            <?php endif; ?>

            <?php if (hasRole(['Administrador', 'Marketing', 'Gestor de contenido'])): ?>
            <a href="#" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                Blog
            </a>
            <?php endif; ?>

            <?php if (hasRole(['Administrador', 'Marketing'])): ?>
            <a href="#" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Banners
            </a>
            <?php endif; ?>

            <?php if (hasRole('Administrador')): ?>
            <div class="pt-4 mt-4 border-t border-white/5">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider px-3 mb-3">Configuración</p>
                
                <a href="<?php echo ADMIN_URL; ?>/modules/configuracion/firma.php" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Firma Digital
                </a>

                <a href="#" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Usuarios
                </a>

                <a href="#" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Logs
                </a>
            </div>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- ============================================================= -->
    <!-- ADMIN MAIN CONTENT -->
    <!-- ============================================================= -->
    <div class="lg:ml-64 pt-16 min-h-screen">
        <div class="p-6 lg:p-8">

            <!-- Flash Messages -->
            <?php echo renderFlash(); ?>

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-['Outfit'] font-bold text-white"><?php echo e($pageTitle); ?></h1>
            </div>
