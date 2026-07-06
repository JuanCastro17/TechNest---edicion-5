<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Traemos tu conexión original saliendo correctamente a config/
require_once __DIR__ . '/../../config/conexion.php';

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// NUEVA FUNCIONALIDAD: Si viene el parámetro 'q' (ej: api/consultar.php?q=lavadora)
if (isset($_GET['q'])) {
    $busqueda = mysqli_real_escape_string($conexion, trim($_GET['q']));
    
    if (strlen($busqueda) >= 1) {
        // Busca coincidencias parciales por nombre o por tu nueva columna categoria
        $query = "SELECT *, (stock > 0) AS disponible FROM productos 
                  WHERE nombre LIKE '%$busqueda%' OR categoria LIKE '%$busqueda%' 
                  ORDER BY nombre ASC LIMIT 6";
        
        $resultado = mysqli_query($conexion, $query);
        $sugerencias = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $sugerencias[] = $row;
            }
        }
        echo json_encode($sugerencias, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode([]);
    }
    exit();
}

// TU LÓGICA ORIGINAL: Verificar si se solicita un ID específico en la URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT *, (stock > 0) AS disponible FROM productos WHERE id = $id";
    $resultado = mysqli_query($conexion, $query);
    
    if ($resultado) {
        $producto = mysqli_fetch_assoc($resultado);
        echo json_encode($producto ? $producto : [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al consultar producto: " . mysqli_error($conexion)]);
    }
} else {
    // TU LÓGICA ORIGINAL: Si no hay parámetros, listar todo el inventario
    $query = "SELECT *, (stock > 0) AS disponible FROM productos";
    $resultado = mysqli_query($conexion, $query);
    
    if ($resultado) {
        $productos = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $productos[] = $fila;
        }
        echo json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al listar productos: " . mysqli_error($conexion)]);
    }
}
?>
