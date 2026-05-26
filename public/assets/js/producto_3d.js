/**
 * Technical del Perú — Motor Visual 3D (Three.js Engine)
 * 
 * Orquesta el renderizado WebGL de los modelos tridimensionales del catálogo.
 * Soporta carga de archivos .glb/gltf de forma dinámica y cuenta con un
 * motor interactivo de modelos procedimentales de alta fidelidad como fallback.
 * 
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('canvas-3d-container');
    const loadingOverlay = document.getElementById('viewer-loading');

    if (!container) return;

    // 1. Obtener atributos del producto
    const slug = container.getAttribute('data-slug') || '';
    const modelUrl = container.getAttribute('data-model') || '';
    const industry = container.getAttribute('data-industry') || '';

    // 2. Inicializar Variables de Three.js
    let scene, camera, renderer, controls;
    let mainModelGroup = new THREE.Group();
    let animationId = null;
    let isAnimated = true;
    let isWireframe = false;
    let clock = new THREE.Clock();

    // Referencias para animaciones procedimentales
    let scannerRing = null;
    let particleSystem = null;
    let particlePositions = [];
    let particleVelocities = [];
    const maxParticles = 120;
    let spotlights = [];

    // Posición inicial de cámara
    const targetCameraPos = new THREE.Vector3(4.5, 3.5, 6.5);
    const targetLookAt = new THREE.Vector3(0, 0.2, 0);

    // 3. Inicializar el motor 3D
    function init() {
        const width = container.clientWidth;
        const height = container.clientHeight;

        // Crear Escena
        scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0x030712, 0.05);

        // Crear Cámara
        camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
        camera.position.copy(targetCameraPos);

        // Crear Renderizador WebGL
        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        container.appendChild(renderer.domElement);

        // Agregar Grupo Principal a la escena
        scene.add(mainModelGroup);

        // Crear Controles Orbitales
        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.target.copy(targetLookAt);
        controls.minDistance = 3;
        controls.maxDistance = 14;
        controls.maxPolarAngle = Math.PI / 2.05; // Impedir traspasar el suelo técnico

        // 4. Configurar Iluminación
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.35);
        scene.add(ambientLight);

        // Luz Direccional Principal (Sombras suaves)
        const dirLight = new THREE.DirectionalLight(0xffffff, 0.85);
        dirLight.position.set(5, 10, 4);
        dirLight.castShadow = true;
        dirLight.shadow.mapSize.width = 1024;
        dirLight.shadow.mapSize.height = 1024;
        dirLight.shadow.bias = -0.001;
        scene.add(dirLight);

        // Luces Puntuales Decorativas (Neon HSL Glow)
        const bluePointLight = new THREE.PointLight(0x0ea5e9, 1.2, 15);
        bluePointLight.position.set(-4, 3, -4);
        scene.add(bluePointLight);

        const indigoPointLight = new THREE.PointLight(0x6366f1, 1, 15);
        indigoPointLight.position.set(4, -1, 4);
        scene.add(indigoPointLight);

        // 5. Suelo Técnico Grid
        const gridHelper = new THREE.GridHelper(30, 30, 0x0ea5e9, 0x1f2937);
        gridHelper.position.y = -1.8;
        // Modificar material de la grilla para suavidad
        gridHelper.material.opacity = 0.25;
        gridHelper.material.transparent = true;
        scene.add(gridHelper);

        // 6. Cargar el Activo 3D
        if (modelUrl !== '') {
            loadGLTFModel(modelUrl);
        } else {
            // Cargar generador procedimental específico según el slug
            generateProceduralModel(slug);
        }

        // Registrar Evento Resize
        window.addEventListener('resize', onWindowResize);

        // Configurar botones de la interfaz
        setupUIControls();

        // Iniciar ciclo de renderizado
        animate();
    }

    // 7. Cargador de Archivos GLTF
    function loadGLTFModel(url) {
        const loader = new THREE.GLTFLoader();
        loader.load(
            url,
            (gltf) => {
                const model = gltf.scene;
                
                // Centrar y escalar automáticamente
                const box = new THREE.Box3().setFromObject(model);
                const size = box.getSize(new THREE.Vector3());
                const center = box.getCenter(new THREE.Vector3());
                
                model.position.x += (model.position.x - center.x);
                model.position.y += (model.position.y - center.y) - 1.8; // Alinear con el suelo
                model.position.z += (model.position.z - center.z);
                
                const maxDim = Math.max(size.x, size.y, size.z);
                if (maxDim > 0) {
                    const scale = 3.5 / maxDim;
                    model.scale.set(scale, scale, scale);
                }

                model.traverse((node) => {
                    if (node.isMesh) {
                        node.castShadow = true;
                        node.receiveShadow = true;
                        if (node.material) {
                            node.material.roughness = Math.max(node.material.roughness, 0.25);
                        }
                    }
                });

                mainModelGroup.add(model);
                hideLoading();
            },
            (xhr) => {
                // Progreso de carga
                if (xhr.total > 0) {
                    const percent = Math.round((xhr.loaded / xhr.total) * 100);
                    const loadingText = document.querySelector('#viewer-loading p');
                    if (loadingText) loadingText.textContent = `Descargando: ${percent}%...`;
                }
            },
            (error) => {
                console.error('Error al cargar GLTF. Cargando fallback procedimental:', error);
                generateProceduralModel(slug);
            }
        );
    }

    // 8. Generadores Procedimentales (Failsafe Fallback)
    function generateProceduralModel(productSlug) {
        // Limpiar grupo principal
        while(mainModelGroup.children.length > 0){ 
            mainModelGroup.remove(mainModelGroup.children[0]); 
        }

        const isRefugio = productSlug.includes('refugio-minero');
        const isIncendio = productSlug.includes('supresion') || productSlug.includes('incendios');
        const isPolvorin = productSlug.includes('polvorin') || productSlug.includes('explosivos');
        const isEstructura = productSlug.includes('estructura') || productSlug.includes('nave');

        if (isRefugio) {
            buildRefugioProcedural();
        } else if (isIncendio) {
            buildIncendioProcedural();
        } else if (isPolvorin) {
            buildPolvorinProcedural();
        } else if (isEstructura) {
            buildEstructuraProcedural();
        } else {
            // Modelo por defecto si no coincide el slug
            buildRefugioProcedural();
        }

        hideLoading();
    }

    // A. DISEÑO PROCEDIMENTAL: REFUGIO MINERO
    function buildRefugioProcedural() {
        const refugioGroup = new THREE.Group();

        // 1. Cuerpo Principal Cápsula (Acero Galvanizado)
        const bodyGeo = new THREE.CylinderGeometry(0.9, 0.9, 2.6, 32);
        bodyGeo.rotateZ(Math.PI / 2); // Acostado
        const bodyMat = new THREE.MeshStandardMaterial({
            color: 0x4b5563,
            roughness: 0.15,
            metalness: 0.9,
            bumpScale: 0.05
        });
        const bodyMesh = new THREE.Mesh(bodyGeo, bodyMat);
        bodyMesh.castShadow = true;
        bodyMesh.receiveShadow = true;
        refugioGroup.add(bodyMesh);

        // 2. Extremos Esféricos (Domos de sellado)
        const capGeo = new THREE.SphereGeometry(0.9, 32, 16, 0, Math.PI * 2, 0, Math.PI / 2);
        
        const leftCap = new THREE.Mesh(capGeo, bodyMat);
        leftCap.position.x = -1.3;
        leftCap.rotateZ(Math.PI / 2);
        leftCap.castShadow = true;
        refugioGroup.add(leftCap);

        const rightCap = new THREE.Mesh(capGeo, bodyMat);
        rightCap.position.x = 1.3;
        rightCap.rotateZ(-Math.PI / 2);
        rightCap.castShadow = true;
        refugioGroup.add(rightCap);

        // 3. Anillos de Sellado Estructural (Glow Celeste)
        const ringGeo = new THREE.TorusGeometry(0.92, 0.04, 8, 32);
        ringGeo.rotateY(Math.PI / 2);

        const ringMat = new THREE.MeshBasicMaterial({ color: 0x0ea5e9 });

        const ring1 = new THREE.Mesh(ringGeo, ringMat);
        ring1.position.x = -0.7;
        refugioGroup.add(ring1);

        const ring2 = new THREE.Mesh(ringGeo, ringMat);
        ring2.position.x = 0.7;
        refugioGroup.add(ring2);

        // 4. Escáner Láser de Diagnóstico de Vida (Anillo Móvil Animado)
        const scannerGeo = new THREE.TorusGeometry(0.94, 0.03, 8, 32);
        scannerGeo.rotateY(Math.PI / 2);
        const scannerMat = new THREE.MeshBasicMaterial({ 
            color: 0x38bdf8,
            transparent: true,
            opacity: 0.8
        });
        scannerRing = new THREE.Mesh(scannerGeo, scannerMat);
        refugioGroup.add(scannerRing);

        // 5. Escotilla de Acceso Frontal
        const hatchGeo = new THREE.CylinderGeometry(0.45, 0.45, 0.15, 24);
        hatchGeo.rotateX(Math.PI / 2);
        const hatchMat = new THREE.MeshStandardMaterial({
            color: 0x1f2937,
            roughness: 0.3,
            metalness: 0.8
        });
        const hatch = new THREE.Mesh(hatchGeo, hatchMat);
        hatch.position.set(0, 0, 0.82);
        hatch.castShadow = true;
        refugioGroup.add(hatch);

        // Pequeño vidrio circular en escotilla
        const glassGeo = new THREE.CylinderGeometry(0.18, 0.18, 0.05, 16);
        glassGeo.rotateX(Math.PI / 2);
        const glassMat = new THREE.MeshBasicMaterial({ color: 0x0ea5e9, transparent: true, opacity: 0.6 });
        const glass = new THREE.Mesh(glassGeo, glassMat);
        glass.position.set(0, 0, 0.91);
        refugioGroup.add(glass);

        // Alinear cápsula completa al suelo técnico
        refugioGroup.position.y = 0.2;
        mainModelGroup.add(refugioGroup);
    }

    // B. DISEÑO PROCEDIMENTAL: SISTEMA CONTRA INCENDIOS
    function buildIncendioProcedural() {
        const incendioGroup = new THREE.Group();

        // 1. Cilindro Presurizado de Extinción (Rojo Industrial)
        const tankGeo = new THREE.CylinderGeometry(0.55, 0.55, 2.0, 32);
        const tankMat = new THREE.MeshStandardMaterial({
            color: 0xe11d48, // Rojo brillante
            roughness: 0.15,
            metalness: 0.75
        });
        const tank = new THREE.Mesh(tankGeo, tankMat);
        tank.position.y = 0.2;
        tank.castShadow = true;
        tank.receiveShadow = true;
        incendioGroup.add(tank);

        // Cúpula Superior Esférica
        const capGeo = new THREE.SphereGeometry(0.55, 32, 16, 0, Math.PI * 2, 0, Math.PI / 2);
        const tankCap = new THREE.Mesh(capGeo, tankMat);
        tankCap.position.y = 1.2;
        tankCap.castShadow = true;
        incendioGroup.add(tankCap);

        // 2. Válvula de Latón y Manómetro
        const valveGeo = new THREE.CylinderGeometry(0.12, 0.12, 0.35, 16);
        const brassMat = new THREE.MeshStandardMaterial({
            color: 0xeab308, // Dorado bronce
            roughness: 0.1,
            metalness: 0.95
        });
        const valve = new THREE.Mesh(valveGeo, brassMat);
        valve.position.y = 1.5;
        valve.castShadow = true;
        incendioGroup.add(valve);

        // Tubería de Salida / Boquilla de Aspersión
        const pipeGeo = new THREE.CylinderGeometry(0.06, 0.06, 0.5, 12);
        pipeGeo.rotateX(Math.PI / 2.5); // Inclinado
        const pipe = new THREE.Mesh(pipeGeo, brassMat);
        pipe.position.set(0, 1.6, 0.2);
        pipe.castShadow = true;
        incendioGroup.add(pipe);

        // 3. Sistema de Partículas de Supresión Activas (Chorro neón celeste)
        const particleGeo = new THREE.BufferGeometry();
        const positions = new Float32Array(maxParticles * 3);
        
        for (let i = 0; i < maxParticles; i++) {
            // Inicializar partículas en la punta de la boquilla
            positions[i * 3] = 0;
            positions[i * 3 + 1] = 1.7;
            positions[i * 3 + 2] = 0.4;

            particlePositions.push(new THREE.Vector3(0, 1.7, 0.4));
            // Velocidades de esparcimiento en parábola
            particleVelocities.push(new THREE.Vector3(
                (Math.random() - 0.5) * 0.8,
                Math.random() * 0.7 + 0.5,
                Math.random() * 1.5 + 1.2
            ));
        }

        particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        const particleMat = new THREE.PointsMaterial({
            color: 0x38bdf8,
            size: 0.08,
            transparent: true,
            opacity: 0.75,
            blending: THREE.AdditiveBlending
        });

        particleSystem = new THREE.Points(particleGeo, particleMat);
        incendioGroup.add(particleSystem);

        // Alinear al suelo técnico
        incendioGroup.position.y = -0.4;
        mainModelGroup.add(incendioGroup);
    }

    // C. DISEÑO PROCEDIMENTAL: POLVORÍN MÓVIL
    function buildPolvorinProcedural() {
        const polvorinGroup = new THREE.Group();

        // 1. Contenedor Blindado Base (Cubo Pesado)
        const boxGeo = new THREE.BoxGeometry(1.9, 1.4, 1.5);
        const steelMat = new THREE.MeshStandardMaterial({
            color: 0x1f2937, // Gris oscuro antracita
            roughness: 0.25,
            metalness: 0.8
        });
        const mainBox = new THREE.Mesh(boxGeo, steelMat);
        mainBox.position.y = 0.2;
        mainBox.castShadow = true;
        mainBox.receiveShadow = true;
        polvorinGroup.add(mainBox);

        // 2. Ribetes Estructurales de Esquina (Amarillo Tráfico / Blindaje)
        const frameMat = new THREE.MeshStandardMaterial({
            color: 0xeab308, // Amarillo de seguridad
            roughness: 0.2,
            metalness: 0.6
        });

        // Columnas angulares verticales
        const angleGeo = new THREE.BoxGeometry(0.12, 1.44, 0.12);
        
        const c1 = new THREE.Mesh(angleGeo, frameMat);
        c1.position.set(-0.96, 0.2, -0.76);
        polvorinGroup.add(c1);

        const c2 = new THREE.Mesh(angleGeo, frameMat);
        c2.position.set(0.96, 0.2, -0.76);
        polvorinGroup.add(c2);

        const c3 = new THREE.Mesh(angleGeo, frameMat);
        c3.position.set(-0.96, 0.2, 0.76);
        polvorinGroup.add(c3);

        const c4 = new THREE.Mesh(angleGeo, frameMat);
        c4.position.set(0.96, 0.2, 0.76);
        polvorinGroup.add(c4);

        // 3. Pantalla de Acceso Holográfico SUCAMEC (Verde Neón)
        const padGeo = new THREE.BoxGeometry(0.25, 0.35, 0.05);
        const padMat = new THREE.MeshStandardMaterial({ color: 0x374151, roughness: 0.4 });
        const keyPad = new THREE.Mesh(padGeo, padMat);
        keyPad.position.set(0, 0.3, 0.76);
        polvorinGroup.add(keyPad);

        const screenGeo = new THREE.PlaneGeometry(0.18, 0.14);
        const screenMat = new THREE.MeshBasicMaterial({ 
            color: 0x10b981, // Verde neón brillante
            transparent: true,
            opacity: 0.85
        });
        const screen = new THREE.Mesh(screenGeo, screenMat);
        screen.position.set(0, 0.38, 0.79);
        polvorinGroup.add(screen);

        // Alinear al suelo técnico
        polvorinGroup.position.y = -0.3;
        mainModelGroup.add(polvorinGroup);
    }

    // D. DISEÑO PROCEDIMENTAL: ESTRUCTURAS METÁLICAS
    function buildEstructuraProcedural() {
        const estructuraGroup = new THREE.Group();

        // Material Acero Estructural Pesado (Gris Texturizado)
        const structuralMat = new THREE.MeshStandardMaterial({
            color: 0x374151,
            roughness: 0.2,
            metalness: 0.88
        });

        // 1. Columnas Principales Verticales (Perfiles H/I)
        const pillarGeo = new THREE.BoxGeometry(0.2, 2.6, 0.2);
        
        const leftPillar = new THREE.Mesh(pillarGeo, structuralMat);
        leftPillar.position.set(-1.4, 0.3, 0);
        leftPillar.castShadow = true;
        leftPillar.receiveShadow = true;
        estructuraGroup.add(leftPillar);

        const rightPillar = new THREE.Mesh(pillarGeo, structuralMat);
        rightPillar.position.set(1.4, 0.3, 0);
        rightPillar.castShadow = true;
        rightPillar.receiveShadow = true;
        estructuraGroup.add(rightPillar);

        // 2. Viga Rafter Horizontal Superior
        const rafterGeo = new THREE.BoxGeometry(3.0, 0.2, 0.2);
        const topRafter = new THREE.Mesh(rafterGeo, structuralMat);
        topRafter.position.set(0, 1.5, 0);
        topRafter.castShadow = true;
        estructuraGroup.add(topRafter);

        // 3. Soportes Diagonales (Truss Frame de Refuerzo)
        const strutGeo = new THREE.BoxGeometry(0.12, 0.7, 0.12);
        
        const leftStrut = new THREE.Mesh(strutGeo, structuralMat);
        leftStrut.position.set(-1.0, 1.2, 0);
        leftStrut.rotateZ(-Math.PI / 4); // Inclinación a 45 grados
        leftStrut.castShadow = true;
        estructuraGroup.add(leftStrut);

        const rightStrut = new THREE.Mesh(strutGeo, structuralMat);
        rightStrut.position.set(1.0, 1.2, 0);
        rightStrut.rotateZ(Math.PI / 4);
        rightStrut.castShadow = true;
        estructuraGroup.add(rightStrut);

        // 4. Focos de Iluminación Aéreos (Spotlights Tecnológicos)
        const spotConeGeo = new THREE.ConeGeometry(0.15, 0.3, 16);
        spotConeGeo.rotateX(Math.PI); // Apuntando hacia abajo
        const spotConeMat = new THREE.MeshStandardMaterial({ color: 0x1f2937, roughness: 0.3 });
        
        const spot1 = new THREE.Mesh(spotConeGeo, spotConeMat);
        spot1.position.set(-0.8, 1.4, 0);
        estructuraGroup.add(spot1);

        const spot2 = new THREE.Mesh(spotConeGeo, spotConeMat);
        spot2.position.set(0.8, 1.4, 0);
        estructuraGroup.add(spot2);

        // Conos de Luz Emisivos Gradientes (Holográficos)
        const beamGeo = new THREE.CylinderGeometry(0.15, 0.9, 2.5, 16, 1, true);
        beamGeo.translate(0, -1.25, 0); // Desplazar pivote a la punta del cono
        const beamMat = new THREE.MeshBasicMaterial({
            color: 0xeab308, // Luz amarilla brillante
            transparent: true,
            opacity: 0.18,
            blending: THREE.AdditiveBlending,
            side: THREE.DoubleSide
        });

        const beam1 = new THREE.Mesh(beamGeo, beamMat);
        beam1.position.set(-0.8, 1.3, 0);
        estructuraGroup.add(beam1);
        spotlights.push(beam1);

        const beam2 = new THREE.Mesh(beamGeo, beamMat);
        beam2.position.set(0.8, 1.3, 0);
        estructuraGroup.add(beam2);
        spotlights.push(beam2);

        // Alinear al suelo técnico
        estructuraGroup.position.y = -0.5;
        mainModelGroup.add(estructuraGroup);
    }

    // 9. Ciclo de Animación Render Loop
    function animate() {
        animationId = requestAnimationFrame(animate);

        const time = clock.getElapsedTime();

        // Control dinámico de animaciones
        if (isAnimated) {
            // Rotación global sutil del grupo principal
            mainModelGroup.rotation.y = time * 0.15;

            // A. Animación Refugio: Escáner deslizante
            if (scannerRing) {
                scannerRing.position.x = Math.sin(time * 2.2) * 1.25;
            }

            // B. Animación Incendio: Chorro de partículas
            if (particleSystem) {
                const positions = particleSystem.geometry.attributes.position.array;
                
                for (let i = 0; i < maxParticles; i++) {
                    const pos = particlePositions[i];
                    const vel = particleVelocities[i];

                    // Actualizar posición de la partícula en física de gravedad
                    pos.x += vel.x * 0.016;
                    pos.y += vel.y * 0.016;
                    pos.z += vel.z * 0.016;

                    // Fuerza de gravedad sutil hacia abajo
                    vel.y -= 0.12 * 0.016;

                    // Escribir en el arreglo de posiciones
                    positions[i * 3] = pos.x;
                    positions[i * 3 + 1] = pos.y;
                    positions[i * 3 + 2] = pos.z;

                    // Reiniciar partícula si excede distancia
                    if (pos.y < -1.8 || pos.z > 5.5) {
                        pos.set(0, 1.7, 0.4);
                        vel.set(
                            (Math.random() - 0.5) * 0.8,
                            Math.random() * 0.7 + 0.5,
                            Math.random() * 1.5 + 1.2
                        );
                    }
                }
                particleSystem.geometry.attributes.position.needsUpdate = true;
            }

            // D. Animación Estructura: Destello de reflectores
            if (spotlights.length > 0) {
                spotlights.forEach(beam => {
                    beam.material.opacity = 0.12 + Math.sin(time * 4) * 0.06;
                });
            }
        }

        // Actualizar controles orbitales
        controls.update();

        // Renderizar escena
        renderer.render(scene, camera);
    }

    // 10. Controles Flotantes del Panel
    function setupUIControls() {
        const btnWireframe = document.getElementById('btn-3d-wireframe');
        const btnPlay = document.getElementById('btn-3d-play');
        const btnReset = document.getElementById('btn-3d-reset');
        
        const iconPlay = document.getElementById('icon-play');
        const iconPause = document.getElementById('icon-pause');

        // Alternador de Wireframe
        if (btnWireframe) {
            btnWireframe.addEventListener('click', () => {
                isWireframe = !isWireframe;
                btnWireframe.classList.toggle('active', isWireframe);
                
                mainModelGroup.traverse((node) => {
                    if (node.isMesh && node.material) {
                        node.material.wireframe = isWireframe;
                        // Modificar color de Wireframe si es de neón para mayor estética
                        if (isWireframe) {
                            node.material.color.setHex(0x0ea5e9);
                        } else {
                            // Restaurar color nativo
                            if (slug.includes('refugio')) node.material.color.setHex(0x4b5563);
                            else if (slug.includes('incendio') || slug.includes('supresion')) node.material.color.setHex(0xe11d48);
                            else if (slug.includes('polvorin')) node.material.color.setHex(0x1f2937);
                            else if (slug.includes('estructura')) node.material.color.setHex(0x374151);
                        }
                    }
                });
            });
        }

        // Play / Pause de la Animación
        if (btnPlay) {
            btnPlay.addEventListener('click', () => {
                isAnimated = !isAnimated;
                btnPlay.classList.toggle('active', isAnimated);

                if (isAnimated) {
                    iconPlay.classList.add('hidden');
                    iconPause.classList.remove('hidden');
                } else {
                    iconPlay.classList.remove('hidden');
                    iconPause.classList.add('hidden');
                }
            });
        }

        // Reinicio suave de la Cámara (GSAP Interpolación)
        if (btnReset) {
            btnReset.addEventListener('click', () => {
                // Si GSAP está disponible cargado en footer
                if (window.gsap) {
                    window.gsap.to(camera.position, {
                        x: targetCameraPos.x,
                        y: targetCameraPos.y,
                        z: targetCameraPos.z,
                        duration: 0.8,
                        ease: "power2.out"
                    });
                    window.gsap.to(controls.target, {
                        x: targetLookAt.x,
                        y: targetLookAt.y,
                        z: targetLookAt.z,
                        duration: 0.8,
                        ease: "power2.out",
                        onUpdate: () => controls.update()
                    });
                } else {
                    // Fallback directo sin GSAP
                    camera.position.copy(targetCameraPos);
                    controls.target.copy(targetLookAt);
                    controls.update();
                }
            });
        }
    }

    // 11. Ocultar Overlay de Carga
    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 700);
        }
    }

    // 12. Ajustar al redimensionar ventana
    function onWindowResize() {
        if (!container) return;
        const width = container.clientWidth;
        const height = container.clientHeight;

        camera.aspect = width / height;
        camera.updateProjectionMatrix();

        renderer.setSize(width, height);
    }

    // Iniciar Ejecución
    init();
});
