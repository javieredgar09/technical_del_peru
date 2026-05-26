<?php
/**
 * Technical del Perú — Login del Panel de Administración (Premium Redesign)
 * 
 * Permite a los usuarios autorizados iniciar sesión en el sistema.
 * Cuenta con fondo de video industrial real (hero.mp4) y un generador interactivo
 * de partículas de chispas de soldadura sobre una interfaz glassmorphic impecable.
 * 
 * @version 1.1.0
 */

// Cargar configuración y helpers
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/helpers/auth_helper.php';
require_once __DIR__ . '/../src/helpers/functions_helper.php';

// Iniciar sesión de forma segura
initSession();

// Si ya está logueado, redirigir al panel principal
if (isLoggedIn()) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF token
        requireCSRF();
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $emailValue = sanitizeInput($email);
        
        if (empty($email) || empty($password)) {
            $error = 'Por favor, ingrese el correo electrónico y la contraseña.';
            registrarLog('Intento de login fallido', 'Campos de entrada vacíos');
        } else {
            $db = getDB();
            $stmt = $db->prepare('
                SELECT u.*, r.nombre AS rol_nombre 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE u.email = ? 
                LIMIT 1
            ');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ((int)$user['activo'] !== 1) {
                    $error = 'Su cuenta ha sido desactivada. Por favor, póngase en contacto con soporte técnico.';
                    registrarLog('Intento de login fallido', "Usuario inactivo intentó acceder: {$email}", 'usuarios', $user['id']);
                } else {
                    // Login exitoso — Regenerar ID de sesión
                    regenerateSession();
                    
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nombre'] = $user['nombre'];
                    $_SESSION['usuario_email'] = $user['email'];
                    $_SESSION['usuario_rol'] = $user['rol_nombre'];
                    $_SESSION['usuario_rol_id'] = $user['rol_id'];
                    
                    // Actualizar marca de último acceso
                    $stmtUpdate = $db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?');
                    $stmtUpdate->execute([$user['id']]);
                    
                    // Registrar acción en logs
                    registrarLog('Login exitoso', 'El usuario ha iniciado sesión de forma segura', 'usuarios', $user['id']);
                    
                    // Redireccionar al dashboard o a la ruta previa guardada
                    $redirectUrl = $_SESSION['redirect_after_login'] ?? (ADMIN_URL . '/index.php');
                    unset($_SESSION['redirect_after_login']);
                    
                    setFlash('success', "¡Bienvenido de nuevo, {$user['nombre']}!");
                    redirect($redirectUrl);
                }
            } else {
                $error = 'El correo electrónico o la contraseña ingresada es incorrecta.';
                registrarLog('Intento de login fallido', "Credenciales incorrectas para el correo: {$email}");
            }
        }
    } catch (Exception $e) {
        $error = 'Ha ocurrido un error en el servidor. Por favor, inténtelo de nuevo más tarde.';
        error_log('Error en autenticación de login: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Iniciar Sesión | Technical del Perú Panel</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (compilado) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
    
    <style>
        /* Fix for browser autofill background making inputs white in dark theme */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #161827 inset !important;
            -webkit-text-fill-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        
        /* Blur backdrop specifically designed for login cards */
        .glass-login-card {
            background: rgba(10, 11, 20, 0.75);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-['Inter',sans-serif] antialiased h-full flex items-center justify-center relative overflow-hidden select-none">

    <!-- ============================================================= -->
    <!-- INDUSTRIAL VIDEO BACKGROUND -->
    <!-- ============================================================= -->
    <div class="absolute inset-0 z-0 overflow-hidden">
        <video autoplay muted loop playsinline class="w-full h-full object-cover opacity-85 filter brightness-[0.9]" poster="<?php echo BASE_URL; ?>/assets/images/hero-poster.jpg">
            <source src="<?php echo BASE_URL; ?>/assets/video/hero.mp4" type="video/mp4">
        </video>
        <!-- Overlay subtle tints to allow background video to be clearly visible and preserve card contrast -->
        <div class="absolute inset-0 bg-black/25"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/70 via-transparent to-gray-950/40"></div>
    </div>

    <!-- ============================================================= -->
    <!-- INTERACTIVE WELDING SPARKS CANVAS -->
    <!-- ============================================================= -->
    <canvas id="sparks-canvas" class="absolute inset-0 z-10 pointer-events-none opacity-60"></canvas>

    <!-- ============================================================= -->
    <!-- LOGIN INTERFACE CONTAINER -->
    <!-- ============================================================= -->
    <div class="relative z-20 w-full max-w-md mx-4 md:mx-auto my-auto" id="login-container">
        
        <!-- Animated Brand Logo Header -->
        <div class="flex flex-col items-center justify-center mb-6 text-center">
            <a href="<?php echo BASE_URL; ?>/" class="inline-block hover:scale-[1.02] transition-transform duration-300">
                <?php echo renderBrandLogo('h-14', true); ?>
            </a>
            <span class="text-sky-400 font-semibold text-xs uppercase tracking-widest mt-2 bg-sky-500/10 px-3 py-1 rounded-full border border-sky-500/20">
                Panel Administrativo
            </span>
        </div>

        <!-- Glassmorphism Form Card -->
        <div class="glass-login-card rounded-2xl p-8 shadow-2xl relative overflow-hidden transition-all duration-300">
            
            <div class="mb-6">
                <h2 class="text-xl font-['Outfit'] font-bold text-white mb-1">Ingreso seguro</h2>
                <p class="text-xs text-gray-400">Ingrese sus credenciales de Technical del Perú.</p>
            </div>

            <!-- Error Alerts -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-sm flex items-start gap-2.5 mb-5" role="alert" id="login-error-alert">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="flex-1"><?php echo e($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Flash message for logout/session timeout -->
            <?php echo renderFlash(); ?>

            <form action="" method="POST" class="space-y-5" id="login-form">
                
                <!-- CSRF Protection Field -->
                <?php echo csrfField(); ?>

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="text-[11px] font-semibold text-gray-300 uppercase tracking-wider block">Correo Electrónico</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </span>
                        <input type="email" id="email" name="email" required autocomplete="email"
                               placeholder="usuario@technicaldelperu.pe" 
                               value="<?php echo e($emailValue); ?>"
                               class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="text-[11px] font-semibold text-gray-300 uppercase tracking-wider block">Contraseña</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               placeholder="••••••••" 
                               class="w-full pl-10 pr-10 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 text-sm focus:outline-none focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/30 transition-all duration-300">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-white transition-colors" aria-label="Mostrar contraseña" id="password-toggle-btn">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="eye-show-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4.5 h-4.5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="eye-hide-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 rounded-xl font-semibold text-sm bg-gradient-to-r from-sky-500 to-blue-600 text-white hover:from-sky-400 hover:to-blue-500 shadow-xl shadow-sky-500/20 hover:shadow-sky-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 block text-center cursor-pointer mt-4">
                    Ingresar al Panel
                </button>
            </form>
        </div>
        
        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="<?php echo BASE_URL; ?>/" class="text-xs text-gray-500 hover:text-sky-400 flex items-center justify-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al Sitio Público
            </a>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- WELDING SPARKS INTERACTIVE PHYSICS ANIMATION -->
    <!-- ============================================================= -->
    <script>
        const canvas = document.getElementById('sparks-canvas');
        const ctx = canvas.getContext('2d');

        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        });

        // Spark Particle Class simulating welding sparks
        class Spark {
            constructor(x, y, group) {
                this.x = x;
                this.y = y;
                this.size = Math.random() * 2 + 1;
                // Velocity
                const angle = Math.random() * Math.PI * 0.4 - Math.PI * 0.2 - Math.PI * 0.5; // Upwards angled
                const speed = Math.random() * 6 + 3;
                this.vx = Math.cos(angle) * speed;
                this.vy = Math.sin(angle) * speed;
                this.gravity = 0.12;
                this.life = 1;
                this.decay = Math.random() * 0.02 + 0.008;
                // Flame colors: bright yellow, gold, hot orange, red
                const colors = ['#ffffff', '#ffe17d', '#ff9436', '#ff512f', '#ed1c24'];
                this.color = colors[Math.floor(Math.random() * colors.length)];
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;
                this.vy += this.gravity;
                this.life -= this.decay;
            }

            draw() {
                ctx.save();
                ctx.globalAlpha = this.life;
                ctx.shadowBlur = Math.random() * 6 + 4;
                ctx.shadowColor = this.color;
                
                ctx.fillStyle = this.color;
                ctx.beginPath();
                // Draw a streak for high speed sparks
                const speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
                if (speed > 1) {
                    ctx.moveTo(this.x, this.y);
                    ctx.lineTo(this.x - this.vx * 1.5, this.y - this.vy * 1.5);
                    ctx.lineWidth = this.size;
                    ctx.strokeStyle = this.color;
                    ctx.stroke();
                } else {
                    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.restore();
            }
        }

        const sparks = [];
        
        // Spawn point is bottom-left or bottom-right, simulating a worker welding/cutting
        const sparkGenerators = [
            { x: width * 0.15, y: height * 0.95 },
            { x: width * 0.85, y: height * 0.95 }
        ];

        function animate() {
            // Semi-transparent background clearing for light trails
            ctx.clearRect(0, 0, width, height);

            // Dynamically generate sparks from active generators
            sparkGenerators.forEach(gen => {
                if (Math.random() < 0.25) {
                    for (let i = 0; i < Math.floor(Math.random() * 3 + 1); i++) {
                        sparks.push(new Spark(gen.x + (Math.random() * 10 - 5), gen.y));
                    }
                }
            });

            // Random ambient sparks
            if (Math.random() < 0.15) {
                sparks.push(new Spark(Math.random() * width, height));
            }

            // Update and draw sparks
            for (let i = sparks.length - 1; i >= 0; i--) {
                const spark = sparks[i];
                spark.update();
                if (spark.life <= 0 || spark.y > height || spark.x < 0 || spark.x > width) {
                    sparks.splice(i, 1);
                } else {
                    spark.draw();
                }
            }

            requestAnimationFrame(animate);
        }

        animate();

        // Password toggler functionality
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const showIcon = document.getElementById('eye-show-icon');
            const hideIcon = document.getElementById('eye-hide-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showIcon.classList.add('hidden');
                hideIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                showIcon.classList.remove('hidden');
                hideIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
