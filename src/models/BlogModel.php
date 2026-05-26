<?php
/**
 * Technical del Perú — Modelo de Blog
 * 
 * Gestiona consultas seguras y preparadas para la tabla `blog`,
 * incluyendo listados paginados, búsquedas, filtrado por tags
 * y recuperación por slug único.
 * 
 * @version 1.0.0
 */

require_once dirname(__DIR__) . '/config/db.php';

class BlogModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Lista los artículos publicados y vigentes con soporte para búsqueda y tags (paginado).
     * 
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento
     * @param string|null $search Término de búsqueda
     * @param string|null $tag Filtro por tag específico
     * @return array Artículos coincidentes
     */
    public function listarPublicados(int $limit = 6, int $offset = 0, ?string $search = null, ?string $tag = null): array
    {
        $sql = "SELECT * FROM blog WHERE publicado = 1 AND (fecha_publicacion IS NULL OR fecha_publicacion <= NOW())";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (titulo LIKE :search OR contenido LIKE :search_cont)";
            $params[':search'] = '%' . $search . '%';
            $params[':search_cont'] = '%' . $search . '%';
        }

        if (!empty($tag)) {
            $sql .= " AND tags LIKE :tag";
            $params[':tag'] = '%' . $tag . '%';
        }

        $sql .= " ORDER BY fecha_publicacion DESC, id DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);

            // Binds estándar para parámetros dinámicos
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }

            // Binds estrictos como enteros para evitar fallos de sintaxis en prepared statements reales
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en BlogModel::listarPublicados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el total de artículos publicados bajo los filtros de búsqueda y tags.
     * 
     * @param string|null $search Término de búsqueda
     * @param string|null $tag Filtro por tag específico
     * @return int Cuenta de registros
     */
    public function contarPublicados(?string $search = null, ?string $tag = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM blog WHERE publicado = 1 AND (fecha_publicacion IS NULL OR fecha_publicacion <= NOW())";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (titulo LIKE :search OR contenido LIKE :search_cont)";
            $params[':search'] = '%' . $search . '%';
            $params[':search_cont'] = '%' . $search . '%';
        }

        if (!empty($tag)) {
            $sql .= " AND tags LIKE :tag";
            $params[':tag'] = '%' . $tag . '%';
        }

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en BlogModel::contarPublicados: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene un artículo publicado por su slug único.
     * 
     * @param string $slug Slug único del artículo
     * @return array|null Fila del artículo o null si no se encuentra o no está publicado
     */
    public function obtenerPorSlug(string $slug): ?array
    {
        $sql = "SELECT * FROM blog 
                WHERE slug = :slug 
                  AND publicado = 1 
                  AND (fecha_publicacion IS NULL OR fecha_publicacion <= NOW()) 
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':slug' => $slug]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row : null;
        } catch (PDOException $e) {
            error_log("Error en BlogModel::obtenerPorSlug: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los artículos más recientes publicados (excluyendo uno opcionalmente).
     * 
     * @param int $limit Cantidad a recuperar
     * @param int|null $excludeId ID de artículo a excluir
     * @return array Artículos recientes
     */
    public function obtenerRecientes(int $limit = 3, ?int $excludeId = null): array
    {
        $sql = "SELECT * FROM blog 
                WHERE publicado = 1 
                  AND (fecha_publicacion IS NULL OR fecha_publicacion <= NOW())";
        
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }

        $sql .= " ORDER BY fecha_publicacion DESC, id DESC LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            if ($excludeId !== null) {
                $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en BlogModel::obtenerRecientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los tags más populares (analiza la columna tags separada por comas).
     * 
     * @param int $limit Número máximo de tags a retornar
     * @return array Lista de tags únicos ordenados por frecuencia
     */
    public function obtenerTagsPopulares(int $limit = 8): array
    {
        $sql = "SELECT tags FROM blog 
                WHERE publicado = 1 
                  AND (fecha_publicacion IS NULL OR fecha_publicacion <= NOW()) 
                  AND tags IS NOT NULL 
                  AND tags != ''";

        try {
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $allTags = [];
            foreach ($rows as $row) {
                // Separar por comas, limpiar espacios en blanco y consolidar
                $parts = explode(',', $row);
                foreach ($parts as $p) {
                    $cleaned = trim($p);
                    if (!empty($cleaned)) {
                        $allTags[] = $cleaned;
                    }
                }
            }

            if (empty($allTags)) {
                return [];
            }

            // Contar frecuencias
            $frequencies = array_count_values($allTags);
            // Ordenar de mayor a menor frecuencia
            arsort($frequencies);

            // Obtener las llaves (nombres de los tags) y limitar resultados
            return array_slice(array_keys($frequencies), 0, $limit);
        } catch (PDOException $e) {
            error_log("Error en BlogModel::obtenerTagsPopulares: " . $e->getMessage());
            return [];
        }
    }
}
