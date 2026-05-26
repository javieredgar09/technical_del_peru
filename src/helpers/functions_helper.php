<?php
/**
 * Technical del Perú — Helpers de Funciones Generales
 * 
 * Utilidades comunes: redirección, validación de archivos,
 * sanitización, formateo de fechas, generación de slugs.
 * 
 * @version 1.0.0
 */

// ================================================================
// REDIRECCIÓN
// ================================================================

/**
 * Redirige a una URL y termina la ejecución.
 * 
 * @param string $url URL de destino
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// ================================================================
// VALIDACIÓN DE ARCHIVOS
// ================================================================

/**
 * Valida un archivo subido.
 * 
 * @param array $file Elemento de $_FILES
 * @param array $allowedExtensions Extensiones permitidas
 * @param int $maxSize Tamaño máximo en bytes
 * @return array ['valid' => bool, 'error' => string|null, 'extension' => string]
 */
function validateFileUpload(array $file, array $allowedExtensions = [], int $maxSize = 0): array
{
    // Verificar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor.',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo del formulario.',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
        ];
        return [
            'valid'     => false,
            'error'     => $errors[$file['error']] ?? 'Error desconocido al subir el archivo.',
            'extension' => '',
        ];
    }

    // Obtener extensión
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Validar extensión
    if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions, true)) {
        return [
            'valid'     => false,
            'error'     => 'Extensión no permitida. Permitidas: ' . implode(', ', $allowedExtensions),
            'extension' => $extension,
        ];
    }

    // Validar tamaño
    $maxSize = $maxSize > 0 ? $maxSize : MAX_FILE_SIZE;
    if ($file['size'] > $maxSize) {
        $maxMB = round($maxSize / (1024 * 1024), 1);
        return [
            'valid'     => false,
            'error'     => "El archivo excede el tamaño máximo de {$maxMB} MB.",
            'extension' => $extension,
        ];
    }

    // Verificar que es realmente un archivo subido
    if (!is_uploaded_file($file['tmp_name'])) {
        return [
            'valid'     => false,
            'error'     => 'Archivo no válido.',
            'extension' => $extension,
        ];
    }

    return [
        'valid'     => true,
        'error'     => null,
        'extension' => $extension,
    ];
}

/**
 * Mueve un archivo subido a la carpeta de destino con nombre seguro.
 * 
 * @param array $file Elemento de $_FILES
 * @param string $destDir Directorio de destino (absoluto)
 * @param string|null $customName Nombre personalizado (sin extensión)
 * @return array ['success' => bool, 'path' => string, 'filename' => string, 'error' => string|null]
 */
function moveUploadedFile(array $file, string $destDir, ?string $customName = null): array
{
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Generar nombre seguro
    if ($customName) {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $customName) . '.' . $extension;
    } else {
        $filename = uniqid('file_', true) . '.' . $extension;
    }

    // Crear directorio si no existe
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $destPath = rtrim($destDir, '/\\') . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        return [
            'success'  => true,
            'path'     => $destPath,
            'filename' => $filename,
            'error'    => null,
        ];
    }

    return [
        'success'  => false,
        'path'     => '',
        'filename' => '',
        'error'    => 'No se pudo mover el archivo al destino.',
    ];
}

// ================================================================
// SANITIZACIÓN
// ================================================================

/**
 * Sanitiza un string de entrada.
 * 
 * @param string $data Dato a sanitizar
 * @return string Dato limpio
 */
function sanitizeInput(string $data): string
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida que un RUC peruano tenga 11 dígitos numéricos.
 * 
 * @param string $ruc
 * @return bool
 */
function validateRUC(string $ruc): bool
{
    return preg_match('/^\d{11}$/', $ruc) === 1;
}

/**
 * Valida formato de email.
 * 
 * @param string $email
 * @return bool
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ================================================================
// FORMATEO
// ================================================================

/**
 * Formatea una fecha para visualización en formato peruano.
 * 
 * @param string|null $date Fecha en formato Y-m-d o datetime
 * @param string $format Formato de salida
 * @return string Fecha formateada
 */
function formatDate(?string $date, string $format = 'd/m/Y'): string
{
    if (empty($date)) {
        return '—';
    }

    try {
        $dt = new DateTime($date);
        return $dt->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Formatea una fecha con hora.
 * 
 * @param string|null $date
 * @return string
 */
function formatDateTime(?string $date): string
{
    return formatDate($date, 'd/m/Y H:i');
}

// ================================================================
// SLUGS Y URLS
// ================================================================

/**
 * Genera un slug URL-friendly a partir de un texto.
 * 
 * @param string $text Texto original
 * @return string Slug generado
 */
function generateSlug(string $text): string
{
    // Transliterar caracteres especiales del español
    $replacements = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'ñ' => 'n', 'Ñ' => 'n', 'ü' => 'u', 'Ü' => 'u',
    ];

    $text = strtr($text, $replacements);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');

    return $text;
}

// ================================================================
// PAGINACIÓN
// ================================================================

/**
 * Calcula los valores de paginación.
 * 
 * @param int $totalItems Total de registros
 * @param int $perPage Registros por página
 * @param int $currentPage Página actual
 * @return array ['offset', 'totalPages', 'currentPage', 'perPage', 'hasNext', 'hasPrev']
 */
function paginate(int $totalItems, int $perPage = 15, int $currentPage = 1): array
{
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'offset'      => $offset,
        'totalPages'  => $totalPages,
        'currentPage' => $currentPage,
        'perPage'     => $perPage,
        'totalItems'  => $totalItems,
        'hasNext'     => $currentPage < $totalPages,
        'hasPrev'     => $currentPage > 1,
    ];
}

// ================================================================
// LOGO DINÁMICO CON EFECTOS
// ================================================================

/**
 * Renderiza el logo vectorial premium de Technical del Perú con soporte para efectos 3D y ondas.
 * 
 * @param string $heightClass Clase de Tailwind para altura (ej: 'h-12')
 * @param bool $includeText Determina si se incluye el texto de la marca
 * @return string Código HTML/SVG del logo
 */
function renderBrandLogo(string $heightClass = 'h-12', bool $includeText = true): string
{
    $textHtml = $includeText ? '
    <span class="font-[\'Outfit\'] font-bold text-white text-base sm:text-lg md:text-xl tracking-wider select-none transition-colors group-hover:text-sky-400">
        TECHNICAL DEL PERÚ
    </span>' : '';

    return '
    <div class="brand-logo-container group flex items-center gap-3">
        <div class="brand-logo-waves p-1.5 rounded-xl bg-white/0 transition-all duration-300 group-hover:bg-white/5">
            <svg class="' . $heightClass . ' w-auto brand-logo-3d object-contain" viewBox="0 0 80 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- White Triangle (Structure / A) -->
                <g class="logo-white-group" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Outer Triangle Outline -->
                    <polygon points="34,8 10,56 58,56" />
                    <!-- Horizontal Crossbar A -->
                    <line x1="22" y1="36" x2="46" y2="36" />
                    <!-- Horizontal Crossbar B -->
                    <line x1="15" y1="48" x2="53" y2="48" stroke-width="3" />
                </g>
                
                <!-- Blue Triangle (Overlap / Wave Energy) -->
                <g class="logo-blue-group" stroke="#3B9FE7" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Tilted Overlapping Triangle -->
                    <polygon points="56,22 40,56 72,56" class="logo-blue-triangle-wave" />
                </g>
            </svg>
        </div>
        ' . $textHtml . '
    </div>';
}

