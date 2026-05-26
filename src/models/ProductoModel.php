<?php
/**
 * Technical del Perú — Modelo de Productos
 * 
 * Gestiona la recuperación, filtrado y detalle de los productos
 * del catálogo industrial de la empresa.
 * 
 * @version 1.0.0
 */

class ProductoModel
{
    private PDO $db;

    /**
     * Constructor del modelo.
     * 
     * @param PDO|null $db Conexión PDO. Si es null, obtiene la del singleton.
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getDB();
    }

    /**
     * Obtiene todos los productos activos, con soporte para filtros de texto e industria.
     * 
     * @param string|null $search Término de búsqueda
     * @param string|null $industria Nombre de la industria
     * @return array Listado de productos encontrados
     */
    public function listarActivos(?string $search = null, ?string $industria = null): array
    {
        try {
            $sql = "SELECT * FROM `productos` WHERE `activo` = 1";
            $params = [];

            if ($search !== null && trim($search) !== '') {
                $sql .= " AND (`nombre` LIKE ? OR `descripcion_corta` LIKE ? OR `descripcion` LIKE ?)";
                $searchParam = "%" . trim($search) . "%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if ($industria !== null && trim($industria) !== '') {
                $sql .= " AND `industria` = ?";
                $params[] = trim($industria);
            }

            $sql .= " ORDER BY `orden` ASC, `nombre` ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ProductoModel::listarActivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un producto activo basándose en su slug único.
     * 
     * @param string $slug El slug identificador del producto
     * @return array|null Fila del producto o null si no se encuentra
     */
    public function obtenerPorSlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM `productos` 
                WHERE `slug` = ? AND `activo` = 1 
                LIMIT 1
            ");
            $stmt->execute([$slug]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result : null;
        } catch (PDOException $e) {
            error_log("Error en ProductoModel::obtenerPorSlug: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene las industrias distintas de los productos activos.
     * 
     * @return array Lista de nombres de industrias
     */
    public function obtenerIndustrias(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT DISTINCT `industria` 
                FROM `productos` 
                WHERE `activo` = 1 AND `industria` IS NOT NULL AND `industria` != '' 
                ORDER BY `industria` ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error en ProductoModel::obtenerIndustrias: " . $e->getMessage());
            return [];
        }
    }
}
