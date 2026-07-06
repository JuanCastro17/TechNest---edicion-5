<?php
session_start();

if (!isset($_SESSION['admin_user'])) { 
    header("Location: login_admin.php"); 
    exit(); 
}

include(__DIR__ . '/../config/conexion.php');
if (!isset($conexion) || !$conexion) { 
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307); 
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM productos WHERE id = $id";
    $resultado = mysqli_query($conexion, $query);
    if ($resultado && mysqli_num_rows($resultado) == 1) { 
        $producto = mysqli_fetch_assoc($resultado); 
    } else { 
        header("Location: admin.php"); 
        exit(); 
    }
} else { 
    header("Location: admin.php"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $precio_limpio = preg_replace('/[^\d.]/', '', $_POST['precio']);
    $precio = floatval($precio_limpio);
    $stock  = intval($_POST['stock']);
    $categoria = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
    
    // FUNCIÓN CORPORATIVA: Generar nombre limpio basado en el texto editado
    $nombre_limpio = strtolower(trim($_POST['nombre']));
    $nombre_limpio = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' ', 'ñ'], 
        ['a', 'e', 'i', 'o', 'u', 'n', '_', 'n'], 
        $nombre_limpio
    );
    $nombre_limpio = preg_replace('/[^a-z0-8_]/', '', $nombre_limpio);

    $directorio_destino = __DIR__ . "/../src/productos/";
    $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    
    // Si no se suben archivos nuevos, se conservan los nombres que ya estaban guardados
    $img1_name = $producto['imagen']; 
    $img2_name = $producto['imagen2']; 
    $img3_name = $producto['imagen3'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $permitidos)) { 
            $img1_name = $nombre_limpio . "_1." . $ext; 
            move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio_destino . $img1_name); 
        }
    }
    if (isset($_FILES['imagen2']) && $_FILES['imagen2']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen2']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $permitidos)) { 
            $img2_name = $nombre_limpio . "_2." . $ext; 
            move_uploaded_file($_FILES['imagen2']['tmp_name'], $directorio_destino . $img2_name); 
        }
    }
    if (isset($_FILES['imagen3']) && $_FILES['imagen3']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen3']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $permitidos)) { 
            $img3_name = $nombre_limpio . "_3." . $ext; 
            move_uploaded_file($_FILES['imagen3']['tmp_name'], $directorio_destino . $img3_name); 
        }
    }

    $query_update = "UPDATE productos SET 
                     nombre = '$nombre', precio = '$precio', stock = '$stock', categoria = '$categoria', 
                     imagen = '$img1_name', imagen2 = '$img2_name', imagen3 = '$img3_name', descripcion = '$descripcion' 
                     WHERE id = $id";
                     
    if (mysqli_query($conexion, $query_update)) { 
        echo "<script>alert('¡Producto y nombres de archivo actualizados con éxito!'); window.location='admin.php';</script>"; 
        exit(); 
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Editar Producto | TechNest</title>
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <style>
        body { font-family: sans-serif; background-color: #f2f5f6; padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 80vh; margin: 0; }
        .form-container { background: white; padding: 30px; width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 4px solid #dfe8ea; }
        h2 { color: #2d6f73; margin-top: 0; font-size: 26px; font-weight: bold; margin-bottom: 20px; }
        label { font-weight: bold; color: #333; display: block; margin-bottom: 6px; font-size: 14px; margin-top: 10px; }
        input[type="text"], input[type="number"], input[type="file"], select, textarea { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 15px; outline: none; }
        textarea { resize: vertical; min-height: 80px; font-family: inherit; }
        .btn-group { display: flex; gap: 15px; margin-top: 10px; }
        .save-btn { background-color: #f2c300; color: black; font-weight: bold; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; flex: 1; font-size: 15px; }
        .back-btn { background-color: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; text-align: center; flex: 1; font-size: 15px; display:flex; align-items:center; justify-content:center; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Modificar Producto</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Nombre del Producto:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

        <label>Precio ($ Pesos COP):</label>
        <input type="number" step="1" name="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>

        <label>Cantidad en Stock:</label>
        <input type="number" name="stock" min="0" value="<?php echo $producto['stock']; ?>" required>

        <label>Categoría del Catálogo:</label>
        <select name="categoria" required>
            <?php 
            $cats = ['conectividad y redes', 'perifericos de entrada', 'energia y almacenamiento', 'imagen y video', 'audio y sonido', 'ergonomia e iluminacion', 'electrodomestico'];
            foreach($cats as $c):
                $sel = ($producto['categoria'] == $c) ? 'selected' : '';
                echo "<option value='$c' $sel>$c</option>";
            endforeach;
            ?>
        </select>

        <label>Descripción / Especificaciones:</label>
        <textarea name="descripcion" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>

        <label>Cambiar Imagen Principal:</label>
        <input type="file" name="imagen" accept="image/*">
        
        <label>Cambiar Imagen 2:</label>
        <input type="file" name="imagen2" accept="image/*">
        
        <label>Cambiar Imagen 3:</label>
        <input type="file" name="imagen3" accept="image/*">

        <div class="btn-group">
            <button type="submit" class="save-btn">Actualizar Cambios</button>
            <a href="admin.php" class="back-btn">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
