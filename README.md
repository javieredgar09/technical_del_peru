# 🏢 Technical del Perú

**Plataforma integral para gestión y verificación de certificados profesionales con catálogo de productos y visor 3D**

[![GitHub](https://img.shields.io/badge/GitHub-View_on_GitHub-blue?logo=github)](https://github.com/javieredgar09/technical_del_peru)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.0+-blue)](https://www.php.net/)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7+-blue)](https://www.mysql.com/)

---

## 📋 Descripción

Technical del Perú es una aplicación web completa diseñada para empresas de certificación y capacitación que necesitan:

- ✅ Gestionar y verificar certificados digitales
- ✅ Generar códigos QR únicos por certificado
- ✅ Exportar certificados en PDF con firma digital
- ✅ Catálogo de productos con visor 3D interactivo
- ✅ Blog con contenido SEO optimizado
- ✅ Panel administrativo con roles y permisos
- ✅ Búsqueda pública por RUC

---

## 🚀 Características Principales

### 🔐 Seguridad & Autenticación
- Sistema de login seguro con `password_verify()`
- Gestión de roles (Admin, Vendedor, Capacitador, Usuario)
- Protección CSRF en formularios
- Sesiones seguras con httpOnly y SameSite
- Prepared statements en todas las consultas SQL

### 📄 Gestión de Certificados
- CRUD completo de certificados en panel admin
- Generación automática de códigos únicos
- QR dinámicos con librería `php-qrcode`
- Descarga de PDF con firma digital integrada
- Búsqueda pública por RUC (11 dígitos)

### 🎨 Frontend Moderno
- Diseño responsive con **Tailwind CSS v4**
- Animaciones fluidas con **AOS** (Animate On Scroll)
- Interacciones avanzadas con **GSAP**
- Visor 3D interactivo con **Three.js**
- Videos de fondo en secciones hero

### 📱 Catálogo de Productos
- Grid de productos con filtros dinámicos
- Búsqueda de texto en tiempo real
- Visor 3D procedural como fallback
- Generadores 3D procedimentales
- Wireframe neón y controles de cámara
- Integración con WhatsApp para cotizaciones

### 📚 Blog & SEO
- Sistema de blog con slug URL-friendly
- Meta tags dinámicos (OG, title, description)
- Paginación de artículos
- Optimizado para motores de búsqueda

### 🎯 Panel Administrativo
- Dashboard intuitivo
- Gestión de configuración de firma digital
- Módulos organizados por funcionalidad
- Edición de banners y secciones

---

## 📦 Requisitos del Sistema

- **PHP** 8.0 o superior
- **MySQL** 5.7 o superior
- **Node.js** 16+ (para compilar Tailwind)
- **Composer** (gestor de dependencias PHP)
- **pnpm** (gestor de dependencias NPM)

---

## ⚙️ Instalación Rápida

### 1. Clonar el repositorio
```bash
git clone https://github.com/javieredgar09/technical_del_peru.git
cd technical_del_peru
```

### 2. Instalar dependencias PHP
```bash
composer install
```

### 3. Instalar dependencias Node.js
```bash
pnpm install
```

### 4. Configurar base de datos
```bash
# Importar estructura
mysql -u root -p < sql/estructura.sql
```

### 5. Configurar conexión a BD
Editar `src/config/db.php` con tus credenciales:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'technical_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 6. Compilar Tailwind CSS
```bash
# Terminal 1: Watch mode (desarrollo)
pnpm watch:css

# O una sola vez
pnpm build:css
```

### 7. Iniciar servidor PHP
```bash
# Terminal 2
cd public
php -S localhost:8000
```

Accede a: **http://localhost:8000**

---

## 👤 Credenciales de Demostración

**Panel Admin:**
- Email: `admin@technicaldelperu.pe`
- Contraseña: `Admin2026!`

> ⚠️ Cambiar estas credenciales en producción

---

## 📁 Estructura del Proyecto

```
technical_del_peru/
├── admin/                          # Panel administrativo
│   ├── modules/
│   │   ├── certificados/          # CRUD de certificados
│   │   └── configuracion/         # Gestión de firma digital
│   ├── login.php
│   └── logout.php
├── public/                         # Frontend público
│   ├── index.php                  # Home
│   ├── productos.php              # Catálogo
│   ├── producto.php               # Visor 3D
│   ├── blog.php                   # Blog
│   ├── articulo.php               # Artículos
│   ├── buscar-certificado.php     # Búsqueda RUC
│   ├── verificar-certificado.php  # Verificación pública
│   ├── descargar-certificado.php  # Descarga PDF
│   └── assets/
│       ├── css/
│       ├── js/
│       └── uploads/
├── src/
│   ├── config/
│   │   ├── db.php
│   │   └── session.php
│   ├── models/
│   │   ├── CertificadoModel.php
│   │   ├── ProductoModel.php
│   │   └── BlogModel.php
│   ├── helpers/
│   │   ├── auth_helper.php
│   │   ├── pdf_helper.php
│   │   ├── qr_helper.php
│   │   └── functions_helper.php
│   └── views/
├── sql/
│   └── estructura.sql
├── package.json
├── composer.json
└── tailwind.config.js
```

---

## 📚 Documentación Completa

Ver [ROADMAP.md](ROADMAP.md) para:
- Descripción detallada de cada módulo
- Checklist de implementación
- Estado de desarrollo actual
- Dependencias y bloqueadores

---

## 🛠️ Dependencias

### PHP (Composer)
- **dompdf/dompdf** ^3.1 - Generación de PDFs
- **chillerlan/php-qrcode** ^5.0 - Generación de códigos QR

### JavaScript (pnpm)
- **tailwindcss** ^4.0 - Framework CSS
- **postcss** ^8.0 - Procesador CSS
- **aos** ^3.0 - Animaciones al scroll
- **gsap** ^3.12 - Animaciones avanzadas
- **three** ^r128 - Motor 3D

---

## 🎯 Flujo Principal

### Para Usuarios Finales:
1. **Home** → Ver productos y servicios
2. **Productos** → Explorar catálogo con visor 3D
3. **Blog** → Leer artículos
4. **Buscar Certificado** → Ingresa RUC
5. **Verificar Certificado** → Ve detalles y descarga PDF

### Para Administradores:
1. **Login** (admin@technicaldelperu.pe)
2. **Gestionar Certificados** → Crear, editar, eliminar
3. **Configurar Firma Digital** → Subir imagen PNG
4. **Ver Productos** → Administrar catálogo
5. **Gestionar Banners** → Contenido dinámico

---

## 🔒 Seguridad Implementada

✅ **Prepared Statements** - Todas las consultas usan parámetros seguros  
✅ **Password Hashing** - Contraseñas hasheadas con `password_hash()`  
✅ **CSRF Protection** - Tokens en todos los formularios  
✅ **Session Security** - httpOnly, SameSite=Strict  
✅ **Input Validation** - Validación del lado servidor  
✅ **XSS Prevention** - `htmlspecialchars()` en outputs  
✅ **SQL Injection Prevention** - Prepared statements  

---

## 📊 Estado de Desarrollo

| Módulo | Estado | Completitud |
|--------|--------|-------------|
| Configuración base | ✅ Completado | 100% |
| Autenticación y roles | ✅ Completado | 100% |
| Gestión de Certificados | ✅ Completado | 100% |
| Búsqueda por RUC | ✅ Completado | 100% |
| Verificación y PDF | ✅ Completado | 100% |
| Panel Admin Certificados | ✅ Completado | 100% |
| Firma Digital | ✅ Completado | 100% |
| Frontend Principal | ✅ Completado | 100% |
| Catálogo 3D | ✅ Completado | 100% |
| Blog y SEO | ✅ Completado | 100% |
| Banners y Secciones | ✅ Completado | 100% |
| Contacto y Logs | ⏳ Pendiente | 0% |
| Seguridad y Pruebas | ⏳ Pendiente | 0% |

---

## 🚀 Despliegue en Producción

### Opción 1: Hosting Tradicional (Recomendado para PHP)
- **Hostinger**, **GoDaddy**, **Bluehost**
- Soportan PHP 8+ y MySQL
- FTP para subir archivos

### Opción 2: VPS (Control Total)
- **DigitalOcean**, **Linode**, **AWS Lightsail**
- Instala Apache/Nginx + PHP + MySQL
- Máximo control y escalabilidad

### Opción 3: Cloud con Contenedores
- **Railway.app** (soporta PHP)
- **Render** (con Docker)
- **AWS App Runner**

> **Nota:** Vercel no soporta PHP nativamente. Para Vercel, necesitarías convertir a Next.js/Node.js.

---

## 📸 Pantallas Principales

### Home
- Hero section con video de fondo
- 4 enfoques empresariales
- Productos destacados
- Animaciones fluidas

### Catálogo de Productos
- Grid responsivo glassmorphic
- Filtros por industria
- Visor 3D interactivo
- Búsqueda de texto en tiempo real

### Panel Admin
- Dashboard intuitivo
- CRUD de certificados
- Gestión de firma digital
- Edición de contenido dinámico

---

## 📝 Comandos Útiles

```bash
# Desarrollo
pnpm watch:css       # Watch Tailwind en desarrollo
php -S localhost:8000  # Servidor PHP local

# Producción
pnpm build:css       # Compilar Tailwind una sola vez
composer install --no-dev  # Instalar sin dependencias de desarrollo

# Base de datos
mysql -u root -p technical_db < sql/estructura.sql
mysqldump -u root -p technical_db > backup.sql
```

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Para cambios importantes:

1. Fork el repositorio
2. Crea una rama (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📞 Soporte

- **GitHub Issues:** [Reporta un bug](https://github.com/javieredgar09/technical_del_peru/issues)
- **Email:** javieredgar9@gmail.com
- **GitHub:** [@javieredgar09](https://github.com/javieredgar09)

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Ver archivo `LICENSE` para detalles.

---

## 👨‍💻 Autor

**Javier Edgar** - [@javieredgar09](https://github.com/javieredgar09)

Desarrollador Full Stack especializado en PHP y JavaScript.

---

## ⭐ Reconocimientos

- **Tailwind CSS** - Framework CSS utility-first
- **Three.js** - Motor 3D web
- **GSAP** - Librería de animaciones profesionales
- **AOS** - Animaciones al scroll
- **Dompdf** - Generación de PDFs en PHP
- **php-qrcode** - Códigos QR en PHP

---

**Última actualización:** 26 de mayo de 2026

🌟 Si te gustó el proyecto, ¡dale una estrella en GitHub!