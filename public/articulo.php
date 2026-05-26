<?php
/**
 * Technical del Perú — Vista Detallada de Artículo de Blog
 * 
 * Despliega el contenido técnico completo de un artículo del blog,
 * inyectando metatags SEO y Open Graph dinámicos, maquetado en 2 columnas.
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

// Validar slug
$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';
if (empty($slug)) {
    redirect('blog.php');
}

// Instanciar modelo y obtener artículo
$model = new BlogModel();
$articulo = $model->obtenerPorSlug($slug);

if (!$articulo) {
    // Mensaje flash de error si el artículo no existe
    setFlashMessage('error', 'El artículo solicitado no existe o no está disponible en este momento.');
    redirect('blog.php');
}

// Configurar metatags dinámicos de SEO y Open Graph antes de inyectar la cabecera
$pageTitle = e($articulo['titulo']) . ' | Technical del Perú';
$pageDescription = e($articulo['descripcion']);
$ogImage = BASE_URL . '/' . e($articulo['imagen_portada']);
$ogType = 'article';

// Obtener datos del sidebar (3 recientes excluyendo el actual)
$recientes = $model->obtenerRecientes(3, (int)$articulo['id']);
$tagsPopulares = $model->obtenerTagsPopulares(6);

// Cargar cabecera pública
require_once __DIR__ . '/../src/views/header.php';
?>

<!-- ============================================================= -->
<!-- CUERPO PRINCIPAL DEL ARTÍCULO -->
<!-- ============================================================= -->
<section class="py-12 relative overflow-hidden" id="article-detail-section">
    <!-- Glow effects -->
    <div class="absolute w-[350px] h-[350px] rounded-full bg-sky-500/5 blur-[100px] top-10 right-10 pointer-events-none"></div>
    <div class="absolute w-[400px] h-[400px] rounded-full bg-indigo-600/5 blur-[120px] bottom-10 left-10 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-2xs text-gray-500 font-bold uppercase tracking-wider mb-8 font-mono" aria-label="Breadcrumb" data-aos="fade-up">
            <a href="<?php echo BASE_URL; ?>/" class="hover:text-sky-400 transition-colors">Inicio</a>
            <span>/</span>
            <a href="<?php echo BASE_URL; ?>/blog.php" class="hover:text-sky-400 transition-colors">Blog</a>
            <span>/</span>
            <span class="text-gray-400 truncate max-w-[200px] sm:max-w-none"><?php echo e($articulo['titulo']); ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- ============================================================= -->
            <!-- COLUMNA IZQUIERDA: CONTENIDO DEL ARTÍCULO -->
            <!-- ============================================================= -->
            <div class="lg:col-span-2" data-aos="fade-right">
                
                <article class="glass-card rounded-3xl border border-white/5 shadow-2xl p-6 sm:p-10 overflow-hidden">
                    
                    <!-- Portada de alta definición -->
                    <div class="relative rounded-2xl overflow-hidden aspect-[21/9] mb-8 bg-gray-900 border border-white/5">
                        <img src="<?php echo BASE_URL . '/' . e($articulo['imagen_portada']); ?>" 
                             alt="<?php echo e($articulo['titulo']); ?>" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/90 via-gray-950/10 to-transparent"></div>
                    </div>

                    <!-- Tags / Categorías -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php 
                        $tags = explode(',', $articulo['tags']);
                        foreach ($tags as $tg): 
                            $tg = trim($tg);
                            if (empty($tg)) continue;
                        ?>
                            <a href="<?php echo BASE_URL; ?>/blog.php?tag=<?php echo urlencode($tg); ?>" 
                               class="px-3 py-1 rounded-lg text-2xs font-bold uppercase tracking-wide bg-sky-500/10 text-sky-400 border border-sky-500/20 hover:bg-sky-500/20 transition-all">
                                <?php echo e($tg); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Título principal -->
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-['Outfit'] font-extrabold text-white leading-tight mb-5">
                        <?php echo e($articulo['titulo']); ?>
                    </h1>

                    <!-- Metadatos de publicación -->
                    <div class="flex flex-wrap items-center gap-y-3 gap-x-6 pb-6 border-b border-white/5 mb-8 text-xs text-gray-400 font-medium">
                        <span class="flex items-center gap-1.5">
                            <!-- Icono Autor -->
                            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Por <strong><?php echo e($articulo['autor']); ?></strong>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <!-- Icono Calendario -->
                            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <?php echo formatDate($articulo['fecha_publicacion'] ?? $articulo['created_at']); ?>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <!-- Icono Reloj -->
                            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            4 min de lectura
                        </span>
                    </div>

                    <!-- Cuerpo enriquecido (CSS-Styled Technical Article) -->
                    <div class="rich-content text-sm sm:text-base text-gray-300 leading-relaxed space-y-6">
                        <?php echo $articulo['contenido']; ?>
                    </div>

                    <!-- Separador de Compartir -->
                    <div class="mt-12 pt-6 border-t border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <span class="text-xs text-gray-400 font-bold uppercase tracking-wider font-mono">Compartir artículo técnico:</span>
                        <div class="flex items-center gap-2">
                            <!-- Compartir LinkedIn -->
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(BASE_URL . '/articulo.php?slug=' . $articulo['slug']); ?>" 
                               target="_blank" rel="noopener"
                               class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 hover:border-sky-500/30 hover:bg-sky-500/10 text-white hover:text-sky-300 text-xs font-semibold tracking-wide transition-all flex items-center gap-1.5">
                                LinkedIn
                            </a>
                            <!-- Compartir WhatsApp -->
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('Te recomiendo leer este artículo técnico especializado: ' . $articulo['titulo'] . ' ' . BASE_URL . '/articulo.php?slug=' . $articulo['slug']); ?>" 
                               target="_blank" rel="noopener"
                               class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 hover:border-emerald-500/30 hover:bg-emerald-500/10 text-white hover:text-emerald-400 text-xs font-semibold tracking-wide transition-all flex items-center gap-1.5">
                                WhatsApp
                            </a>
                        </div>
                    </div>

                </article>
            </div>

            <!-- ============================================================= -->
            <!-- COLUMNA DERECHA: SIDEBAR DE APOYO -->
            <!-- ============================================================= -->
            <div class="space-y-8" data-aos="fade-left">
                
                <!-- Tarjeta del Autor -->
                <div class="glass-card rounded-3xl p-6 border border-white/5 relative overflow-hidden">
                    <h3 class="text-white font-['Outfit'] font-bold text-base border-b border-white/5 pb-3 mb-4">
                        Autor Corporativo
                    </h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400 font-bold text-lg font-['Outfit'] shadow-md">
                            🔩
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white leading-tight"><?php echo e(explode(' (', $articulo['autor'])[0]); ?></h4>
                            <p class="text-3xs text-sky-400 font-bold uppercase tracking-wider mt-0.5">
                                <?php echo e(preg_replace('/^[^(]+\(([^)]+)\)$/', '$1', $articulo['autor'])); ?>
                            </p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Especialista senior en diseño, normativas de seguridad e ingeniería industrial en TECHNICAL DEL PERÚ, dedicado al desarrollo de infraestructuras críticas y soporte técnico avanzado.
                    </p>
                </div>

                <!-- Artículos Recientes -->
                <?php if (!empty($recientes)): ?>
                    <div class="glass-card rounded-3xl p-6 border border-white/5">
                        <h3 class="text-white font-['Outfit'] font-bold text-base border-b border-white/5 pb-3 mb-4">
                            Artículos Recientes
                        </h3>
                        <div class="space-y-4">
                            <?php foreach ($recientes as $rec): ?>
                                <a href="<?php echo BASE_URL; ?>/articulo.php?slug=<?php echo urlencode($rec['slug']); ?>" 
                                   class="group block flex items-start gap-3 border-b border-white/5 pb-4 last:border-b-0 last:pb-0">
                                    <div class="w-16 h-12 rounded-lg overflow-hidden bg-gray-900 border border-white/5 flex-shrink-0">
                                        <img src="<?php echo BASE_URL . '/' . e($rec['imagen_portada']); ?>" 
                                             alt="<?php echo e($rec['titulo']); ?>" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    </div>
                                    <div>
                                        <h4 class="text-2xs font-bold text-gray-300 group-hover:text-sky-400 transition-colors leading-snug line-clamp-2">
                                            <?php echo e($rec['titulo']); ?>
                                        </h4>
                                        <span class="text-3xs text-gray-500 font-medium mt-1 block">
                                            <?php echo formatDate($rec['fecha_publicacion'] ?? $rec['created_at']); ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tags Populares en Sidebar -->
                <?php if (!empty($tagsPopulares)): ?>
                    <div class="glass-card rounded-3xl p-6 border border-white/5">
                        <h3 class="text-white font-['Outfit'] font-bold text-base border-b border-white/5 pb-3 mb-4">
                            Etiquetas Frecuentes
                        </h3>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($tagsPopulares as $tgPop): ?>
                                <a href="<?php echo BASE_URL; ?>/blog.php?tag=<?php echo urlencode($tgPop); ?>" 
                                   class="px-2.5 py-1.5 rounded-lg text-3xs font-bold uppercase tracking-wider border border-white/10 text-gray-400 hover:text-white hover:border-white/20 hover:bg-white/5 transition-all">
                                    <?php echo e($tgPop); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
</section>

<!-- Custom css properties for rich content typography layout styling -->
<style>
.rich-content h3 {
    font-family: 'Outfit', sans-serif;
    color: #ffffff;
    font-size: 1.15rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
    border-left: 3px solid #0ea5e9;
    padding-left: 0.75rem;
}
.rich-content p {
    margin-bottom: 1.25rem;
    color: #cbd5e1; /* gray-300 */
}
.rich-content ul, .rich-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
    color: #cbd5e1;
}
.rich-content ul {
    list-style-type: disc;
}
.rich-content ol {
    list-style-type: decimal;
}
.rich-content li {
    margin-bottom: 0.5rem;
}
.rich-content strong {
    color: #38bdf8; /* sky-400 */
}
</style>

<?php
// Cargar pie de página público
require_once __DIR__ . '/../src/views/footer.php';
?>
