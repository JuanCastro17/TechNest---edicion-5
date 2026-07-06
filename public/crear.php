<?php
session_start();

// Filtro de seguridad estricto para administradores
if (!isset($_SESSION['admin_user'])) { 
    header("Location: login_admin.php"); 
    exit(); 
}

include(__DIR__ . '/../config/conexion.php');
if (!isset($conexion) || !$conexion) { 
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $precio_limpio = preg_replace('/[^\d.]/', '', $_POST['precio']);
    $precio = floatval($precio_limpio);
    $stock  = intval($_POST['stock']);
    $categoria = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
    
    // FUNCIÓN CORPORATIVA: Limpia el nombre del producto para usarlo en el archivo físico
    // Convierte a minúsculas, cambia espacios por guiones bajos y remueve caracteres especiales
    $nombre_limpio = strtolower(trim($_POST['nombre']));
    $nombre_limpio = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' ', 'ñ'], 
        ['a', 'e', 'i', 'o', 'u', 'n', '_', 'n'], 
        $nombre_limpio
    );
    $nombre_limpio = preg_replace('/[^a-z0-8_]/', '', $nombre_limpio); // Deja solo letras, números y guiones bajos

    // Directorio de destino personalizado solicitado
    $directorio_destino = __DIR__ . "/../src/productos/";
    
    // Si por alguna razón la carpeta no existe al momento de subir, PHP la crea automáticamente
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }

    $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    
    // Nombres por defecto en la base de datos
    $img1_name = "default.jpg"; 
    $img2_name = "default.jpg"; 
    $img3_name = "default.jpg";

    // Procesar e inyectar Imagen 1 (Sufijo _1)
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $permitidos)) {
            // REQUERIMIENTO COMPLETO: Nombre idéntico al producto + índice secuencial
            $img1_name = $nombre_limpio . "_1." . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio_destino . $img1_name);
        }
    }
    
    // Procesar e inyectar Imagen 2 (Sufijo _2)
    if (isset($_FILES['imagen2']) && $_FILES['imagen2']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen2']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $permitidos)) {
            $img2_name = $nombre_limpio . "_2." . $ext;
            move_uploaded_file($_FILES['imagen2']['tmp_name'], $directorio_destino . $img2_name);
        }
    }

    // Procesar e inyectar Imagen 3 (Sufijo _3)
    if (isset($_FILES['imagen3']) && $_FILES['imagen3']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen3']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $permitidos)) {
            $img3_name = $nombre_limpio . "_3." . $ext;
            move_uploaded_file($_FILES['imagen3']['tmp_name'], $directorio_destino . $img3_name);
        }
    }

    // Guardamos las rutas de los archivos binarios de forma limpia en la base de datos expandida
    $query = "INSERT INTO productos (nombre, precio, stock, categoria, imagen, imagen2, imagen3, descripcion) 
              VALUES ('$nombre', '$precio', '$stock', '$categoria', '$img1_name', '$img2_name', '$img3_name', '$descripcion')";
              
    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('¡Producto creado y fotos guardadas en src/productos/ con éxito!'); window.location='admin.php';</script>"; 
        exit();
    } else {
        echo "<script>alert('Error al guardar el producto: " . mysqli_error($conexion) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto | TechNest</title>
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 40px; margin: 0; }
        .form-container { background: white; padding: 30px; max-width: 500px; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e0e0e0; }
        h2 { color: #2d6f73; margin-top: 0; font-weight: bold; }
        label { font-weight: bold; color: #333; font-size: 14px; display: block; margin-top: 10px; }
        input[type="text"], input[type="number"], input[type="file"], select, textarea { width: 100%; padding: 10px; margin: 6px 0 14px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        textarea { resize: vertical; min-height: 80px; font-family: inherit; }
        input:focus, textarea:focus, select:focus { border-color: #2d6f73; outline: none; }
        button { background-color: #f2c300; color: black; font-weight: bold; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: 0.2s; }
        button:hover { background-color: #dbb100; transform: scale(1.02); }
        .btn-volver { background-color: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin-left: 10px; font-size: 14px; font-weight: bold; text-align: center; display: inline-block; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Nuevo Producto Organizado</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Nombre del Dispositivo:</label>
        <input type="text" name="nombre" required placeholder="Ej: Nevera Haceb No Frost">

        <label>Precio Unitario (Pesos COP):</label>
        <input type="number" step="1" name="precio" required>

        <label>Cantidad en Stock:</label>
        <input type="number" name="stock" min="0" required>

        <label>Categoría del Catálogo:</label>
        <select name="categoria" required>
            <option value="conectividad y redes">conectividad y redes</option>
            <option value="perifericos de entrada">perifericos de entrada</option>
            <option value="energia y almacenamiento">energia y almacenamiento</option>
            <option value="imagen y video">imagen y video</option>
            <option value="audio y sonido">audio y sonido</option>
            <option value="ergonomia e iluminacion">ergonomia e iluminacion</option>
            <option value="electrodomestico" selected>electrodomestico</option>
        </select>

        <label>Descripción / Especificaciones:</label>
        <textarea name="descripcion" required></textarea>

        <label>Imagen Vista 1 (Nevera_1):</label>
        <input type="file" name="imagen" accept="image/*" required>
        
        <label>Imagen Vista 2 (Nevera_2):</label>
        <input type="file" name="imagen2" accept="image/*">
        
        <label>Imagen Vista 3 (Nevera_3):</label>
        <input type="file" name="imagen3" accept="image/*">

        <div style="display:flex; margin-top:10px;">
            <button type="submit">Guardar Producto</button>
            <a href="admin.php" class="btn-volver">Volver</a>
        </div>
    </form>
</div>
</body>
</html>
