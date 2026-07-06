<?php
session_start();

// REQUERIMIENTO COMPLETO: Filtro de seguridad estricto para que solo entren administradores logueados
if (!isset($_SESSION['admin_user'])) {
    header("Location: login_admin.php");
    exit();
}

// CORRECCIÓN CAPAS: Como estás en public/, salimos un nivel con /../ para ir a la carpeta config/
include(__DIR__ . '/../config/conexion.php');

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); 

    // Eliminar el producto seleccionado de la base de datos real
    $query = "DELETE FROM productos WHERE id = $id";

    if (mysqli_query($conexion, $query)) {
        
        // COMPROBACIÓN AUTOMÁTICA EXTRAORDINARIA: Ver si la tabla quedó vacía
        $check_query = "SELECT COUNT(*) as total FROM productos";
        $check_result = mysqli_query($conexion, $check_query);
        $row = mysqli_fetch_assoc($check_result);

        if ($row['total'] == 0) {
            // Si ya no hay productos, reseteamos el contador a 1 de forma automatizada
            mysqli_query($conexion, "ALTER TABLE productos AUTO_INCREMENT = 1");
        }

        header("Location: admin.php?mensaje=eliminado");
        exit();
    } else {
        echo "Error al intentar eliminar el producto: " . mysqli_error($conexion);
    }

} else {
    header("Location: admin.php");
    exit();
}
?>
