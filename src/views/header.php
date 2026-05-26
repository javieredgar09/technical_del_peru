<?php
/**
 * Technical del Perú — Header Público
 * 
 * Variables disponibles:
 * @var string $pageTitle Título de la página
 * @var string $pageDescription Meta description
 */
$pageTitle = $pageTitle ?? 'Technical del Perú | Soluciones Industriales';
$pageDescription = $pageDescription ?? 'Especialistas en refugios mineros, sistemas contra incendios, polvorines y estructuras metálicas.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($pageDescription); ?>">
    <meta name="author" content="Technical del Perú">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo e($pageTitle); ?>">
    <meta property="og:description" content="<?php echo e($pageDescription); ?>">
    <meta property="og:type" content="<?php echo $ogType ?? 'website'; ?>">
    <meta property="og:image" content="<?php echo $ogImage ?? (BASE_URL . '/assets/images/logo.png'); ?>">
    <meta property="og:locale" content="es_PE">
    
    <title><?php echo e($pageTitle); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animations -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    
    <!-- Swiper.js CSS (Para carrusel 3D) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Tailwind CSS (compilado) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
</head>
<body class="bg-gray-950 text-gray-100 font-['Inter',sans-serif] antialiased">

    <!-- ============================================================= -->
    <!-- NAVBAR -->
    <!-- ============================================================= -->
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="main-navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>/" id="nav-logo">
                    <?php echo renderBrandLogo('h-12'); ?>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center gap-1" id="desktop-menu">
                    <a href="<?php echo BASE_URL; ?>/" class="nav-link px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">Inicio</a>
                    <a href="<?php echo BASE_URL; ?>/#quienes-somos-section" class="nav-link px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">Quiénes Somos</a>
                    <a href="<?php echo BASE_URL; ?>/#enfoques-section" class="nav-link px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">Servicios</a>
                    <a href="<?php echo BASE_URL; ?>/productos.php" class="nav-link px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">Productos</a>
                    <a href="<?php echo BASE_URL; ?>/blog.php" class="nav-link px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">Blog</a>
                    <a href="<?php echo BASE_URL; ?>/#cta-certificados-section" class="nav-link px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">Capacitaciones</a>
                    <a href="<?php echo BASE_URL; ?>/buscar-certificado.php" class="nav-link px-3 py-2 rounded-lg text-sm font-medium text-sky-400 hover:text-sky-300 hover:bg-sky-400/10 transition-all duration-200 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Validar Certificado
                    </a>
                    <a href="<?php echo BASE_URL; ?>/contacto.php" class="ml-1 px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 transition-all duration-200 shadow-lg shadow-sky-500/20 hover:shadow-sky-500/40 hover:-translate-y-0.5">
                        Contáctanos
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors" id="mobile-menu-btn" aria-label="Abrir menú">
                    <svg class="w-6 h-6 block" id="menu-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="w-6 h-6 hidden" id="menu-icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Panel -->
        <div class="lg:hidden hidden" id="mobile-menu">
            <div class="bg-gray-900/95 backdrop-blur-xl border-t border-white/5 px-4 py-6 space-y-1">
                <a href="<?php echo BASE_URL; ?>/" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-colors">Inicio</a>
                <a href="<?php echo BASE_URL; ?>/#quienes-somos-section" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-colors">Quiénes Somos</a>
                <a href="<?php echo BASE_URL; ?>/#enfoques-section" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-colors">Servicios</a>
                <a href="<?php echo BASE_URL; ?>/productos.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-colors">Productos</a>
                <a href="<?php echo BASE_URL; ?>/blog.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-colors">Blog</a>
                <a href="<?php echo BASE_URL; ?>/#cta-certificados-section" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-colors">Capacitaciones</a>
                <a href="<?php echo BASE_URL; ?>/buscar-certificado.php" class="block px-4 py-3 rounded-lg text-sky-400 hover:text-sky-300 hover:bg-sky-400/10 transition-colors">
                    🛡️ Validar Certificado
                </a>
                <div class="pt-4">
                    <a href="<?php echo BASE_URL; ?>/contacto.php" class="block text-center px-5 py-3 rounded-lg font-semibold bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 transition-all">
                        Contáctanos
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Spacer for fixed navbar -->
    <div class="h-20"></div>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <?php echo renderFlash(); ?>
    </div>

    <!-- Main Content -->
    <main>
