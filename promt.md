# PROMPT: Desarrollo Web Technical del Perú

Eres un experto desarrollador full-stack. Debes construir desde cero la web para **Technical del Perú** con las siguientes especificaciones exactas.

## Objetivo
Rediseñar completamente https://technicaldelperu.pe/ transformándola en una web moderna, profesional, con alto poder de conversión, animaciones 3D y panel de administración multi-rol.  
**Requerimiento estrella:** Buscador público de certificados por número de RUC, cada certificado con código QR y firma digital.

## Tecnologías obligatorias
- Backend: PHP 8+ puro (sin frameworks)
- Base de datos: MySQL 8+ (nombre: `technical_db`)
- Frontend: HTML5, CSS3 (Tailwind CSS vía pnpm), JavaScript ES6+
- Librerías: Three.js (visor 3D), phpqrcode (QR), Dompdf (PDF), GSAP/AOS (animaciones)
- Gestor de paquetes: pnpm

## Base de datos (estructura ya creada en technical_db)
Usa las tablas: `roles`, `usuarios`, `certificados`, `config_firma`, `productos`, `banners`, `blog`, `secciones`, `logs`.  
(El script SQL se entrega aparte en `sql/estructura.sql`)

## Funcionalidades críticas

### 1. Sistema de autenticación y roles
- Login en `/admin/login.php`
- Roles: Administrador, Vendedor, Marketing, Gestor de contenido
- Control de acceso por página según rol

### 2. Gestión de certificados (módulo estrella)
- CRUD completo (solo Admin y Vendedor)
- Campos: RUC (11 dígitos), razón social, nombre participante, tipo certificado, fechas emisión/vencimiento
- Al crear: generar automáticamente `codigo_verificacion` (hash único) y código QR (imagen PNG) que apunte a `https://dominio/verificar-certificado.php?codigo=...`
- Guardar ruta del QR en la BD
- Página pública `verificar-certificado.php` que muestra todos los datos del certificado + QR + firma digital (desde tabla `config_firma`)
- Botón para descargar PDF (`descargar-certificado.php`) que genera un PDF con los mismos datos + QR + firma usando Dompdf

### 3. Buscador público por RUC
- Ruta: `/buscar-certificado.php`
- Formulario que recibe RUC (validar 11 dígitos)
- Consultar tabla `certificados` por RUC y mostrar lista de certificados encontrados, cada uno con enlace a su verificación individual

### 4. Frontend principal
- **Hero:** Video MP4 animado (cargado desde `public/assets/video/hero.mp4`) con autoplay, muted, loop
- **4 enfoques estratégicos** (cards animadas):
  - Refugios Mineros Móviles
  - Sistema Contra Incendios
  - Especialistas en Polvorines
  - Fabricantes de estructuras metálicas
- **Catálogo de productos** con filtros por industria, imágenes destacadas, efecto hover zoom
- **Visor 3D** en página de producto individual usando Three.js (modelo .glb)
- **Blog** con URLs amigables y meta tags dinámicos
- **Formulario de contacto** funcional (guarda en BD y envía correo)
- **Botones flotantes** de WhatsApp y cotización

### 5. Panel de administración
- Módulos: Usuarios, Productos, Certificados, Banners, Blog, Secciones editables (texto del home, quiénes somos, etc.), Logs de actividad
- Subida de firma digital (PNG) en `admin/modules/configuracion/firma.php`
- Editor de textos (CKEditor o textarea simple)

### 6. Seguridad
- Prepared statements en todas las consultas
- `htmlspecialchars()` en todas las salidas
- CSRF tokens en formularios POST
- Contraseñas hasheadas con `password_hash()`
- Validación estricta de subida de archivos (extensiones permitidas: .jpg, .png, .webp, .mp4, .glb, .gltf, .pdf)

## Estructura de carpetas (obligatoria)
technical_del_peru/
├── public/
│ ├── assets/
│ │ ├── css/input.css (Tailwind)
│ │ ├── css/styles.css (generado)
│ │ ├── js/main.js
│ │ ├── js/three-init.js
│ │ ├── video/hero.mp4
│ │ ├── images/
│ │ └── uploads/
│ │ ├── modelos_3d/
│ │ ├── qrcodes/
│ │ └── firmas/
│ ├── index.php
│ ├── buscar-certificado.php
│ ├── verificar-certificado.php
│ ├── descargar-certificado.php
│ ├── productos.php
│ ├── producto.php
│ ├── blog.php
│ ├── articulo.php
│ └── contacto.php
├── src/
│ ├── config/db.php
│ ├── config/session.php
│ ├── models/CertificadoModel.php
│ ├── models/ProductoModel.php
│ ├── helpers/qr_helper.php
│ ├── helpers/pdf_helper.php
│ ├── libs/phpqrcode/
│ └── views/header.php, footer.php
├── admin/
│ ├── login.php
│ ├── index.php
│ ├── logout.php
│ └── modules/
│ ├── certificados/
│ └── configuracion/firma.php
├── sql/estructura.sql
├── vendor/ (Composer)
├── package.json
├── tailwind.config.js
└── README.md