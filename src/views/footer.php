    </main>

    <!-- ============================================================= -->
    <!-- FOOTER -->
    <!-- ============================================================= -->
    <footer class="bg-gray-900 border-t border-white/5 mt-20">
        <!-- Main Footer Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                
                <!-- Company Info -->
                <div class="lg:col-span-1">
                    <a href="<?php echo BASE_URL; ?>/" class="mb-6 block">
                        <?php echo renderBrandLogo('h-14'); ?>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        Especialistas en soluciones industriales: refugios mineros, sistemas contra incendios, polvorines y estructuras metálicas.
                    </p>
                    <!-- Social Links -->
                    <div class="flex items-center gap-3">
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 hover:bg-sky-500/20 flex items-center justify-center text-gray-400 hover:text-sky-400 transition-all duration-200" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 hover:bg-sky-500/20 flex items-center justify-center text-gray-400 hover:text-sky-400 transition-all duration-200" aria-label="LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-white/5 hover:bg-sky-500/20 flex items-center justify-center text-gray-400 hover:text-sky-400 transition-all duration-200" aria-label="YouTube">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-['Outfit'] font-semibold text-lg mb-6">Navegación</h3>
                    <ul class="space-y-3">
                        <li><a href="<?php echo BASE_URL; ?>/" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Inicio</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/quienes-somos.php" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Quiénes Somos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/productos.php" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Productos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/blog.php" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Blog</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/contacto.php" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Contáctanos</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h3 class="text-white font-['Outfit'] font-semibold text-lg mb-6">Servicios</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Refugios Mineros Móviles</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Sistema Contra Incendios</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Especialistas en Polvorines</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-sky-400 text-sm transition-colors duration-200">Estructuras Metálicas</a></li>
                    </ul>
                </div>

                <!-- Contact & Certificates -->
                <div>
                    <h3 class="text-white font-['Outfit'] font-semibold text-lg mb-6">Contacto</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-sky-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-gray-400 text-sm">Lima, Perú</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-sky-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:info@technicaldelperu.pe" class="text-gray-400 hover:text-sky-400 text-sm transition-colors">info@technicaldelperu.pe</a>
                        </li>
                    </ul>
                    
                    <!-- Certificate Search CTA -->
                    <div class="mt-8 p-4 rounded-xl bg-gradient-to-br from-sky-500/10 to-blue-600/10 border border-sky-500/20">
                        <p class="text-sky-400 text-sm font-semibold mb-2">🛡️ Verifica tu certificado</p>
                        <a href="<?php echo BASE_URL; ?>/buscar-certificado.php" class="text-gray-300 hover:text-white text-xs transition-colors underline underline-offset-2 decoration-sky-500/40 hover:decoration-sky-400">
                            Buscar por RUC →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-gray-500 text-sm">
                        &copy; <?php echo date('Y'); ?> Technical del Perú. Todos los derechos reservados.
                    </p>
                    <div class="flex items-center gap-6">
                        <a href="#" class="text-gray-500 hover:text-gray-300 text-sm transition-colors">Términos</a>
                        <a href="#" class="text-gray-500 hover:text-gray-300 text-sm transition-colors">Privacidad</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================================= -->
    <!-- FLOATING BUTTONS (WhatsApp + Cotización) -->
    <!-- ============================================================= -->
    <div class="fixed bottom-6 right-6 flex flex-col gap-3 z-40" id="floating-buttons">
        <!-- WhatsApp -->
        <a href="https://wa.me/51999999999?text=Hola,%20deseo%20más%20información" target="_blank" rel="noopener"
           class="w-14 h-14 rounded-full bg-green-500 hover:bg-green-600 text-white flex items-center justify-center shadow-xl shadow-green-500/30 hover:shadow-green-500/50 transition-all duration-300 hover:scale-110"
           aria-label="WhatsApp" id="btn-whatsapp">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>
    </div>

    <!-- ============================================================= -->
    <!-- SCRIPTS -->
    <!-- ============================================================= -->
    <!-- ============================================================= -->
    <!-- SCRIPTS -->
    <!-- ============================================================= -->
    <!-- GSAP (GreenSock Animation Platform) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- Anime.js (Para efectos de letras elásticos de Julian Garnier) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    
    <!-- Swiper.js JS (Para Carrusel 3D) -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- AOS (Animate on Scroll) -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    
    <script>
        // Animaciones de revelado premium de texto mediante enfoque cinematográfico GSAP

        // Registrar Plugins de GSAP
        gsap.registerPlugin(ScrollTrigger);

        // Inicializar Swiper 3D Cards Carousel
        document.addEventListener('DOMContentLoaded', () => {
            const quienesSwiper = new Swiper('.quienes-swiper', {
                effect: 'cards',
                grabCursor: true,
                loop: true,
                autoplay: {
                    delay: 1800,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                cardsEffect: {
                    slideShadows: true,
                    rotate: true,
                    perSlideRotate: 4,
                    perSlideOffset: 12
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                speed: 550
            });
        });

        // Inicializar AOS
        AOS.init({
            duration: 900,
            easing: 'ease-out-cubic',
            once: true,
            offset: 80,
        });

        // ============================================================
        // Animaciones GSAP: Logotipo y Micro-interacciones
        // ============================================================
        
        // Rotación interactiva de la tuerca 3D interna del logo al hacer scroll
        gsap.to("#logo-gear", {
            rotation: 360,
            ease: "none",
            scrollTrigger: {
                trigger: "body",
                start: "top top",
                end: "bottom bottom",
                scrub: 1
            }
        });

        // ============================================================
        // Animaciones de Letras y Desplazamiento Parallax (Hero)
        // ============================================================
        if (document.getElementById("hero-content-wrapper")) {
            // Hacer visibles los contenedores de inmediato
            document.getElementById("hero-badge").classList.remove("opacity-0");
            document.getElementById("hero-title").classList.remove("opacity-0");
            document.getElementById("hero-desc").classList.remove("opacity-0");
            document.getElementById("hero-ctas").classList.remove("opacity-0");
            document.getElementById("hero-stats").classList.remove("opacity-0");
            
            // 1. Animación de Entrada Staggered (Carga de la página)
            const introTimeline = gsap.timeline({ defaults: { ease: "power4.out" } });
            
            // Setear estados iniciales para la entrada cinematográfica con desenfoque de lente
            gsap.set("#hero-badge", { y: -20, opacity: 0 });
            gsap.set(".hero-title-line-1", { y: 40, opacity: 0, filter: "blur(15px)" });
            gsap.set(".hero-title-line-2", { y: 40, opacity: 0, filter: "blur(15px)" });
            gsap.set("#hero-desc", { y: 30, opacity: 0, filter: "blur(10px)" });
            gsap.set("#hero-ctas", { y: 30, opacity: 0 });
            gsap.set("#hero-stats", { y: 30, opacity: 0 });
            
            introTimeline
                // Entrada del Badge inicial (desliza hacia abajo)
                .to("#hero-badge", { opacity: 1, y: 0, duration: 1.0, delay: 0.2 })
                // Revelado cinematográfico de la Línea 1 del Título (Focus y slide-up)
                .to(".hero-title-line-1", { opacity: 1, y: 0, filter: "blur(0px)", duration: 1.4 }, "-=0.6")
                // Revelado cinematográfico de la Línea 2 del Título (Focus y slide-up)
                .to(".hero-title-line-2", { opacity: 1, y: 0, filter: "blur(0px)", duration: 1.4 }, "-=1.1")
                // Revelado cinematográfico del Subtítulo/Descripción
                .to("#hero-desc", { opacity: 1, y: 0, filter: "blur(0px)", duration: 1.2 }, "-=1.0")
                // Entrada de los botones Call to Action
                .to("#hero-ctas", { opacity: 1, y: 0, duration: 1.0 }, "-=0.8")
                // Entrada de los indicadores de estadísticas
                .to("#hero-stats", { opacity: 1, y: 0, duration: 1.0 }, "-=0.8");

            // 3. Parallax Scroll de Profundidad 3D y Desvanecimiento de todo el bloque
            gsap.to("#hero-content-wrapper", {
                scrollTrigger: {
                    trigger: "#hero-section",
                    start: "top top",
                    end: "bottom top",
                    scrub: true
                },
                y: 160, // Parallax sutil de profundidad 3D
                opacity: 0, // Desvanecimiento progresivo al hacer scroll
                ease: "none"
            });
        }

        // Pulsaciones sutiles y traslación magnética en los botones del Hero
        const heroButtons = document.querySelectorAll("#cta-productos, #cta-certificados");
        heroButtons.forEach(btn => {
            btn.addEventListener("mouseenter", () => {
                gsap.to(btn, { scale: 1.05, duration: 0.3, ease: "power2.out" });
            });
            btn.addEventListener("mouseleave", () => {
                gsap.to(btn, { scale: 1, duration: 0.3, ease: "power2.out" });
            });
        });

        // ============================================================
        // 3D Tilt y Foco de Luz (Spotlight) + Videos para Tarjetas de Enfoques
        // ============================================================
        const focusCards = document.querySelectorAll(".focus-tilt-card");
        focusCards.forEach(card => {
            const video = card.querySelector("video");
            
            card.addEventListener("mousemove", (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = -(y - centerY) / 8; 
                const rotateY = (x - centerX) / 8;  
                
                // Inclinación 3D fluida y elevación magnética
                gsap.to(card, {
                    perspective: 1000,
                    rotateX: rotateX,
                    rotateY: rotateY,
                    translateY: -10, 
                    duration: 0.2,
                    ease: "power1.out"
                });
                
                card.style.setProperty("--x", `${x}px`);
                card.style.setProperty("--y", `${y}px`);
            });
            
            card.addEventListener("mouseleave", () => {
                // Retornar suavemente al centro
                gsap.to(card, {
                    rotateX: 0,
                    rotateY: 0,
                    translateY: 0,
                    duration: 0.5,
                    ease: "power2.out"
                });
            });

            // Manejo del Video en hover permanente (sin pausa)
            if (video) {
                // Asegurar la reproducción inmediata si el navegador lo permite
                video.play().catch(err => console.log("Autoplay background:", err));
                
                card.addEventListener("mouseenter", () => {
                    gsap.to(video, { opacity: 0.8, duration: 0.5, ease: "power1.out" });
                });
                card.addEventListener("mouseleave", () => {
                    gsap.to(video, { opacity: 0.35, duration: 0.4, ease: "power1.out" });
                });
            }
        });

        // ============================================================
        // Navbar scroll effect
        // ============================================================
        const navbar = document.getElementById('main-navbar');
        let lastScroll = 0;

        window.addEventListener('scroll', () => {
            const currentScroll = window.scrollY;
            
            if (currentScroll > 50) {
                navbar.classList.add('bg-gray-950/90', 'backdrop-blur-xl', 'shadow-lg', 'shadow-black/20');
            } else {
                navbar.classList.remove('bg-gray-950/90', 'backdrop-blur-xl', 'shadow-lg', 'shadow-black/20');
            }
            
            lastScroll = currentScroll;
        });

        // ============================================================
        // Mobile menu toggle
        // ============================================================
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIconOpen = document.getElementById('menu-icon-open');
        const menuIconClose = document.getElementById('menu-icon-close');

        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                const isOpen = !mobileMenu.classList.contains('hidden');
                mobileMenu.classList.toggle('hidden');
                menuIconOpen.classList.toggle('hidden');
                menuIconOpen.classList.toggle('block');
                menuIconClose.classList.toggle('hidden');
                menuIconClose.classList.toggle('block');
                mobileMenuBtn.setAttribute('aria-label', isOpen ? 'Abrir menú' : 'Cerrar menú');
            });
        }
    </script>

    <!-- Main JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js" defer></script>
</body>
</html>
