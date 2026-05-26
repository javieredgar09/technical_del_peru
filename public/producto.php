<?php
/**
 * Technical del Perú — Detalle de Producto y Visor 3D
 * 
 * Muestra la información técnica detallada de una solución industrial
 * y renderiza un entorno interactivo WebGL en 3D utilizando Three.js
 * con soporte para modelos GLTF/GLB y fallbacks procedimentales interactivos.
 * 
 * @version 1.0.0
 */

// Cargar archivos de configuración y helpers
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/helpers/functions_helper.php';
require_once __DIR__ . '/../src/models/ProductoModel.php';

// Iniciar sesión
initSession();

// Validar parámetro de slug
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    // Mensaje flash y redirección si no hay slug
    setFlashMessage('error', 'Producto no especificado.');
    redirect('productos.php');
}

// Instanciar modelo y obtener datos del producto
$model = new ProductoModel();
$producto = $model->obtenerPorSlug($slug);

if (!$producto) {
    // Redirección si el producto no existe
    setFlashMessage('error', 'El producto solicitado no existe o no está activo.');
    redirect('productos.php');
}

// Número de contacto comercial de WhatsApp (Simulado de alta conversión)
$whatsappNumber = '51999999999'; // Cambiar por el número real del cliente
$whatsappMessage = "Hola Technical del Perú, deseo solicitar una cotización formal y asesoría técnica para el siguiente producto: " . $producto['nombre'] . " (Sector: " . $producto['industria'] . ").";
$whatsappLink = "https://api.whatsapp.com/send?phone=" . $whatsappNumber . "&text=" . urlencode($whatsappMessage);

// Configurar título y SEO de la página
$pageTitle = $producto['nombre'] . ' | Ficha Técnica 3D | Technical del Perú';
$pageDescription = $producto['descripcion_corta'];

// Cargar cabecera pública
require_once __DIR__ . '/../src/views/header.php';
?>

