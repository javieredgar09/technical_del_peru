/**
 * Technical del Perú — Hero Waves Effect
 * 
 * Animación canvas de partículas con efecto de malla 3D / ondas
 * que responde al movimiento del mouse.
 * 
 * @version 1.0.0
 */

(function () {
    const canvas = document.getElementById('hero-waves-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width, height;
    let mouseX = 0, mouseY = 0;
    let animationId;

    // Configuration
    const config = {
        particleCount: 80,
        connectionDistance: 150,
        particleRadius: 2,
        speed: 0.4,
        waveAmplitude: 30,
        waveFrequency: 0.02,
        // Brand colors
        particleColor: 'rgba(59, 159, 231, ',    // brand-blue
        lineColor: 'rgba(45, 50, 80, ',           // brand-navy
        accentColor: 'rgba(59, 159, 231, ',       // brand-blue accent
    };

    // Particles array
    let particles = [];

    class Particle {
        constructor() {
            this.reset();
        }

        reset() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.baseY = this.y;
            this.vx = (Math.random() - 0.5) * config.speed;
            this.vy = (Math.random() - 0.5) * config.speed * 0.5;
            this.radius = Math.random() * config.particleRadius + 0.5;
            this.opacity = Math.random() * 0.5 + 0.2;
            this.phase = Math.random() * Math.PI * 2;
        }

        update(time) {
            // Wave motion
            this.y = this.baseY + Math.sin(this.x * config.waveFrequency + time * 0.001 + this.phase) * config.waveAmplitude;

            // Horizontal drift
            this.x += this.vx;
            this.baseY += this.vy;

            // Mouse interaction — subtle attraction
            const dx = mouseX - this.x;
            const dy = mouseY - this.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 200) {
                const force = (200 - dist) / 200 * 0.015;
                this.x += dx * force;
                this.y += dy * force;
            }

            // Wrap around edges
            if (this.x < -20) this.x = width + 20;
            if (this.x > width + 20) this.x = -20;
            if (this.baseY < -20) this.baseY = height + 20;
            if (this.baseY > height + 20) this.baseY = -20;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = config.particleColor + this.opacity + ')';
            ctx.fill();

            // Glow effect for larger particles
            if (this.radius > 1.5) {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius * 3, 0, Math.PI * 2);
                ctx.fillStyle = config.accentColor + (this.opacity * 0.1) + ')';
                ctx.fill();
            }
        }
    }

    function drawConnections(time) {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < config.connectionDistance) {
                    const opacity = (1 - dist / config.connectionDistance) * 0.25;
                    
                    // Gradient line between navy and blue
                    const gradient = ctx.createLinearGradient(
                        particles[i].x, particles[i].y,
                        particles[j].x, particles[j].y
                    );
                    gradient.addColorStop(0, config.lineColor + opacity + ')');
                    gradient.addColorStop(0.5, config.accentColor + (opacity * 0.6) + ')');
                    gradient.addColorStop(1, config.lineColor + opacity + ')');

                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = gradient;
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }
    }

    function drawWaveLines(time) {
        // Draw subtle wave lines at different depths
        const waves = [
            { y: height * 0.6, amplitude: 20, frequency: 0.008, speed: 0.0008, opacity: 0.06 },
            { y: height * 0.7, amplitude: 15, frequency: 0.012, speed: 0.001, opacity: 0.04 },
            { y: height * 0.8, amplitude: 25, frequency: 0.006, speed: 0.0006, opacity: 0.08 },
        ];

        waves.forEach(wave => {
            ctx.beginPath();
            ctx.moveTo(0, height);

            for (let x = 0; x <= width; x += 2) {
                const y = wave.y + Math.sin(x * wave.frequency + time * wave.speed) * wave.amplitude
                    + Math.sin(x * wave.frequency * 2.5 + time * wave.speed * 1.5) * (wave.amplitude * 0.3);
                ctx.lineTo(x, y);
            }

            ctx.lineTo(width, height);
            ctx.closePath();

            const gradient = ctx.createLinearGradient(0, wave.y - wave.amplitude, 0, height);
            gradient.addColorStop(0, 'rgba(59, 159, 231, ' + wave.opacity + ')');
            gradient.addColorStop(0.5, 'rgba(45, 50, 80, ' + (wave.opacity * 0.5) + ')');
            gradient.addColorStop(1, 'rgba(45, 50, 80, 0)');
            ctx.fillStyle = gradient;
            ctx.fill();
        });
    }

    function resize() {
        width = canvas.width = canvas.parentElement.offsetWidth;
        height = canvas.height = canvas.parentElement.offsetHeight;
    }

    function init() {
        resize();
        particles = [];
        
        // Adjust particle count based on screen size
        const count = Math.min(config.particleCount, Math.floor((width * height) / 15000));
        
        for (let i = 0; i < count; i++) {
            particles.push(new Particle());
        }
    }

    function animate(time) {
        ctx.clearRect(0, 0, width, height);

        // Draw wave background lines
        drawWaveLines(time);

        // Update and draw particles
        particles.forEach(p => {
            p.update(time);
            p.draw();
        });

        // Draw connections between particles
        drawConnections(time);

        animationId = requestAnimationFrame(animate);
    }

    // Mouse tracking
    canvas.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouseX = e.clientX - rect.left;
        mouseY = e.clientY - rect.top;
    });

    canvas.addEventListener('mouseleave', () => {
        mouseX = -1000;
        mouseY = -1000;
    });

    // Touch support
    canvas.addEventListener('touchmove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouseX = e.touches[0].clientX - rect.left;
        mouseY = e.touches[0].clientY - rect.top;
    }, { passive: true });

    // Resize handler
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            cancelAnimationFrame(animationId);
            init();
            animate(0);
        }, 200);
    });

    // Start
    init();
    animate(0);
})();
