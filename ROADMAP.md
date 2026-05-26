# 📋 Roadmap de Desarrollo – Technical del Perú

**Proyecto:** Technical del Perú  
**Versión:** 1.0.0  
**Estado:** En desarrollo  
**Última actualización:** 26 de mayo de 2026

---

## 📊 Progreso General

| Skill | Estado | % | Prioridad |
|-------|--------|---|-----------|
| 1. Entorno y configuración base | ✅ Completado | 100% | 🔴 Crítica |
| 2. Autenticación y roles | ✅ Completado | 100% | 🔴 Crítica |
| 3. Modelo de Certificados | ✅ Completado | 100% | 🔴 Crítica |
| 4. Buscador público por RUC | ✅ Completado | 100% | 🟡 Alta |
| 5. Verificación pública y PDF | ✅ Completado | 100% | 🟡 Alta |
| 6. Panel admin de certificados | ✅ Completado | 100% | 🟡 Alta |
| 7. Configuración de firma digital | ⏳ Pendiente | 0% | 🟡 Alta |
| 8. Frontend principal (home) | ✅ Completado | 100% | 🟡 Alta |
| 9. Catálogo de productos y visor 3D | ✅ Completado | 100% | 🟢 Media |
| 10. Blog y SEO | ⏳ Pendiente | 0% | 🟢 Media |
| 11. Gestión de banners y secciones | ⏳ Pendiente | 0% | 🟢 Media |
| 12. Formulario de contacto y logs | ⏳ Pendiente | 0% | 🟢 Baja |
| 13. Seguridad y pruebas finales | ⏳ Pendiente | 0% | 🔴 Crítica |

**Leyenda:** ⏳ Pendiente | 🔄 En progreso | ✅ Completado | ❌ Bloqueado

---

## 🛠️ SKILL 1: Entorno y configuración base

**Estado:** ✅ Completado (100%)  
**Prioridad:** 🔴 Crítica  
**Descripción:** Instalación de herramientas, BD, estructura de carpetas y configuración de conexión.

### Checklist

- [x] Instalar PHP 8+
- [x] Instalar MySQL
- [x] Instalar Composer
- [x] Instalar Node.js
- [x] Instalar pnpm
- [x] Crear base de datos `technical_db` (10 tablas, 4 roles, usuario admin)
- [x] Ejecutar `sql/estructura.sql`
- [x] Crear carpetas según estructura
- [x] Configurar `src/config/db.php` (PDO singleton, prepared statements reales)
- [x] Configurar `src/config/session.php` (httponly, samesite, CSRF, timeout)
- [x] `composer require dompdf/dompdf` + `chillerlan/php-qrcode`
- [x] `pnpm init`
- [x] `pnpm add -D tailwindcss postcss autoprefixer`
- [x] `pnpm add swiper aos gsap three`
- [x] QR library instalada vía Composer (chillerlan/php-qrcode v5)
- [x] Helpers base: auth_helper, functions_helper, qr_helper, pdf_helper
- [x] Views base: header, footer, admin_header, admin_footer
- [x] Fix tailwind.config.js para escanear archivos .php
- [x] Página index.php funcional con hero y enfoques
- [x] public/assets/js/main.js

**Completitud:** 20/20 (100%)

### Notas
- Tailwind CSS v4 configurado con PostCSS CLI — CSS compilado: 53KB
- Todas las dependencias NPM instaladas
- PHP y MySQL listos en XAMPP
- Composer packages: dompdf v3.1.5, php-qrcode v5.0.5
- Usuario admin: admin@technicaldelperu.pe / Admin2026!
- Conexión PDO verificada y funcional

### Bloqueadores
- Ninguno — SKILL completado

---

## 🔐 SKILL 2: Autenticación y roles

**Estado:** ✅ Completado (100%)  
Prioridad: 🔴 Crítica  
Descripción: Sistema de login, gestión de roles y protección de rutas.

### Checklist

- [x] Crear `admin/login.php` con formulario
- [x] Validar credenciales contra tabla `usuarios`
- [x] Implementar `password_verify()`
- [x] Iniciar sesión: guardar `usuario_id`, `nombre`, `rol`
- [x] Crear `admin/logout.php`
- [x] Proteger páginas del panel: `if(!isset($_SESSION['usuario_id']))`
- [x] Crear función de restricción por rol en helper
- [x] Aplicar restricciones en rutas admin

**Completitud:** 8/8 (100%)

### Dependencias
- ✅ Skill 1 (base de datos y sesiones)

