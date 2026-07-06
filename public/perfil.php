<?php
session_start();

// CORRECCIÓN CAPAS: Como el archivo está en public/, salimos un nivel para buscar config/
include(__DIR__ . '/../config/conexion.php');

// Bloque de respaldo para garantizar conectividad en tu puerto local 3307
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// Si no hay sesión activa de usuario de la tienda, patearlo al login de cabeza
if (!isset($_SESSION['id_usuario'])) {
    header("Location: seleccion_login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = "";

// 1. Traer los datos más frescos del usuario desde la BD en tiempo real
$query = "SELECT * FROM usuarios WHERE id_usuario = '$id_usuario' LIMIT 1";
$resultado = mysqli_query($conexion, $query);
$usuario_db = mysqli_fetch_assoc($resultado);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_usuario = mysqli_real_escape_string($conexion, trim($_POST['usuario']));
    $nuevo_correo = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $nuevo_celular = mysqli_real_escape_string($conexion, trim($_POST['celular']));
    
    $nombre_actual = $usuario_db['usuario'];
    $subida_ok = true;
    
    // Conservamos el nombre de la imagen que ya existía en la base de datos
    $nombre_imagen = isset($usuario_db['avatar']) ? $usuario_db['avatar'] : 'user.ico'; 

    // 2. VALIDACIÓN TÁCTICA: Evitar nombres de usuario duplicados
    if ($nuevo_usuario != $nombre_actual) {
        $check_user = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE usuario = '$nuevo_usuario' LIMIT 1");
        if (mysqli_num_rows($check_user) > 0) {
            $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2 fw-bold' style='font-size: 14px;'>El nombre de usuario '$nuevo_usuario' ya existe. Elige otro.</div>";
            $subida_ok = false;
        }
    }

    // 3. PROCESAR SUBIDA Y RENOMBRADO DEL AVATAR BASADO EN SU PROPIO USUARIO
    if ($subida_ok && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        // CORRECCIÓN RUTA: Redirigimos la carpeta física de uploads/ hacia la carpeta unificada assets/
        $directorio = "assets/";
        
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $formatos_validos = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extension, $formatos_validos)) {
            // REQUERIMIENTO CUMPLIDO: Limpiamos caracteres extraños y nombramos el archivo según su cuenta de usuario
            $usuario_limpio = preg_replace('/[^a-zA-Z0-9_]/', '', $nuevo_usuario);
            $nombre_new_imagen = strtolower($usuario_limpio) . "_avatar." . $extension;
            $ruta_destino = $directorio . $nombre_new_imagen;

            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
                $nombre_imagen = $nombre_new_imagen; // Actualizamos la variable con el nuevo nombre
            } else {
                $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2 fw-bold' style='font-size: 14px;'>Error al guardar el archivo en el servidor.</div>";
                $subida_ok = false;
            }
        } else {
            $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2 fw-bold' style='font-size: 14px;'>Formato inválido. Solo se permite JPG, PNG o WEBP.</div>";
            $subida_ok = false;
        }
    }

    // 4. GUARDAR CAMBIOS EN LA BASE DE DATOS SI TODO ESTÁ CORRECTO
    if ($subida_ok) {
        $update_query = "UPDATE usuarios SET usuario = '$nuevo_usuario', correo = '$nuevo_correo', celular = '$nuevo_celular', avatar = '$nombre_imagen' WHERE id_usuario = '$id_usuario'";
        
        if (mysqli_query($conexion, $update_query)) {
            $mensaje = "<div class='alert alert-success text-center rounded-pill py-2 fw-bold' style='font-size: 14px;'>¡Datos guardados con éxito!</div>";
            
            // Forzamos la actualización de las variables de sesión para el index de inmediato sin parpadeos
            $_SESSION['tienda_user'] = $nuevo_usuario;
            $_SESSION['tienda_avatar'] = (file_exists("assets/" . $nombre_imagen)) ? "assets/" . $nombre_imagen : "assets/user.ico";
            
            // Recargamos el array del usuario para pintar los datos nuevos en pantalla al instante
            $resultado = mysqli_query($conexion, $query);
            $usuario_db = mysqli_fetch_assoc($resultado);
        } else {
            $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2 fw-bold' style='font-size: 14px;'>Error al actualizar los registros en la base de datos.</div>";
        }
    }
}

