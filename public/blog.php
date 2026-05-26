<?php
/**
 * Technical del Perú — Hub Público del Blog
 * 
 * Despliega listados de artículos técnicos de ingeniería, normativas y
 * seguridad industrial con buscador, filtrado por tags y paginación.
 * 
 * @version 1.0.0
 */

// Cargar archivos de configuración y helpers
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/helpers/functions_helper.php';
require_once __DIR__ . '/../src/models/BlogModel.php';

// Iniciar sesión
initSession();

// Instanciar modelo de blog
$model = new BlogModel();

// Parámetros de búsqueda, tags y paginación
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;
$tagSeleccionado = isset($_GET['tag']) ? sanitizeInput($_GET['tag']) : null;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

$perPage = 6;
$offset = ($currentPage - 1) * $perPage;

// Obtener datos del blog
$articulos = $model->listarPublicados($perPage, $offset, $search, $tagSeleccionado);
$totalItems = $model->contarPublicados($search, $tagSeleccionado);
$totalPages = ceil($totalItems / $perPage);
if ($totalPages < 1) $totalPages = 1;

// Ajustar página actual si supera el máximo
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $perPage;
    $articulos = $model->listarPublicados($perPage, $offset, $search, $tagSeleccionado);
}

$tagsPopulares = $model->obtenerTagsPopulares(8);

// Configurar título y SEO de la página
$pageTitle = 'Blog Técnico e Ingeniería Industrial | Technical del Perú';
$pageDescription = 'Artículos técnicos especializados sobre seguridad minera subterránea, sistemas de supresión de incendios, blindajes de polvorines y metalmecánica pesada.';

// Cargar cabecera pública
require_once __DIR__ . '/../src/views/header.php';
?>

<!-- ============================================================= -->
<!-- BANNER CABECERA -->
<!-- ============================================================= -->
<section class="relative py-20 bg-gray-900 border-b border-white/5 overflow-hidden" id="blog-banner">
    <!-- Glow backgrounds -->
    <div class="absolute w-[400px] h-[400px] rounded-full bg-sky-500/5 blur-[120px] top-[-100px] left-[-100px] pointer-events-none"></div>
    <div class="absolute w-[350px] h-[350px] rounded-full bg-indigo-600/5 blur-[100px] bottom-[-50px] right-[-50px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20 mb-4">
            📚 Conocimiento de Ingeniería
        </span>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-['Outfit'] font-bold text-white tracking-tight">
            Blog Técnico & Artículos Especializados
        </h1>
        <p class="text-sm sm:text-base text-gray-400 max-w-xl mx-auto mt-4 leading-relaxed">
            Consulte análisis de normativas, guías técnicas estructurales y mejores prácticas en seguridad industrial redactadas por nuestros especialistas.
        </p>
    </div>
</section>

<!-- ============================================================= -->
<!-- FILTROS Y BÚSQUEDA -->
<!-- ============================================================= -->
<section class="py-8 relative z-20" id="blog-filters-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="glass-card rounded-3xl p-6 border border-white/5 shadow-2xl relative overflow-hidden" data-aos="fade-up">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <!-- Pills de Tags Populares -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-500 font-bold uppercase tracking-wider mr-2 font-mono">Etiquetas:</span>
                    <!-- Botón "Todo" -->
                    <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>" 
                       class="px-3 py-1.5 rounded-lg text-2xs font-bold uppercase tracking-wide transition-all border <?php echo empty($tagSeleccionado) ? 'bg-sky-500/10 text-sky-400 border-sky-500/30' : 'border-white/10 text-gray-400 hover:text-white hover:border-white/20 hover:bg-white/5'; ?>">
                        Todos
                    </a>
                    
                    <?php foreach ($tagsPopulares as $t): ?>
                        <a href="?tag=<?php echo urlencode($t); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-3 py-1.5 rounded-lg text-2xs font-bold uppercase tracking-wide transition-all border <?php echo ($tagSeleccionado === $t) ? 'bg-sky-500/10 text-sky-400 border-sky-500/30' : 'border-white/10 text-gray-400 hover:text-white hover:border-white/20 hover:bg-white/5'; ?>">
                            <?php echo e($t); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Buscador -->
                <form action="" method="GET" class="w-full lg:w-80 flex gap-2">
                    <?php if ($tagSeleccionado): ?>
                        <input type="hidden" name="tag" value="<?php echo e($tagSeleccionado); ?>">
                    <?php endif; ?>
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" name="search" placeholder="Buscar artículos..." 
                               value="<?php echo e($search ?? ''); ?>"
                               class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-xs text-white placeholder-gray-500 focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300 font-medium">
                    </div>
                    <button type="submit" 
                            class="px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white hover:bg-white/10 text-xs font-semibold transition-all cursor-pointer">
                        Buscar
                    </button>
                </form>

            </div>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- CONTENIDO PRINCIPAL -->