### Arquitectura sugerida
```php
// helpers/auth_helper.php
function requireLogin() { ... }
function requireRole($role) { ... }

// admin/login.php
if($_POST) {
  $usuario = UsuarioModel->verificarCredenciales();
  if($usuario) session_start + guardardatos
}

// admin/logout.php
session_destroy()
```

---

## 📄 SKILL 3: Modelo de Certificados

**Estado:** ✅ Completado (100%)  
Prioridad: 🔴 Crítica  
Descripción: Modelo, generación de QR y lógica de certificados.

### Checklist

- [x] Crear `src/models/CertificadoModel.php`
- [x] Método `crear($datos)` con código único
- [x] Generar código: `bin2hex(random_bytes(16))`
- [x] Insertar en BD con prepared statements
- [x] Método `obtenerPorCodigo($codigo)`
- [x] Método `obtenerPorRUC($ruc)`
- [x] Método `listarTodos()`
- [x] Integrar `qr_helper.php` para generar PNG
- [x] Guardar QR en `public/assets/uploads/qrcodes/`
- [x] Actualizar ruta del QR en BD

**Completitud:** 10/10 (100%)

### Dependencias
- ✅ Skill 1 (BD y conexión)
- phpqrcode descargado

### Código base
```php
// src/models/CertificadoModel.php
class CertificadoModel {
  public function crear($ruc, $razon_social, ...) {
    $codigo = bin2hex(random_bytes(16));
    // INSERT
    // Generar QR
    // Actualizar ruta
  }
}
```

---

## 🔍 SKILL 4: Buscador público por RUC

**Estado:** ✅ Completado (100%)  
Prioridad: 🟡 Alta  
Descripción: Búsqueda pública de certificados por número de RUC.

### Checklist

- [x] Crear `public/buscar-certificado.php`
- [x] Formulario GET con campo RUC
- [x] Validar: 11 dígitos numéricos
- [x] Llamar `CertificadoModel->obtenerPorRUC($ruc)`
- [x] Mostrar resultados en lista HTML
- [x] Enlace a `verificar-certificado.php?codigo=...`
- [x] Styling con Tailwind CSS

**Completitud:** 7/7 (100%)

### Dependencias
- ✅ Skill 3 (Modelo de Certificados)
- ✅ Skill 1 (Tailwind)

---

## ✅ SKILL 5: Verificación pública y PDF

**Estado:** ✅ Completado (100%)  
Prioridad: 🟡 Alta  
Descripción: Página de verificación y descarga de certificado en PDF.

### Checklist

- [x] Crear `public/verificar-certificado.php`
- [x] Recibir `codigo` por GET
- [x] Obtener datos del certificado
- [x] Mostrar todos los datos (RUC, participante, fechas, etc.)
- [x] Mostrar imagen QR
- [x] Mostrar firma digital desde `config_firma`
- [x] Crear `public/descargar-certificado.php`
- [x] Función `generarPDFCertificado()` en `pdf_helper.php`
- [x] Generar PDF con Dompdf + imágenes + firma

**Completitud:** 9/9 (100%)

### Dependencias
- ✅ Skill 3 (Modelo de Certificados)
- ✅ Skill 1 (Dompdf instalado)

---

## 📋 SKILL 6: Panel de administración de certificados

**Estado:** ✅ Completado (100%)  
Prioridad: 🟡 Alta  
Descripción: CRUD de certificados en panel admin.

### Checklist

- [x] Crear `admin/modules/certificados/index.php`
- [x] Listar todos los certificados con paginación
- [x] Opciones: Editar, Eliminar, Ver
- [x] Crear `admin/modules/certificados/crear.php`
- [x] Formulario con campos: RUC, razón social, participante, tipo, fechas
- [x] Guardar llamando `CertificadoModel->crear()`
- [x] Crear `admin/modules/certificados/editar.php`
- [x] Crear `admin/modules/certificados/eliminar.php`
- [x] Proteger acceso: solo Admin y Vendedor
- [x] Validación del lado del servidor

**Completitud:** 10/10 (100%)

### Dependencias
- ✅ Skill 2 (Autenticación y roles)
- ✅ Skill 3 (Modelo de Certificados)

---

## 🔏 SKILL 7: Configuración de firma digital

**Estado:** ⏳ Pendiente  
**Prioridad:** 🟡 Alta  
**Descripción:** Gestión de firma digital que aparece en certificados PDF.

### Checklist

