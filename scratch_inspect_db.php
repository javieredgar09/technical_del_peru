<?php
require_once 'src/config/db.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM productos LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current products in 'productos' table:\n";
    foreach ($rows as $row) {
        echo "ID: {$row['id']}\n";
        echo "Name: {$row['nombre']}\n";
        echo "Slug: {$row['slug']}\n";
        echo "Short Desc: {$row['descripcion_corta']}\n";
        echo "Industria: {$row['industria']}\n";
        echo "Image: {$row['imagen_principal']}\n";
        echo "Model 3D: {$row['modelo_3d']}\n";
        echo "Precio: {$row['precio_referencial']}\n";
        echo "Destacado: {$row['destacado']}\n";
        echo "Activo: {$row['activo']}\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