<!-- ============================================================= -->
<section class="py-12 relative min-h-[40vh]" id="blog-grid-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if (!empty($search) || !empty($tagSeleccionado)): ?>
            <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-10" data-aos="fade-up">
                <div class="text-xs text-gray-400">
                    Artículos filtrados 
                    <?php if ($tagSeleccionado): ?> bajo etiqueta <span class="text-sky-400 font-bold">"<?php echo e($tagSeleccionado); ?>"</span><?php endif; ?>
                    <?php if ($search): ?> con término de búsqueda <span class="text-sky-400 font-bold">"<?php echo e($search); ?>"</span><?php endif; ?>
                </div>
                <a href="blog.php" class="text-xs font-bold text-sky-400 hover:text-sky-300 transition-colors flex items-center gap-1">
                    Limpiar Filtros &times;
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($articulos)): ?>
            <div class="glass-card rounded-3xl p-16 border border-white/5 text-center flex flex-col items-center justify-center max-w-xl mx-auto" data-aos="fade-up">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-400 mb-5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-base font-['Outfit'] font-bold text-white mb-2">No se encontraron artículos</h3>
                <p class="text-xs text-gray-400 leading-relaxed">
                    No poseemos ningún artículo publicado en el blog técnico que coincida con los criterios especificados en este momento.
                </p>
                <a href="blog.php" class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 text-xs font-semibold tracking-wide shadow-lg shadow-sky-500/20">
                    Restaurar Blog Completo
                </a>
            </div>
        <?php else: ?>
            <!-- Articles Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($articulos as $idx => $art): ?>
                    <article class="group relative rounded-3xl bg-white/[0.02] border border-white/5 hover:border-sky-500/30 overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-sky-500/[0.02] flex flex-col justify-between" 
                             data-aos="fade-up" 
                             data-aos-delay="<?php echo $idx * 100; ?>">
                        
                        <div>
                            <!-- Aspect ratio 16:9 con Zoom -->
                            <div class="relative overflow-hidden aspect-[16/9] bg-gray-900 border-b border-white/5">
                                <img src="<?php echo BASE_URL . '/' . e($art['imagen_portada']); ?>" 
                                     alt="<?php echo e($art['titulo']); ?>" 
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 to-transparent"></div>
                                
                                <!-- Tags list -->
                                <div class="absolute bottom-4 left-4 flex flex-wrap gap-1">
                                    <?php 
                                    $tags = explode(',', $art['tags']);
                                    foreach ($tags as $tg): 
                                        $tg = trim($tg);
                                        if (empty($tg)) continue;
                                    ?>
                                        <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-500/30 tracking-wide">
                                            <?php echo e($tg); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Detalles -->
                            <div class="p-6">
                                <!-- Meta (Fecha y Autor) -->
                                <div class="flex items-center gap-2 text-[10px] text-gray-500 font-medium mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <?php echo formatDate($art['fecha_publicacion'] ?? $art['created_at']); ?>
                                    </span>
                                    <span>•</span>
                                    <span>Por <?php echo e(explode(' (', $art['autor'])[0]); ?></span>
                                </div>
                                <h3 class="text-base font-['Outfit'] font-bold text-white group-hover:text-sky-400 transition-colors duration-300 leading-snug line-clamp-2">
                                    <a href="<?php echo BASE_URL; ?>/articulo.php?slug=<?php echo urlencode($art['slug']); ?>">
                                        <?php echo e($art['titulo']); ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-gray-400 leading-relaxed mt-3 line-clamp-3">
                                    <?php echo e($art['descripcion']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 pb-6 pt-4 border-t border-white/5 flex items-center justify-between">
                            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-wider font-mono">
                                Lectura 4 min
                            </span>
                            <a href="<?php echo BASE_URL; ?>/articulo.php?slug=<?php echo urlencode($art['slug']); ?>" 
                               class="text-2xs font-bold text-white bg-white/5 border border-white/10 group-hover:border-sky-500/30 group-hover:bg-sky-500/10 group-hover:text-sky-300 px-4 py-2 rounded-xl transition-all flex items-center gap-1 shadow-inner">
                                Leer artículo &rarr;
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- ============================================================= -->
            <!-- PAGINACIÓN -->
            <!-- ============================================================= -->
            <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-center gap-2 mt-16" data-aos="fade-up">
                    
                    <!-- Retroceso -->
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $tagSeleccionado ? '&tag=' . urlencode($tagSeleccionado) : ''; ?>" 
                           class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-white hover:bg-white/10 flex items-center justify-center text-sm transition-all">&larr;</a>
                    <?php endif; ?>

                    <!-- Números -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $tagSeleccionado ? '&tag=' . urlencode($tagSeleccionado) : ''; ?>" 
                           class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-bold transition-all border <?php echo ($i === $currentPage) ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white border-transparent shadow-lg shadow-sky-500/20' : 'bg-white/5 border-white/10 text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Avance -->
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $tagSeleccionado ? '&tag=' . urlencode($tagSeleccionado) : ''; ?>" 
                           class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-white hover:bg-white/10 flex items-center justify-center text-sm transition-all">&rarr;</a>
                    <?php endif; ?>

                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php
// Cargar pie de página público
require_once __DIR__ . '/../src/views/footer.php';
?>
