<?php
// Inicializar el motor de sesiones
session_start();

// Borrar absolutamente todas las variables de sesión activas (tanto clientes como administradores)
$_SESSION = array();

// Si se desea destruir la cookie de sesión de forma estricta
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir por completo la sesión en el servidor
session_destroy();

// NOTA: Eliminamos 'header("Location: index.php");' para permitir que el script inferior limpie el navegador primero
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TechNest - Cerrando Sesión</title>
</head>
<body>
    <script>
        // 🛠️ SOLUCIÓN AL BUG DE PRIVACIDAD: Purgamos la memoria local antes de redirigir
        localStorage.removeItem('technest_carrito');
        localStorage.removeItem('technest_notificaciones');
        localStorage.removeItem('technest_nuevas_notificaciones');
        localStorage.removeItem('user_direccion');
        localStorage.removeItem('user_descripcion');

        // Redirigir al usuario a la pantalla principal de la tienda de forma segura
        window.location.href = "index.php";
    </script>
</body>
</html>