// Determinar qué ruta usar para previsualizar el avatar en la tarjeta de forma local
$avatar_render = (isset($usuario_db['avatar']) && file_exists("assets/" . $usuario_db['avatar'])) ? "assets/" . $usuario_db['avatar'] : "assets/user.ico";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Configuración de Perfil | TechNest</title>
<!-- Restauramos tus enlaces estáticos de diseño originales -->
<link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/png" href="assets/logoicono.ico">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    /* Estilos inline extraídos de tu foto para asegurar que quede idéntico */
    body { background-color: #f2f5f6; font-family: sans-serif; margin: 0; padding: 0; }
    .profile-header { width: 100%; background-color: #2d6f73; display: flex; align-items: center; justify-content: space-between; padding: 15px 30px; box-sizing: border-box; }
    .profile-header img { width: 45px; height: 45px; object-fit: contain; }
    .profile-header h1 { color: white; font-size: 24px; margin: 0; font-weight: bold; }
    .btn-volver-inicio { background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 18px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 13px; text-decoration: none; }
    .main-content { display: flex; justify-content: center; align-items: center; min-height: 85vh; padding: 20px; }
    .card-profile { background: white; border: 1px solid #ccc; border-radius: 15px; padding: 40px 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); max-width: 440px; width: 100%; text-align: center; }
    .card-profile h2 { color: #2d6f73; font-size: 26px; margin-top: 0; margin-bottom: 25px; font-weight: bold; }
    .avatar-view { width: 110px; height: 110px; object-fit: cover; border-radius: 50%; border: 3px solid #2d6f73; background: white; margin-bottom: 15px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; text-align: left; }
    .form-group label { font-weight: bold; color: #333; font-size: 14px; padding-left: 10px; }
    .form-group input { width: 100%; padding: 11px 18px; border: 1px solid #ccc; border-radius: 25px; font-size: 14px; outline: none; box-sizing: border-box; background: white; }
    .form-group input:focus { border-color: #2d6f73; }
    .input-readonly { background-color: #f8fafb !important; color: #777 !important; }
    .btn-yellow { width: 100%; background: #f2c300; border: none; padding: 12px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; color: #111; margin-top: 10px; text-transform: uppercase; }
    .btn-yellow:hover { transform: scale(1.02); background: #dbb100; }
</style>
</head>
<body>

<header class="profile-header">
    <div style="display: flex; align-items: center; gap: 12px;">
        <img src="assets/logo.png" alt="Logo">
        <h1>TechNest</h1>
    </div>
    <a href="index.php" class="btn-volver-inicio">Volver al inicio</a>
</header>

<main class="main-content">
    <div class="card-profile">
        <h2>Editar Perfil</h2>

        <!-- Imprime los mensajes de alerta o éxito con tu diseño -->
        <?php echo $mensaje; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            
            <!-- SECCIÓN DEL AVATAR IDÉNTICA A TU FOTO -->
            <div style="margin-bottom: 20px;">
                <img src="<?php echo $avatar_render; ?>" alt="Foto Perfil" class="avatar-view">
                <div class="form-group">
                    <label style="color: #666; font-size: 12px;">Subir nueva foto de perfil</label>
                    <input type="file" name="foto_perfil" accept="image/*" style="border-radius: 25px; padding: 8px 15px;">
                </div>
            </div>

            <!-- CAMPOS DEL FORMULARIO CON BORDES REDONDEADOS TIPO PÍLDORA -->
            <div class="form-group">
                <label>Nombre completo (Lectura)</label>
                <input type="text" class="input-readonly" value="<?php echo htmlspecialchars($usuario_db['nombre'] . ' ' . $usuario_db['apellido']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Nombre de usuario</label>
                <input type="text" name="usuario" value="<?php echo htmlspecialchars($usuario_db['usuario']); ?>" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario_db['correo']); ?>" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Número de celular</label>
                <input type="text" name="celular" placeholder="Ej: 3001234567" value="<?php echo htmlspecialchars(isset($usuario_db['celular']) ? $usuario_db['celular'] : ''); ?>" autocomplete="off">
            </div>

            <button type="submit" class="btn-yellow">Guardar Cambios</button>
        </form>
    </div>
</main>

</body>
</html>
