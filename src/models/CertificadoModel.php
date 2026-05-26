<?php
/**
 * Technical del Perú — Modelo de Certificados
 * 
 * Gestiona el almacenamiento, recuperación, edición y eliminación de
 * certificados en la base de datos, así como la generación automatizada
 * de sus códigos QR físicos.
 * 
 * @version 1.0.0
 */

// Cargar helper de QR
require_once __DIR__ . '/../helpers/qr_helper.php';

class CertificadoModel
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
     * Crea un nuevo certificado con código único y genera su QR.
     * 
     * @param array $datos Datos del certificado
     * @return int|bool ID del certificado creado o false en caso de error
     */
    public function crear(array $datos): int|bool
    {
        try {
            // Iniciar transacción para asegurar consistencia
            $this->db->beginTransaction();

            // 1. Generar hash de verificación único (32 caracteres hexadecimales)
            $codigo = bin2hex(random_bytes(16));

            // 2. Insertar registro inicial en la base de datos
            $stmt = $this->db->prepare('
                INSERT INTO `certificados` (
                    `ruc`, `razon_social`, `nombre_participante`, `tipo_certificado`, 
                    `descripcion`, `fecha_emision`, `fecha_vencimiento`, `codigo_verificacion`, 
                    `estado`, `creado_por`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $datos['ruc'],
                $datos['razon_social'],
                $datos['nombre_participante'],
                $datos['tipo_certificado'],
                $datos['descripcion'] ?? null,
                $datos['fecha_emision'],
                $datos['fecha_vencimiento'] ?? null,
                $codigo,
                $datos['estado'] ?? 'vigente',
                $datos['creado_por'] ?? null
            ]);

            $id = (int)$this->db->lastInsertId();

            if (!$id) {
                throw new Exception('No se pudo obtener el ID del certificado insertado.');
            }

            // 3. Generar el código QR con el helper
            $verificationURL = getVerificationURL($codigo);
            $qrFilename = 'qr_' . $codigo;
            
            $qrResult = generateQRCode($verificationURL, $qrFilename);

            if (!$qrResult['success']) {
                throw new Exception('Fallo en la generación física del código QR: ' . $qrResult['error']);
            }

            // 4. Actualizar el registro con la ruta del QR generado
            $stmtUpdate = $this->db->prepare('
                UPDATE `certificados` 
                SET `ruta_qr` = ? 
                WHERE `id` = ?
            ');
            $stmtUpdate->execute([$qrResult['relative_path'], $id]);

            // Confirmar transacción
            $this->db->commit();

            // Registrar acción en logs
            if (function_exists('registrarLog')) {
                registrarLog('Creación de certificado', "Certificado emitido para RUC {$datos['ruc']} con código {$codigo}", 'certificados', $id);
            }

            return $id;
        } catch (Exception $e) {
            // Revertir base de datos si algo falla
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en CertificadoModel::crear - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los detalles de un certificado por su ID.
     * 
     * @param int $id ID del certificado
     * @return array|bool Datos del certificado o false si no existe
     */
    public function obtenerPorId(int $id): array|bool
    {
        try {
            $stmt = $this->db->prepare('
                SELECT c.*, u.nombre AS creador_nombre 
                FROM `certificados` c 
                LEFT JOIN `usuarios` u ON c.creado_por = u.id 
                WHERE c.id = ? 
                LIMIT 1
            ');
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en CertificadoModel::obtenerPorId - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un certificado por su código único de verificación.
     * 
     * @param string $codigo Hash de verificación de 32 caracteres
     * @return array|bool Datos del certificado o false si no existe
     */
    public function obtenerPorCodigo(string $codigo): array|bool
    {
        try {
            $stmt = $this->db->prepare('
                SELECT c.*, u.nombre AS creador_nombre 
                FROM `certificados` c 
                LEFT JOIN `usuarios` u ON c.creado_por = u.id 
                WHERE c.codigo_verificacion = ? 
                LIMIT 1
            ');
            $stmt->execute([$codigo]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error en CertificadoModel::obtenerPorCodigo - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los certificados vinculados a un número de RUC.
     * 
     * @param string $ruc Número de RUC de 11 dígitos
     * @return array Listado de certificados
     */
    public function obtenerPorRUC(string $ruc): array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT * FROM `certificados` 
                WHERE `ruc` = ? 
                ORDER BY `fecha_emision` DESC
            ');
            $stmt->execute([$ruc]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en CertificadoModel::obtenerPorRUC - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista certificados con soporte para filtros de búsqueda y paginación.
     * 
     * @param int|null $limit Límite de registros
     * @param int|null $offset Desplazamiento
     * @param string $search Filtro de búsqueda (RUC, Razón social, Participante o Código)
     * @return array Listado de certificados
     */
    public function listarTodos(?int $limit = null, ?int $offset = null, string $search = ''): array
    {
        try {
            $sql = '
                SELECT c.*, u.nombre AS creador_nombre 
                FROM `certificados` c 
                LEFT JOIN `usuarios` u ON c.creado_por = u.id
            ';
            $params = [];

            if (!empty($search)) {
                $sql .= '
                    WHERE c.ruc LIKE ? 
                    OR c.razon_social LIKE ? 
                    OR c.nombre_participante LIKE ? 
                    OR c.codigo_verificacion LIKE ?
                ';
                $searchParam = '%' . $search . '%';
                $params = [$searchParam, $searchParam, $searchParam, $searchParam];
            }

            $sql .= ' ORDER BY c.created_at DESC';

            if ($limit !== null && $offset !== null) {
                $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error en CertificadoModel::listarTodos - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la cantidad total de certificados según el filtro de búsqueda.
     * 
     * @param string $search Filtro de búsqueda
     * @return int Cantidad total
     */
    public function obtenerTotal(string $search = ''): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM `certificados`';
            $params = [];

            if (!empty($search)) {
                $sql .= '
                    WHERE `ruc` LIKE ? 
                    OR `razon_social` LIKE ? 
                    OR `nombre_participante` LIKE ? 
                    OR `codigo_verificacion` LIKE ?
                ';
                $searchParam = '%' . $search . '%';
                $params = [$searchParam, $searchParam, $searchParam, $searchParam];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en CertificadoModel::obtenerTotal - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Actualiza los datos de un certificado existente.
     * 
     * @param int $id ID del certificado a editar
     * @param array $datos Nuevos datos
     * @return bool true en caso de éxito
     */
    public function editar(int $id, array $datos): bool
    {
        try {
            $stmt = $this->db->prepare('
                UPDATE `certificados` 
                SET `ruc` = ?, 
                    `razon_social` = ?, 
                    `nombre_participante` = ?, 
                    `tipo_certificado` = ?, 
                    `descripcion` = ?, 
                    `fecha_emision` = ?, 
                    `fecha_vencimiento` = ?, 
                    `estado` = ? 
                WHERE `id` = ?
            ');

            $success = $stmt->execute([
                $datos['ruc'],
                $datos['razon_social'],
                $datos['nombre_participante'],
                $datos['tipo_certificado'],
                $datos['descripcion'] ?? null,
                $datos['fecha_emision'],
                $datos['fecha_vencimiento'] ?? null,
                $datos['estado'],
                $id
            ]);

            if ($success && function_exists('registrarLog')) {
                registrarLog('Edición de certificado', "Se editó el certificado ID {$id} vinculado al RUC {$datos['ruc']}", 'certificados', $id);
            }

            return $success;
        } catch (PDOException $e) {
            error_log('Error en CertificadoModel::editar - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un certificado y borra físicamente su archivo QR en disco.
     * 
     * @param int $id ID del certificado a eliminar
     * @return bool true en caso de éxito
     */
    public function eliminar(int $id): bool
    {
        try {
            // 1. Obtener la ruta del código QR antes de eliminar el registro
            $cert = $this->obtenerPorId($id);
            
            if (!$cert) {
                return false;
            }

            $this->db->beginTransaction();

            // 2. Eliminar de la base de datos
            $stmt = $this->db->prepare('DELETE FROM `certificados` WHERE `id` = ?');
            $success = $stmt->execute([$id]);

            if (!$success) {
                throw new Exception('No se pudo borrar el certificado de la base de datos.');
            }

            // 3. Borrar archivo físico de código QR en disco para mantener higiene del servidor
            if (!empty($cert['ruta_qr'])) {
                $physicalPath = ROOT_PATH . '/public/' . $cert['ruta_qr'];
                if (file_exists($physicalPath)) {
                    unlink($physicalPath);
                }
            }

            $this->db->commit();

            // Registrar acción en logs
            if (function_exists('registrarLog')) {
                registrarLog('Eliminación de certificado', "Se eliminó el certificado ID {$id} y su código QR físico", 'certificados', $id);
            }

            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en CertificadoModel::eliminar - ' . $e->getMessage());
            return false;
        }
    }
}
