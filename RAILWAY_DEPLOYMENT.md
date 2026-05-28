# 🚀 Guía de Despliegue en Railway.app

**Technical del Perú en producción en 10 minutos**

---

## 📋 Requisitos Previos

- ✅ Cuenta en GitHub (ya tienes: @javieredgar09)
- ✅ Proyecto en GitHub (ya está: technical_del_peru)
- ✅ Cuenta de Railway (la crearemos)

---

## 🔧 PASO 1: Crear Cuenta en Railway

1. **Ve a:** https://railway.app
2. **Haz clic en:** `Sign in` (arriba a la derecha)
3. **Elige:** `Continue with GitHub`
4. **Autoriza** el acceso a tu cuenta de GitHub
5. **¡Listo!** Ya estás en Railway

---

## 📦 PASO 2: Crear un Nuevo Proyecto

1. **En el Dashboard**, haz clic en: `+ New Project`
2. **Elige:** `Deploy from GitHub repo`
3. **Busca:** `technical_del_peru`
4. **Haz clic:** `Import`

---

## ⚙️ PASO 3: Configurar Variables de Entorno

Cuando Railway detecte que es un proyecto PHP, necesitarás configurar:

### 3.1 Base de Datos MySQL

1. **En el proyecto**, haz clic: `+ Add Service`
2. **Elige:** `MySQL`
3. **Selecciona versión:** `8.0` (o la más reciente)
4. **Haz clic:** `Deploy`

Railway creará automáticamente una BD MySQL en la nube.

### 3.2 Variables de Entorno para PHP

1. **Vuelve al editor visual de Railway**
2. **Haz clic en tu servicio PHP**
3. **Ve a:** `Variables`
4. **Agrega estas variables:**

```
DB_HOST=db.railway.internal
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASS=[MySQL_ROOT_PASSWORD]
```

> ℹ️ Railway te muestra el password de MySQL automáticamente cuando lo despliegas

---

## 📝 PASO 4: Actualizar Archivo de Configuración

Edita tu archivo `src/config/db.php` para leer las variables de entorno:

### Antes (desarrollo):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'technical_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Después (producción con Railway):
```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'technical_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
```

---

## 🗄️ PASO 5: Importar Base de Datos

1. **En Railway**, ve al servicio MySQL
2. **Haz clic:** `Open MySQL`
3. **Copia todo el contenido de:** `sql/estructura.sql`
4. **Pégalo en la consola MySQL de Railway**
5. **Ejecuta** (Ctrl+Enter)

---

## ✅ PASO 6: Desplegar

1. **En Railway**, haz clic: `Deploy`
2. **Espera 2-3 minutos** mientras se compila y despliega
3. **Una vez completado, verás la URL** como:
   ```
   https://technical-del-peru-production.up.railway.app
   ```

---

## 🔐 PASO 7: Acceder al Panel Admin

**URL del admin:**
```
https://[TU-URL-RAILWAY]/admin/login.php
```

**Credenciales:**
```
Email: admin@technicaldelperu.pe
Contraseña: Admin2026!
```

---

## 🧪 Verificar que Funciona

### Prueba 1: Home público
```
https://[TU-URL]/public/index.php
```

### Prueba 2: Buscar certificado
```
https://[TU-URL]/public/buscar-certificado.php
```

### Prueba 3: Panel admin
```
https://[TU-URL]/admin/login.php
```

---

## 🐛 Troubleshooting

### Error 500 - Internal Server Error
**Solución:** Verifica que la BD se creó correctamente
1. Ve a MySQL en Railway
2. Ejecuta: `SHOW DATABASES;`
3. Si no ves `technical_db`, importa `sql/estructura.sql` nuevamente

### Error 404 - Not Found
**Solución:** Asegúrate de que la ruta esté correcta
```
Correcto:   https://[URL]/public/index.php
Incorrecto: https://[URL]/index.php
```

### Connection refused a BD
**Solución:** Verifica las variables de entorno
1. En Railway, ve a tu servicio PHP
2. Haz clic: `Variables`
3. Asegúrate que `DB_HOST`, `DB_USER`, `DB_PASS` estén correctos

---

## 💰 Costos

- **Primeros 3 meses:** GRATIS ($10 de crédito inicial)
- **Después:** $5/mes de base, más uso de BD y ancho de banda

---

## 🎯 Próximos Pasos

Una vez en vivo:
1. ✅ Prueba todos los módulos
2. ✅ Crea certificados de prueba
3. ✅ Descarga PDFs
4. ✅ Verifica la búsqueda por RUC
5. ✅ Comparte la URL con tu equipo

---

## 📞 Soporte

Si tienes problemas:
1. Abre Railway Dashboard
2. Haz clic en tu proyecto
3. Ve a `Logs` para ver errores
4. Comparte los logs conmigo

---

**¿Listo? ¡Vamos a desplegar!** 🚀
