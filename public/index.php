<?php
/**
 * Technical del Perú — Página Principal
 * 
 * Punto de entrada público. Muestra el hero con video, enfoques estratégicos,
 * sección Quiénes Somos (Misión / Visión) y productos destacados cargados desde BD.
 * Incorpora animaciones inmersivas en tarjetas, interactividad de video, 3D tilt y ondas.
 * 
 * @version 1.2.1
 */

// Cargar configuración y autoloader
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/helpers/functions_helper.php';

// Iniciar sesión
initSession();

// Cargar datos dinámicos desde base de datos
try {
    $db = getDB();
    
    // 1. Obtener productos destacados activos
    $stmtProd = $db->query('
        SELECT * FROM `productos` 
        WHERE `activo` = 1 AND `destacado` = 1 
        ORDER BY `orden` ASC 
        LIMIT 4
    ');
    $productosDestacados = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Obtener secciones corporativas desde la BD
    $stmtSec = $db->query('
        SELECT * FROM `secciones` 
        WHERE `clave` IN ("mision", "vision", "quienes_somos", "valores")
    ');
    $seccionesRaw = $stmtSec->fetchAll(PDO::FETCH_ASSOC);
    
    $secciones = [];
    foreach ($seccionesRaw as $sec) {
        $secciones[$sec['clave']] = $sec;
    }
} catch (Exception $e) {
    error_log('Error al cargar datos en public/index.php: ' . $e->getMessage());
    $productosDestacados = [];
    $secciones = [];
}

// Configuración de títulos y SEO
$pageTitle = 'Technical del Perú | Soluciones Industriales de Alto Rendimiento';
$pageDescription = 'Especialistas en refugios mineros móviles, sistemas contra incendios, polvorines y estructuras metálicas. Certificaciones verificables en línea.';

// Cargar cabecera pública
require_once __DIR__ . '/../src/views/header.php';
?>

<!-- Estilos premium para efectos 3D, Spotlight y Ondas -->
<style>
    /* Estilos de alta legibilidad para el Hero con efectos 3D de sombra */
    .hero-title {
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.95), 0 0 45px rgba(0, 0, 0, 0.5), 0 0 90px rgba(0, 0, 0, 0.3);
        filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.7));
    }
    /* Previene parpadeos de renderizado durante transiciones CSS en Chrome/Edge */
    .hero-title span {
        will-change: transform, filter, opacity;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }
    .hero-subtitle {
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.95), 0 0 30px rgba(0, 0, 0, 0.4);
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.7));
    }
    /* Lente radial oscuro sutil detrás del texto para un contraste de primer nivel */
    .hero-text-lens {
        background: radial-gradient(circle at center, rgba(3, 7, 18, 0.5) 0%, rgba(3, 7, 18, 0.25) 50%, rgba(3, 7, 18, 0) 100%);
        border-radius: 2rem;
        backdrop-filter: blur(0.5px);
        padding: 2.5rem 1.5rem;
    }

    .focus-tilt-card {
        position: relative;
        transform-style: preserve-3d;
        perspective: 1000px;
    }
    /* Efecto de Foco Magnético (Spotlight) siguiendo al cursor */
    .focus-tilt-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        background: radial-gradient(300px circle at var(--x, 50%) var(--y, 50%), rgba(56, 189, 248, 0.22), transparent 75%);
        z-index: 3;
        pointer-events: none;
        transition: opacity 0.5s ease;
        opacity: 0;
        border-radius: 1rem;
    }
    .focus-tilt-card:hover::before {
        opacity: 1;
    }
    /* Ondas concéntricas para los iconos */
    .wave-pulse {
        position: relative;
    }
    .wave-pulse::after {
        content: "";
        position: absolute;
        inset: -8px;
        border: 2px solid rgba(56, 189, 248, 0.4);
        border-radius: 0.75rem;
        animation: pulseWave 2s infinite ease-out;
        pointer-events: none;
    }
    @keyframes pulseWave {
        0% {
            transform: scale(0.9);
            opacity: 1;
        }
        100% {
            transform: scale(1.3);
            opacity: 0;
        }
    }
    
    /* Carrusel 3D Swiper (Quiénes Somos) */
    .quienes-swiper {
        width: 100%;
        max-width: 460px;
        height: 380px;
        margin: 0 auto;
    }
    .quienes-swiper .swiper-slide {
        background-color: #0f172a;
        border-radius: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .quienes-swiper .swiper-pagination-bullet-active {
        background: #38bdf8 !important;
        width: 1.25rem !important;
        border-radius: 4px !important;
    }
</style>

<!-- Subtle radial background glows -->
<div class="absolute w-[500px] h-[500px] rounded-full bg-sky-500/[0.03] blur-[120px] top-[-100px] left-[-100px] pointer-events-none"></div>
<div class="absolute w-[600px] h-[600px] rounded-full bg-indigo-600/[0.02] blur-[150px] top-[800px] right-[-100px] pointer-events-none"></div>

<!-- ============================================================= -->
<!-- HERO SECTION -->
<!-- ============================================================= -->
<section class="relative min-h-[95vh] flex items-center justify-center overflow-hidden" id="hero-section">
    <!-- Video Background con Opacidad Optimizada -->
    <div class="absolute inset-0 z-0">
        <video autoplay muted loop playsinline class="w-full h-full object-cover opacity-50"
               poster="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.29 PM.jpeg">
            <source src="<?php echo BASE_URL; ?>/assets/video/hero.mp4" type="video/mp4">
        </video>
        <!-- Degradado de superposición cinematic -->
        <div class="absolute inset-0 bg-gradient-to-b from-gray-950/50 via-gray-950/20 to-gray-950"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center py-24" id="hero-content-wrapper">
        <div class="hero-text-lens">
            <div id="hero-badge" class="opacity-0">
                <span class="inline-flex items-center gap-2 px-4.5 py-1.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20 mb-6">
                    <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                    Tecnología & Seguridad Industrial
                </span>
            </div>
            
            <h1 class="text-4xl sm:text-6xl lg:text-7.5xl font-['Outfit'] font-bold text-white leading-[1.05] mb-6 tracking-tight hero-title opacity-0" id="hero-title">
                <span class="block mb-2 hero-title-line-1">Ingeniería de vanguardia</span>
                <span class="bg-gradient-to-r from-sky-400 via-blue-400 to-indigo-500 bg-clip-text text-transparent block hero-title-line-2">que protege vidas</span>
            </h1>
            
            <p class="text-base sm:text-xl text-gray-300 max-w-3xl mx-auto mb-10 leading-relaxed hero-subtitle opacity-0" id="hero-desc">
                Especialistas en la fabricación de refugios mineros, sistemas de supresión de fuego, polvorines y estructuras metálicas pesadas bajo estrictas certificaciones internacionales.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 opacity-0" id="hero-ctas">
                <a href="<?php echo BASE_URL; ?>/productos.php" 
                   class="px-8 py-4 rounded-xl font-semibold text-base bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-xl shadow-sky-500/20 transition-all duration-300 flex items-center justify-center cursor-pointer w-full sm:w-auto" 
                   id="cta-productos">
                    Ver Catálogo
                </a>
                <a href="<?php echo BASE_URL; ?>/buscar-certificado.php" 
                   class="px-8 py-4 rounded-xl font-semibold text-base border border-white/10 text-white hover:bg-white/5 hover:border-white/20 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto" 
                   id="cta-certificados">
                    <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Validar Certificado
                </a>
            </div>
        </div>

        <!-- Estadísticas corporativas -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 max-w-4xl mx-auto mt-20 border-t border-white/5 pt-10 opacity-0" id="hero-stats">
            <div class="text-center">
                <p class="text-3xl font-['Outfit'] font-bold text-white">10+</p>
                <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Años de trayectoria</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-['Outfit'] font-bold text-white">400+</p>
                <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Naves & Obras construidas</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-['Outfit'] font-bold text-white">100%</p>
                <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Certificaciones SUCAMEC</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-['Outfit'] font-bold text-white">12 OPS</p>
                <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Capacidad por refugio</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================= -->
<!-- 4 ENFOQUES ESTRATÉGICOS CON IMÁGENES 3D REALISTAS & TILT -->
<!-- ============================================================= -->
<section class="py-24 relative" id="enfoques-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="text-sky-400 font-semibold text-xs uppercase tracking-widest">Nuestra Especialidad</span>
            <h2 class="text-3xl sm:text-4xl font-['Outfit'] font-bold text-white mt-3">Enfoques Estratégicos Industriales</h2>
            <p class="text-gray-400 mt-4 max-w-xl mx-auto text-sm">Pasa el cursor para experimentar el movimiento físico 3D, el foco de luz interactivo y la previsualización del video operativo en cada tarjeta.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Card 1: Refugios Mineros Móviles -->
            <div class="focus-tilt-card focus-video-card group relative p-8 rounded-2xl border border-white/10 overflow-hidden transition-all duration-500 flex flex-col justify-between min-h-[320px] shadow-lg cursor-pointer bg-gray-900" 
                 style="transform-style: preserve-3d; backface-visibility: hidden;"
                 data-aos="fade-up" data-aos-delay="100"
                 onclick="window.location.href='<?php echo BASE_URL; ?>/producto.php?slug=refugio-minero-movil-technical-8';">
                <!-- Video de fondo interactivo permanente (refugios.mp4) -->
                <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover opacity-35 transition-all duration-500 z-0">
                    <source src="<?php echo BASE_URL; ?>/assets/video/refugios.mp4" type="video/mp4">
                </video>
                <!-- Cinematic dark overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-gray-950/85 via-gray-950/55 to-gray-950/90 z-1 pointer-events-none"></div>
                
                <!-- Contenido con TranslateZ para efecto 3D -->
                <div class="relative z-10" style="transform: translateZ(50px);">
                    <!-- Icono con efecto de onda de choque -->
                    <div class="w-12 h-12 rounded-xl bg-sky-500/20 border border-sky-500/30 flex items-center justify-center mb-6 group-hover:bg-sky-500/30 transition-all duration-300 wave-pulse">
                        <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <h3 class="text-lg font-['Outfit'] font-bold text-white mb-3">Refugios Mineros Móviles</h3>
                    <p class="text-xs text-gray-300 leading-relaxed font-medium">Estructuras certificadas para la protección del personal en operaciones mineras subterráneas.</p>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-xs text-sky-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="transform: translateZ(30px);">
                    Ver Ficha Técnica &rarr;
                </div>
            </div>

            <!-- Card 2: Sistema Contra Incendios -->
            <div class="focus-tilt-card focus-video-card group relative p-8 rounded-2xl border border-white/10 overflow-hidden transition-all duration-500 flex flex-col justify-between min-h-[320px] shadow-lg cursor-pointer bg-gray-900" 
                 style="transform-style: preserve-3d; backface-visibility: hidden;"
                 data-aos="fade-up" data-aos-delay="200"
                 onclick="window.location.href='<?php echo BASE_URL; ?>/producto.php?slug=sistema-autonomo-supresion-fuego';">
                <!-- Video de fondo interactivo permanente (incendio.mp4) -->
                <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover opacity-35 transition-all duration-500 z-0">
                    <source src="<?php echo BASE_URL; ?>/assets/video/incendio.mp4" type="video/mp4">
                </video>
                <!-- Cinematic dark overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-gray-950/85 via-gray-950/55 to-gray-950/90 z-1 pointer-events-none"></div>
                
                <!-- Contenido -->
                <div class="relative z-10" style="transform: translateZ(50px);">
                    <!-- Icono con onda de choque -->
                    <div class="w-12 h-12 rounded-xl bg-red-500/20 border border-red-500/30 flex items-center justify-center mb-6 group-hover:bg-red-500/30 transition-all duration-300 wave-pulse">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                    </div>
                    <h3 class="text-lg font-['Outfit'] font-bold text-white mb-3">Sistema Contra Incendios</h3>
                    <p class="text-xs text-gray-300 leading-relaxed font-medium">Diseño, instalación y mantenimiento de sistemas de detección y extinción de incendios.</p>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-xs text-red-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="transform: translateZ(30px);">
                    Ver Ficha Técnica &rarr;
                </div>
            </div>

            <!-- Card 3: Especialistas en Polvorines -->
            <div class="focus-tilt-card focus-video-card group relative p-8 rounded-2xl border border-white/10 overflow-hidden transition-all duration-500 flex flex-col justify-between min-h-[320px] shadow-lg cursor-pointer bg-gray-900" 
                 style="transform-style: preserve-3d; backface-visibility: hidden;"
                 data-aos="fade-up" data-aos-delay="300"
                 onclick="window.location.href='<?php echo BASE_URL; ?>/producto.php?slug=polvorin-movil-certificado-tipo-2';">
                <!-- Video de fondo interactivo permanente (polvorines.mp4) -->
                <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover opacity-35 transition-all duration-500 z-0">
                    <source src="<?php echo BASE_URL; ?>/assets/video/polvorines.mp4" type="video/mp4">
                </video>
                <!-- Cinematic dark overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-gray-950/85 via-gray-950/55 to-gray-950/90 z-1 pointer-events-none"></div>
                
                <!-- Contenido -->
                <div class="relative z-10" style="transform: translateZ(50px);">
                    <!-- Icono con onda de choque -->
                    <div class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center mb-6 group-hover:bg-amber-500/30 transition-all duration-300 wave-pulse">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-lg font-['Outfit'] font-bold text-white mb-3">Especialistas en Polvorines</h3>
                    <p class="text-xs text-gray-300 leading-relaxed font-medium">Almacenes certificados para explosivos con los más altos estándares de seguridad.</p>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-xs text-amber-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="transform: translateZ(30px);">
                    Ver Ficha Técnica &rarr;
                </div>
            </div>

            <!-- Card 4: Estructuras Metálicas -->
            <div class="focus-tilt-card focus-video-card group relative p-8 rounded-2xl border border-white/10 overflow-hidden transition-all duration-500 flex flex-col justify-between min-h-[320px] shadow-lg cursor-pointer bg-gray-900" 
                 style="transform-style: preserve-3d; backface-visibility: hidden;"
                 data-aos="fade-up" data-aos-delay="400"
                 onclick="window.location.href='<?php echo BASE_URL; ?>/producto.php?slug=nave-industrial-estructura-pesada';">
                <!-- Video de fondo interactivo permanente (estructuras.mp4) -->
                <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover opacity-35 transition-all duration-500 z-0">
                    <source src="<?php echo BASE_URL; ?>/assets/video/estructuras.mp4" type="video/mp4">
                </video>
                <!-- Cinematic dark overlay -->
                <div class="absolute inset-0 bg-gradient-to-b from-gray-950/85 via-gray-950/55 to-gray-950/90 z-1 pointer-events-none"></div>
                
                <!-- Contenido -->
                <div class="relative z-10" style="transform: translateZ(50px);">
                    <!-- Icono con onda de choque -->
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center mb-6 group-hover:bg-indigo-500/30 transition-all duration-300 wave-pulse">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-lg font-['Outfit'] font-bold text-white mb-3">Estructuras Metálicas</h3>
                    <p class="text-xs text-gray-300 leading-relaxed font-medium">Fabricación y montaje de estructuras metálicas para proyectos industriales y civiles.</p>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-xs text-indigo-400 font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="transform: translateZ(30px);">
                    Ver Ficha Técnica &rarr;
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================= -->
<!-- QUIÉNES SOMOS (MISIÓN / VISIÓN DE LA BD) -->
<!-- ============================================================= -->
<section class="py-24 relative overflow-hidden bg-white/[0.01] border-y border-white/5" id="quienes-somos-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            
            <!-- Columna izquierda: Información corporativa dinámica -->
            <div data-aos="fade-right">
                <span class="text-sky-400 font-semibold text-xs uppercase tracking-widest">Nuestra Identidad</span>
                <h2 class="text-3xl sm:text-4xl font-['Outfit'] font-bold text-white mt-3 mb-6">
                    <?php echo e($secciones['quienes_somos']['titulo'] ?? 'Quiénes Somos'); ?>
                </h2>
                <p class="text-sm text-gray-400 leading-relaxed mb-10">
                    <?php echo e($secciones['quienes_somos']['contenido'] ?? 'Technical del Perú es una empresa líder en ingeniería estructural y metalmecánica. Desarrollamos soluciones integrales de alta durabilidad adaptándonos a las geografías más hostiles del territorio nacional.'); ?>
                </p>

                <!-- Grid Misión y Visión con Glowing HSL borders -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Misión -->
                    <div class="p-6 rounded-2xl bg-white/[0.01] border border-white/5 hover:border-sky-500/20 shadow-lg shadow-black/10 transition-all duration-300">
                        <div class="w-10 h-10 rounded-lg bg-sky-500/10 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-2">
                            <?php echo e($secciones['mision']['titulo'] ?? 'Misión'); ?>
                        </h4>
                        <p class="text-2xs text-gray-400 leading-relaxed">
                            <?php echo e($secciones['mision']['contenido'] ?? 'Brindar soluciones técnicas de alta calidad para la industria peruana.'); ?>
                        </p>
                    </div>

                    <!-- Visión -->
                    <div class="p-6 rounded-2xl bg-white/[0.01] border border-white/5 hover:border-indigo-500/20 shadow-lg shadow-black/10 transition-all duration-300">
                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-2">
                            <?php echo e($secciones['vision']['titulo'] ?? 'Visión'); ?>
                        </h4>
                        <p class="text-2xs text-gray-400 leading-relaxed">
                            <?php echo e($secciones['vision']['contenido'] ?? 'Ser la empresa líder en servicios técnicos especializados del Perú.'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Carrusel de Fotos con Efecto 3D Moderno -->
            <div class="relative" data-aos="fade-left" data-aos-delay="200">
                <!-- Glowing HSL Background Glows -->
                <div class="absolute w-[350px] h-[350px] rounded-full bg-sky-500/10 blur-[100px] top-[-50px] right-[-50px] pointer-events-none"></div>
                <div class="absolute w-[350px] h-[350px] rounded-full bg-indigo-500/5 blur-[120px] bottom-[-80px] left-[-80px] pointer-events-none"></div>
                
                <!-- Swiper 3D Cards Container -->
                <div class="swiper quienes-swiper select-none">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.37 PM.jpeg" 
                                 alt="Montaje Mecánico" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Metalmecánica</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Montaje de Tolvas Mineras</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Instalación y soldadura de tolvas de alta capacidad para la gran minería en el territorio nacional.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.21 PM.jpeg" 
                                 alt="Techado Industrial" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Ingeniería</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Naves y Techados Industriales</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Montaje estructural de grandes luces y techados metálicos para almacenes de alta resistencia.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.16 PM.jpeg" 
                                 alt="Sistemas Hidráulicos" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Fluidos</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Redes de Bombeo Industrial</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Conexiones bridadas e hidráulicas para sistemas contra incendios y flujo de fluidos críticos.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.23 PM (2).jpeg" 
                                 alt="Soldadura Homologada" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Calidad</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Soldadura Estructural AWS</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Soldadura y control de calidad riguroso en uniones estructurales de alta exigencia mecánica.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.24 PM.jpeg" 
                                 alt="Fabricación Metálica" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Procesos</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Tuberías y Spools Industriales</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Fabricación y ensamble de spools de acero al carbono con certificación radiográfica.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.36 PM.jpeg" 
                                 alt="Resguardo de Materiales" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Seguridad</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Polvorines y Cámaras Estancas</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Sistemas de almacenamiento de explosivos homologados bajo la norma SUCAMEC.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.40 PM (1).jpeg" 
                                 alt="Montaje de Ductos" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Infraestructura</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Sistemas de Tuberías Aéreas</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Soportes, anclajes y tendido de líneas de tuberías en naves industriales complejas.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.20.31 PM (1).jpeg" 
                                 alt="Obras de Altura" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Energía</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Montajes Críticos en Altura</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Equipos altamente entrenados y calificados para montajes en condiciones geográficas complejas.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.32 PM (1).jpeg" 
                                 alt="Columnas Metálicas" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Estructura</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Pórticos y Columnas Pesadas</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Instalación de columnas de soporte estructural para soportar cargas pesadas continuas.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.28 PM (2).jpeg" 
                                 alt="Refugios Mineros" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Minería</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Construcción de Refugios Mineros</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Ensamblaje estanco y seguro de refugios mineros capaces de salvaguardar vidas humanas.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.29 PM.jpeg" 
                                 alt="Salas de Control" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Automatización</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Sistemas e Instrumentación</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Control preciso y seguro de variables de proceso, presión, temperatura y supresión.</p>
                            </div>
                        </div>
                        <div class="swiper-slide relative group overflow-hidden">
                            <img src="<?php echo BASE_URL; ?>/assets/images/WhatsApp Image 2026-05-21 at 4.17.18 PM.jpeg" 
                                 alt="Sistemas de Extinción" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent opacity-85 z-10"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8 z-20">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wider">Prevención</span>
                                <h5 class="text-white font-['Outfit'] font-bold text-base mt-2.5">Instalaciones de Válvulas NFPA</h5>
                                <p class="text-gray-400 text-xs mt-1.5 leading-relaxed font-medium">Sistemas activos de rociadores y diluvio diseñados para contener siniestros industriales.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Paginación dinámica Swiper -->
                    <div class="swiper-pagination !bottom-6"></div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================= -->
<!-- PRODUCTOS DESTACADOS DESDE LA BD -->
<!-- ============================================================= -->
<section class="py-24 relative" id="productos-destacados">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16" data-aos="fade-up">
            <div>
                <span class="text-sky-400 font-semibold text-xs uppercase tracking-widest">Ingeniería Certificada</span>
                <h2 class="text-3xl sm:text-4xl font-['Outfit'] font-bold text-white mt-3">Productos Destacados</h2>
                <p class="text-gray-400 mt-2 text-sm max-w-lg">Soluciones de alto rendimiento diseñadas a la medida y validadas por entes reguladores.</p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>/productos.php" class="inline-flex items-center gap-1.5 px-5 py-3 rounded-xl border border-white/10 hover:border-white/20 text-xs font-semibold text-white hover:bg-white/5 transition-all duration-300">
                    Ver Todos los Productos
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (empty($productosDestacados)): ?>
                <div class="col-span-full py-12 text-center text-gray-500">
                    No hay productos destacados configurados en este momento.
                </div>
            <?php else: ?>
                <?php foreach ($productosDestacados as $prod): ?>
                    <div class="group relative rounded-3xl bg-white/[0.02] border border-white/5 hover:border-sky-500/30 overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-sky-500/[0.02] flex flex-col justify-between" 
                         data-aos="fade-up" 
                         data-aos-delay="<?php echo $prod['orden'] * 100; ?>">
                        
                        <div>
                            <!-- Aspect ratio 4:3 con Zoom -->
                            <div class="relative overflow-hidden aspect-[4/3] bg-gray-900 border-b border-white/5">
                                <img src="<?php echo BASE_URL . '/' . e($prod['imagen_principal']); ?>" 
                                     alt="<?php echo e($prod['nombre']); ?>" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 to-transparent"></div>
                                <span class="absolute top-4 left-4 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase bg-sky-500/10 text-sky-400 border border-sky-500/20 tracking-widest">
                                    <?php echo e($prod['industria']); ?>
                                </span>
                            </div>
                            
                            <!-- Detalles -->
                            <div class="p-6">
                                <h3 class="text-base font-['Outfit'] font-bold text-white group-hover:text-sky-400 transition-colors duration-300 leading-snug">
                                    <?php echo e($prod['nombre']); ?>
                                </h3>
                                <p class="text-2xs text-gray-400 leading-relaxed mt-2.5 line-clamp-3">
                                    <?php echo e($prod['descripcion_corta']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Footer de la tarjeta -->
                        <div class="px-6 pb-6 pt-4 border-t border-white/5 flex items-center justify-between">
                            <span class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Cotización Rápida</span>
                            <a href="<?php echo BASE_URL; ?>/productos.php?slug=<?php echo urlencode($prod['slug']); ?>" 
                               class="text-2xs font-bold text-sky-400 group-hover:text-sky-300 transition-colors flex items-center gap-1">
                                Ficha Técnica &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- CTA: CERTIFICADOS PÚBLICOS -->
<!-- ============================================================= -->
<section class="py-20 relative overflow-hidden" id="cta-certificados-section">
    <div class="absolute inset-0 bg-gradient-to-r from-sky-500/[0.03] via-blue-500/[0.03] to-indigo-500/[0.03]"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10" data-aos="fade-up">
        <div class="p-8 sm:p-14 rounded-3xl bg-gradient-to-b from-white/5 to-transparent border border-white/10 backdrop-blur-sm shadow-2xl">
            <div class="w-16 h-16 rounded-2xl bg-sky-500/10 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h2 class="text-2xl sm:text-3xl font-['Outfit'] font-bold text-white mb-4">Validador Público de Competencia Técnica</h2>
            <p class="text-sm text-gray-400 mb-8 max-w-lg mx-auto">Verifique al instante la validez, vigencia y autenticidad de los certificados emitidos a contratistas y personal técnico.</p>
            <a href="<?php echo BASE_URL; ?>/buscar-certificado.php" 
               class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-semibold bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-xl shadow-sky-500/20 transition-all duration-300 hover:-translate-y-1 w-full sm:w-auto justify-center cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Buscar por RUC de la Empresa
            </a>
        </div>
    </div>
</section>

<?php
// Incluir pie de página público
require_once __DIR__ . '/../src/views/footer.php';
?>
