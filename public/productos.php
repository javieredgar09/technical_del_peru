<?php
/**
 * Technical del Perú — Catálogo Público de Productos
 * 
 * Despliega el catálogo completo de soluciones de ingeniería pesada,
 * refugios mineros, sistemas de supresión y estructuras metálicas,
 * con filtros dinámicos por industria y buscador integrado.
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

// Instanciar modelo de productos
$model = new ProductoModel();

// Parámetros de búsqueda y filtros
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;
$industriaSeleccionada = isset($_GET['industria']) ? sanitizeInput($_GET['industria']) : null;

// Obtener datos
$productos = $model->listarActivos($search, $industriaSeleccionada);
$industrias = $model->obtenerIndustrias();

// Configurar título y SEO de la página
$pageTitle = 'Catálogo de Soluciones Industriales | Technical del Perú';
$pageDescription = 'Explore nuestro portafolio de ingeniería estructural, refugios mineros móviles homologados, sistemas supresores de fuego NFPA y naves industriales pesadas.';

// Cargar cabecera pública
require_once __DIR__ . '/../src/views/header.php';
?>

<!-- ============================================================= -->
<!-- BANNER CABECERA -->
<!-- ============================================================= -->
<section class="relative py-20 bg-gray-900 border-b border-white/5 overflow-hidden" id="header-banner">
    <!-- Subtle glow backgrounds -->
    <div class="absolute w-[350px] h-[350px] rounded-full bg-sky-500/5 blur-[100px] top-[-50px] left-[-50px] pointer-events-none"></div>
    <div class="absolute w-[400px] h-[400px] rounded-full bg-indigo-600/5 blur-[120px] bottom-[-100px] right-[-100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20 mb-4">
            🔩 Ingeniería Certificada
        </span>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-['Outfit'] font-bold text-white tracking-tight">
            Catálogo de Soluciones Especializadas
        </h1>
        <p class="text-sm sm:text-base text-gray-400 max-w-xl mx-auto mt-4 leading-relaxed">
            Equipos de alta resistencia e instalaciones validadas bajo los más altos estándares nacionales e internacionales. Seleccione una categoría para inspeccionar nuestras fichas de ingeniería.
        </p>
    </div>
</section>

<!-- ============================================================= -->
<!-- FILTROS Y BÚSQUEDA -->
<!-- ============================================================= -->
<section class="py-8 relative z-20" id="filters-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="glass-card rounded-3xl p-6 border border-white/5 shadow-2xl relative overflow-hidden" data-aos="fade-up">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <!-- Pills de Industrias -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Botón "Todo" -->
                    <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>" 
                       class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-300 border <?php echo empty($industriaSeleccionada) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white border-transparent shadow-lg shadow-sky-500/20' : 'border-white/10 text-gray-400 hover:text-white hover:border-white/20 hover:bg-white/5'; ?>">
                        Todos los Sectores
                    </a>
                    
                    <?php foreach ($industrias as $ind): ?>
                        <a href="?industria=<?php echo urlencode($ind); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-300 border <?php echo ($industriaSeleccionada === $ind) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white border-transparent shadow-lg shadow-sky-500/20' : 'border-white/10 text-gray-400 hover:text-white hover:border-white/20 hover:bg-white/5'; ?>">
                            <?php echo e($ind); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Buscador de Productos -->
                <form action="" method="GET" class="w-full lg:w-96 flex gap-2">
                    <?php if ($industriaSeleccionada): ?>
                        <input type="hidden" name="industria" value="<?php echo e($industriaSeleccionada); ?>">
                    <?php endif; ?>
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" name="search" placeholder="Buscar productos..." 
                               value="<?php echo e($search ?? ''); ?>"
                               class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300 font-medium">
                    </div>
                    <button type="submit" 
                            class="px-5 py-3 rounded-xl bg-white/5 border border-white/10 text-white hover:bg-white/10 text-xs font-semibold tracking-wide transition-all cursor-pointer">
                        Buscar
                    </button>
                </form>

            </div>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- GRID DE PRODUCTOS -->
<!-- ============================================================= -->
<section class="py-12 relative min-h-[50vh]" id="catalog-grid-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if (!empty($search) || !empty($industriaSeleccionada)): ?>
            <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-10" data-aos="fade-up">
                <div class="text-sm text-gray-400">
                    Mostrando resultados de filtrado 
                    <?php if ($industriaSeleccionada): ?> para sector <span class="text-sky-400 font-bold">"<?php echo e($industriaSeleccionada); ?>"</span><?php endif; ?>
                    <?php if ($search): ?> con búsqueda <span class="text-sky-400 font-bold">"<?php echo e($search); ?>"</span><?php endif; ?>
                </div>
                <a href="productos.php" class="text-xs font-bold text-sky-400 hover:text-sky-300 transition-colors flex items-center gap-1">
                    Limpiar Filtros &times;
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($productos)): ?>
            <!-- Clean elegant No Results Card -->
            <div class="glass-card rounded-3xl p-16 border border-white/5 text-center flex flex-col items-center justify-center max-w-2xl mx-auto" data-aos="fade-up">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-400 mb-5">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-lg font-['Outfit'] font-bold text-white mb-2">No se encontraron productos</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    No poseemos ningún producto registrado en catálogo que coincida con los criterios de búsqueda especificados en este momento.
                </p>
                <a href="productos.php" class="mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 text-xs font-semibold tracking-wider shadow-lg shadow-sky-500/20">
                    Restaurar Catálogo Completo
                </a>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($productos as $prod): ?>
                    <div class="group relative rounded-3xl bg-white/[0.02] border border-white/5 hover:border-sky-500/30 overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-sky-500/[0.02] flex flex-col justify-between" 
                         data-aos="fade-up" 
                         data-aos-delay="<?php echo $prod['orden'] * 50; ?>">
                        
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
                            <span class="text-[10px] text-sky-400/80 font-bold uppercase tracking-wider font-mono">
                                <?php if ($prod['precio_referencial'] > 0): ?>
                                    US$ <?php echo number_format($prod['precio_referencial'], 2); ?>
                                <?php else: ?>
                                    Cotizar
                                <?php endif; ?>
                            </span>
                            <a href="<?php echo BASE_URL; ?>/producto.php?slug=<?php echo urlencode($prod['slug']); ?>" 
                               class="text-2xs font-bold text-white bg-white/5 border border-white/10 group-hover:border-sky-500/30 group-hover:bg-sky-500/10 group-hover:text-sky-300 px-4 py-2 rounded-xl transition-all flex items-center gap-1 shadow-inner">
                                Ficha & 3D &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
// Cargar pie de página público
require_once __DIR__ . '/../src/views/footer.php';
?>