<!-- Estilos específicos del Visor 3D y Glassmorphism de la página -->
<style>
    .viewer-container {
        position: relative;
        background: radial-gradient(circle at center, #111827 0%, #030712 100%);
    }
    .viewer-toolbar-btn {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #9ca3af;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .viewer-toolbar-btn:hover {
        background: rgba(56, 189, 248, 0.1);
        border-color: rgba(56, 189, 248, 0.3);
        color: #38bdf8;
        transform: translateY(-2px);
    }
    .viewer-toolbar-btn.active {
        background: rgba(56, 189, 248, 0.2);
        border-color: rgba(56, 189, 248, 0.5);
        color: #38bdf8;
        box-shadow: 0 0 15px rgba(56, 189, 248, 0.2);
    }
    /* Animación del engranaje del loading */
    @keyframes gear-rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .loading-gear {
        animation: gear-rotate 4s linear infinite;
    }
</style>

<!-- ============================================================= -->
<!-- ENLACE VOLVER -->
<!-- ============================================================= -->
<div class="py-6 bg-gray-950 border-b border-white/5 relative z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="productos.php" class="inline-flex items-center gap-2 text-xs font-semibold text-gray-400 hover:text-sky-400 transition-colors">
            <span>&larr;</span> Volver al Catálogo Completo
        </a>
    </div>
</div>

<!-- ============================================================= -->
<!-- CUERPO DE FICHA TÉCNICA -->
<!-- ============================================================= -->
<section class="py-16 relative overflow-hidden" id="product-detail-section">
    <!-- Glowing background lights -->
    <div class="absolute w-[450px] h-[450px] rounded-full bg-sky-500/[0.02] blur-[120px] top-[-100px] left-[-100px] pointer-events-none"></div>
    <div class="absolute w-[450px] h-[450px] rounded-full bg-indigo-500/[0.02] blur-[120px] bottom-[100px] right-[-100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-stretch">
            
            <!-- COLUMNA IZQUIERDA: VISOR INTERACTIVO 3D -->
            <div class="lg:col-span-7 flex flex-col justify-between" data-aos="fade-right">
                <div class="flex-1 flex flex-col">
                    <!-- Cabecera de la tarjeta del visor -->
                    <div class="flex items-center justify-between bg-white/[0.01] border-x border-t border-white/5 rounded-t-3xl p-5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
                            </span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest font-mono">Entorno de Pruebas WebGL 3D</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-white/5 border border-white/10 text-gray-400 tracking-wider">
                            Interactive Sandbox
                        </span>
                    </div>

                    <!-- Contenedor del Canvas de Three.js -->
                    <div class="viewer-container rounded-b-3xl border border-white/5 overflow-hidden shadow-2xl relative flex-1 min-h-[420px] sm:min-h-[500px]">
                        <!-- Loading Overlay -->
                        <div id="viewer-loading" class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-gray-950/90 transition-opacity duration-700">
                            <!-- Engranaje tecnológico en 2D que gira -->
                            <div class="w-16 h-16 rounded-2xl bg-sky-500/10 flex items-center justify-center text-sky-400 mb-5 loading-gear">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <h4 class="text-sm font-['Outfit'] font-bold text-white tracking-wider">Cargando Renderizador 3D</h4>
                            <p class="text-2xs text-gray-500 mt-2 font-medium tracking-wide">Compilando shaders y texturas de ingeniería...</p>
                        </div>

                        <!-- Canvas Holder -->
                        <div id="canvas-3d-container" 
                             data-slug="<?php echo e($producto['slug']); ?>" 
                             data-model="<?php echo $producto['modelo_3d'] ? BASE_URL . '/' . e($producto['modelo_3d']) : ''; ?>"
                             data-industry="<?php echo e($producto['industria']); ?>"
                             class="w-full h-full absolute inset-0 z-10"></div>

                        <!-- Controles Flotantes del Visor -->
                        <div class="absolute bottom-5 left-5 right-5 z-20 flex items-center justify-between gap-4 pointer-events-none">
                            <!-- Controles de Modo -->
                            <div class="flex items-center gap-2 pointer-events-auto">
                                <button id="btn-3d-wireframe" class="viewer-toolbar-btn w-10 h-10 rounded-xl flex items-center justify-center" title="Alternar Estructura de Alambre">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                </button>
                                <button id="btn-3d-play" class="viewer-toolbar-btn active w-10 h-10 rounded-xl flex items-center justify-center" title="Pausar/Reanudar Animación">
                                    <svg id="icon-pause" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <svg id="icon-play" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </div>

                            <!-- Botón de Reset -->
                            <button id="btn-3d-reset" class="viewer-toolbar-btn w-10 h-10 rounded-xl flex items-center justify-center pointer-events-auto" title="Restaurar Vista Original">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2M7 9a7 7 0 0111.79-1.78l3.42 3.42"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: ESPECIFICACIONES TÉCNICAS -->
            <div class="lg:col-span-5 flex flex-col justify-between" data-aos="fade-left">
                <div class="space-y-8">
                    <!-- Badge Sector e Identificador -->
                    <div class="flex items-center justify-between border-b border-white/5 pb-5">
                        <span class="px-3.5 py-1.5 rounded-full text-[10px] font-bold uppercase bg-sky-500/10 text-sky-400 border border-sky-500/20 tracking-wider">
                            Sector <?php echo e($producto['industria']); ?>
                        </span>
                        <span class="text-2xs font-mono text-gray-500">ID: TECH-PROD-<?php echo str_pad($producto['id'], 3, '0', STR_PAD_LEFT); ?></span>
                    </div>

                    <!-- Título del Producto -->
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-['Outfit'] font-bold text-white tracking-tight leading-tight">
                            <?php echo e($producto['nombre']); ?>
                        </h1>
                        <p class="text-gray-400 mt-4 text-sm leading-relaxed font-medium">
                            <?php echo e($producto['descripcion_corta']); ?>
                        </p>
                    </div>

                    <!-- Precio Estimado de Ingeniería -->
                    <div class="p-6 rounded-2xl bg-white/[0.01] border border-white/5 shadow-inner">
                        <span class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold">Valoración Estimada de Proyecto</span>
                        <div class="text-2xl font-bold text-sky-400 mt-1.5 font-mono">
                            <?php if ($producto['precio_referencial'] > 0): ?>
                                US$ <?php echo number_format($producto['precio_referencial'], 2); ?>
                                <span class="text-3xs text-gray-500 font-sans tracking-normal ml-1 font-medium">(Valor base de ingeniería)</span>
                            <?php else: ?>
                                Cotización Personalizada
                                <span class="text-3xs text-gray-500 font-sans tracking-normal ml-1 font-medium">(Bajo especificaciones del RUC)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Detalles del Producto -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Descripción del Activo</h3>
                        <p class="text-xs text-gray-400 leading-relaxed font-medium">
                            <?php echo nl2br(e($producto['descripcion'])); ?>
                        </p>
                    </div>

                    <!-- Especificaciones Adicionales Fijas (Garantía de Ingeniería) -->
                    <div class="grid grid-cols-2 gap-4 border-t border-white/5 pt-6">
                        <div class="flex items-start gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 mt-2 shrink-0"></span>
                            <div>
                                <h5 class="text-[11px] font-bold text-white uppercase tracking-wider">Normativa NFPA / AWS</h5>
                                <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed font-medium">Materiales certificados bajo estrictos estándares.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 mt-2 shrink-0"></span>
                            <div>
                                <h5 class="text-[11px] font-bold text-white uppercase tracking-wider">Garantía Estructural</h5>
                                <p class="text-[10px] text-gray-500 mt-0.5 leading-relaxed font-medium">Plena cobertura contra fallas físicas y mecánicas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Conversión WhatsApp -->
                <div class="pt-8 mt-8 border-t border-white/5">
                    <a href="<?php echo $whatsappLink; ?>" target="_blank"
                       class="w-full py-4 px-6 rounded-2xl font-bold text-sm bg-gradient-to-r from-emerald-500 to-green-600 text-white hover:from-emerald-400 hover:to-green-500 shadow-xl shadow-emerald-500/10 hover:shadow-emerald-500/25 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2.5 cursor-pointer">
                        <!-- Icono SVG de WhatsApp -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.262 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.733-1.464L0 24zm6.59-4.846c1.6.95 3.197 1.45 4.817 1.451 5.46-.001 9.902-4.43 9.905-9.877.001-2.639-1.025-5.12-2.887-6.985C16.621 1.879 14.137.854 11.5.855c-5.461 0-9.903 4.43-9.905 9.879-.001 1.761.503 3.483 1.459 5.019L2.093 21.84l6.11-1.602zM18.06 14.93c-.33-.165-1.957-.967-2.288-1.087-.33-.12-.57-.18-.81.18-.24.36-.93 1.17-1.14 1.41-.21.24-.42.27-.75.105-1.74-.83-2.94-1.47-3.99-2.28-.28-.21-.43-.49-.09-.89.3-.34.66-.79.75-1.05.09-.27.045-.51-.023-.675-.067-.165-.57-1.37-.78-1.874-.206-.496-.44-.427-.6-.436-.153-.008-.33-.009-.51-.009-.18 0-.48.068-.73.342-.25.27-.96.936-.96 2.285 0 1.348.98 2.65 1.11 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.57.66.21 1.25.18 1.72.11.53-.08 1.958-.8 2.235-1.57.278-.77.278-1.43.195-1.57-.083-.14-.308-.225-.638-.39z"/></svg>
                        Cotizar Vía WhatsApp
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================= -->
<!-- CARGAR DEPENDENCIAS DE THREE.JS DESDE CDN -->
<!-- ============================================================= -->
<!-- Core Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<!-- Orbit Controls to rotate, pan and zoom -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<!-- Optional GLTFLoader to load dynamic files in the future -->
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

<!-- Cargar nuestro motor visual custom para inicializar el renderizador y el modelo procedimental -->
<script src="<?php echo BASE_URL; ?>/assets/js/producto_3d.js"></script>

<?php
// Cargar pie de página público
require_once __DIR__ . '/../src/views/footer.php';
?>