- [ ] Crear tabla `config_firma` (una sola fila)
- [ ] Campos: id, nombre_firmante, cargo, ruta_imagen, fecha_actualizacion
- [ ] Crear `admin/modules/configuracion/firma.php`
- [ ] Formulario para subir PNG (firma)
- [ ] Campo de texto para nombre del firmante
- [ ] Campo de texto para cargo
- [ ] Guardar archivo en `public/assets/uploads/firmas/firma_digital.png`
- [ ] Actualizar registro en BD
- [ ] Proteger acceso: solo Admin

**Completitud:** 0/9 (0%)

### Dependencias
- ✅ Skill 2 (Autenticación)
- ✅ Skill 1 (Carpeta uploads)

---

## 🏠 SKILL 8: Frontend principal (home, header, footer)

**Estado:** ✅ Completado (100%)  
**Prioridad:** 🟡 Alta  
**Descripción:** Página principal responsive con animaciones.

### Checklist

- [x] Crear `public/index.php`
- [x] Sección hero con video de fondo
- [x] Sección de 4 enfoques empresariales (con video loops interactivos en hover)
- [x] Sección de productos destacados
- [x] Header con menú: Inicio, Quiénes Somos, Servicios, Productos, Capacitaciones, Validar Cert., Contáctanos
- [x] Footer con enlace a buscador de RUC
- [x] Styling responsive con Tailwind CSS
- [x] Implementar AOS (scroll animations)
- [x] Implementar GSAP (hover gear rotation & CTA hover magnet scale)
- [x] Leer productos desde BD (tabla `productos` con 4 semillas premium reales)

**Completitud:** 10/10 (100%)

### Dependencias
- ✅ Skill 1 (Tailwind, AOS, GSAP)
- ✅ Skill 7 (Datos de configuración)

### Estructura HTML base
```php
<nav><!-- Header --></nav>
<section><!-- Hero + Video --></section>
<section><!-- 4 Enfoques --></section>
<section><!-- Productos Destacados (from BD) --></section>
<footer><!-- Links --></footer>
```

---

## 🎨 SKILL 9: Catálogo de productos y visor 3D

**Estado:** ✅ Completado (100%)  
**Prioridad:** 🟢 Media  
**Descripción:** Grid de productos con filtros y visor 3D interactivo con Three.js.

### Checklist

- [x] Crear `src/models/ProductoModel.php` con prepared statements seguros
- [x] Crear `public/productos.php` con grilla responsiva glassmorphic
- [x] Filtros por industria y buscador dinámico de texto
- [x] Crear `public/producto.php` con ficha técnica en 2 columnas
- [x] Integrar Three.js y OrbitControls desde CDN estable
- [x] Diseñar visor 3D con iluminación de sombras y grilla técnica
- [x] Implementar 4 generadores 3D procedimentales como fallback de modelos
- [x] Implementar alternador de wireframe neón y reset de cámara
- [x] Enlace de cotización comercial directa vía API de WhatsApp
- [x] Validar responsive en móviles y tablets

**Completitud:** 10/10 (100%)

### Dependencias
- ✅ Skill 1 (Three.js instalado)
- ✅ Skill 8 (Estilos base)

---

## 📚 SKILL 10: Blog y SEO

**Estado:** ⏳ Pendiente  
**Prioridad:** 🟢 Media  
**Descripción:** Sistema de blog con artículos y optimización SEO.

### Checklist

- [ ] Crear tabla `blog`
- [ ] Campos: id, slug, titulo, descripcion, contenido, fecha, autor
- [ ] Crear `public/blog.php`
- [ ] Listado de artículos con paginación
- [ ] Crear `public/articulo.php`
- [ ] Vista detalle con slug
- [ ] Meta tags dinámicos (title, description, og:*)
- [ ] Configurar `.htaccess` para URLs amigables (opcional)
- [ ] Styling responsive

**Completitud:** 0/9 (0%)

### Dependencias
- ✅ Skill 1 (BD)

---

## 🎯 SKILL 11: Gestión de banners y secciones editables

**Estado:** ⏳ Pendiente  
**Prioridad:** 🟢 Media  
**Descripción:** Módulo admin para editar contenido dinámico del sitio.

### Checklist

- [ ] Crear tabla `banners`
- [ ] Crear tabla `secciones`
- [ ] Módulo admin para ABM de banners
- [ ] Módulo admin para editar secciones (misión, visión, textos)
- [ ] Mostrar banners en homepage dinámicamente
- [ ] Mostrar secciones editables en sitio público
- [ ] Proteger acceso: solo Admin

**Completitud:** 0/7 (0%)

### Dependencias
- ✅ Skill 2 (Autenticación)
- ✅ Skill 8 (Home existente)

---

## 📧 SKILL 12: Formulario de contacto y logs

**Estado:** ⏳ Pendiente  
**Prioridad:** 🟢 Baja  
**Descripción:** Formulario de contacto, emails y logging de acciones.

### Checklist

- [ ] Crear tabla `contactos`
- [ ] Crear tabla `logs`
- [ ] Crear `public/contacto.php`
- [ ] Formulario: nombre, email, mensaje
- [ ] Guardar en BD
- [ ] Enviar email con `mail()` o PHPMailer
- [ ] Registrar acciones importantes en logs (quién, qué, IP)
- [ ] Crear función `registrarLog()` en helper
- [ ] Panel admin para ver logs (opcional)

**Completitud:** 0/9 (0%)

### Dependencias
- ✅ Skill 1 (BD)

---

## 🔒 SKILL 13: Seguridad y pruebas finales

**Estado:** ⏳ Pendiente  
**Prioridad:** 🔴 Crítica  
**Descripción:** Auditoría de seguridad, validaciones y pruebas end-to-end.

### Checklist

- [ ] Revisar: todas las consultas usan prepared statements
- [ ] Revisar: `htmlspecialchars()` en cada variable mostrada
- [ ] Revisar: escapar datos de entrada
- [ ] Agregar CSRF tokens en formularios del panel
- [ ] Validar: subida de archivos (extensiones permitidas)
- [ ] Validar: tamaño máximo de archivos
- [ ] Probar: flujo completo crear → buscar → QR → PDF
- [ ] Probar: autenticación (login, roles, logout)
- [ ] Probar: búsqueda de certificados
- [ ] Probar: descarga de PDF
- [ ] Probar: formulario de contacto
- [ ] Pruebas en navegadores principales
- [ ] Optimización de performance
- [ ] Documentación final

**Completitud:** 0/14 (0%)

### Dependencias
- ✅ Todos los skills anteriores

### Checklist de seguridad
```php
// ✅ Prepared Statements
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

// ✅ htmlspecialchars
echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

// ✅ CSRF Token
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// ✅ Validar extensiones
$extensiones_permitidas = ['jpg', 'png', 'gif'];
if(!in_array(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION), $extensiones_permitidas)) {
  die('Extensión no permitida');
}
```

---

## 📝 Notas generales

### Estructura de carpetas completada
```
technical_del_peru/
├── admin/
│   ├── modules/
│   │   ├── certificados/
│   │   │   ├── index.php
│   │   │   ├── crear.php
│   │   │   └── editar.php
│   │   └── configuracion/
│   │       └── firma.php
│   ├── login.php
│   └── logout.php
├── src/
│   ├── config/
│   │   ├── db.php
│   │   └── session.php
│   ├── controllers/
│   ├── helpers/
│   │   ├── auth_helper.php
│   │   ├── qr_helper.php
│   │   └── pdf_helper.php
│   ├── libs/
│   │   └── phpqrcode/
│   ├── models/
│   │   └── CertificadoModel.php
│   └── views/
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   ├── uploads/
│   │   │   ├── certificados/
│   │   │   ├── firmas/
│   │   │   ├── modelos_3d/
│   │   │   └── qrcodes/
│   │   └── video/
│   ├── index.php
│   ├── productos.php
│   ├── producto.php
│   ├── blog.php
│   ├── articulo.php
│   ├── buscar-certificado.php
│   ├── verificar-certificado.php
│   ├── descargar-certificado.php
│   └── contacto.php
├── sql/
│   └── estructura.sql
├── package.json
├── tailwind.config.js
├── postcss.config.js
└── ROADMAP.md (este archivo)
```

### Ambiente de desarrollo
```bash
# Terminal 1: Watch Tailwind
pnpm watch:css

# Terminal 2: Servir PHP (XAMPP)
# Acceder a http://localhost/technical_del_peru
```

### Recursos útiles
- [Tailwind CSS v4](https://tailwindcss.com/docs)
- [Three.js Docs](https://threejs.org/docs)
- [AOS Library](https://michalsnik.github.io/aos/)
- [GSAP Docs](https://gsap.com/docs)
- [Dompdf](https://github.com/dompdf/dompdf)
- [phpqrcode](https://tcpdf.org/wiki/index.php?title=Qrcode:QRcode&redirect=no)

---

## 🎯 Meta

**Objetivo final:** Plataforma completa para gestión y verificación de certificados con catálogo de productos y blog.

**Tiempo estimado:** 4-6 semanas (según dedicación)

**Próxima reunión de revisión:** [Configurar fecha]

---

*Última actualización: 26 de mayo de 2026*
