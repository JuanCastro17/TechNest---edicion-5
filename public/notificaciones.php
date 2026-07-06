<?php
session_start();

$usuario_activo = isset($_SESSION['tienda_user']) ? $_SESSION['tienda_user'] : null;
$avatar_activo = isset($_SESSION['tienda_avatar']) ? $_SESSION['tienda_avatar'] : 'user.ico';

// Seguridad: Si no hay usuario activo, lo mandamos al login
if (!$usuario_activo) {
    header("Location: seleccion_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - TechNest</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Estilos específicos para la vista limpia de notificaciones en móvil */
        body {
            background-color: #f4f7f6;
            margin: 0;
            padding-bottom: 80px; /* Espacio para que la barra inferior no tape nada */
        }

        .mobile-noti-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 15px;
        }

        .mobile-noti-header {
            background: #2d6f73;
            color: white;
            display: flex;
            align-items: center;
            padding: 15px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-atras {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-right: 15px;
            text-decoration: none;
        }

        .mobile-noti-title {
            font-size: 18px;
            font-weight: bold;
            flex-grow: 1;
            margin: 0;
        }

        .btn-limpiar-notis {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
        }

        .noti-lista-movil {
            margin-top: 15px;
        }

        .noti-item-movil {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-left: 5px solid #2d6f73;
            animation: fadeIn 0.3s ease-in-out;
        }

        .noti-item-movil p {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
        }

        .noti-item-movil .meta-noti {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #999;
        }

        .noti-vacia-movil {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }

        .noti-vacia-movil img {
            width: 80px;
            opacity: 0.5;
            margin-bottom: 15px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="mobile-noti-header">
        <a href="index.php" class="btn-atras">←</a>
        <h1 class="mobile-noti-title">Notificaciones</h1>
        <button class="btn-limpiar-notis" onclick="borrarTodasLasNotis()">Borrar todo</button>
    </div>

    <div class="mobile-noti-container">
        <div id="lista-notificaciones-target" class="noti-lista-movil">
            </div>
    </div>

    <nav class="bottom-nav">
        <a href="index.php" class="bottom-nav-item">
            <img src="carrito.png" alt="Catálogo" class="bottom-nav-icon">
            <span>Catálogo</span>
        </a>
        <a href="index.php" class="bottom-nav-item bottom-nav-center">
            <img src="<?php echo $avatar_activo; ?>" alt="Perfil" class="bottom-nav-avatar">
            <span><?php echo $usuario_activo; ?></span>
        </a>
        <a href="#" class="bottom-nav-item">
            <span class="bottom-nav-badge">%</span>
            <span>Ofertas</span>
        </a>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            renderizarNotificacionesMovil();
            
            // Cuando entran a esta pantalla, limpiamos automáticamente el punto de alerta rojo
            localStorage.setItem('technest_nuevas_notificaciones', 'false');
        });

        function renderizarNotificacionesMovil() {
            const contenedor = document.getElementById('lista-notificaciones-target');
            if (!contenedor) return;

            const listaNotis = JSON.parse(localStorage.getItem('technest_notificaciones')) || [];

            if (listaNotis.length === 0) {
                contenedor.innerHTML = `
                    <div class="noti-vacia-movil">
                        <p>No tienes notificaciones por el momento.</p>
                        <span style="font-size: 13px; color: #aaa;">Tus alertas de compra aparecerán aquí</span>
                    </div>
                `;
                return;
            }

            // Muestra las notificaciones de la más reciente a la más antigua
            contenedor.innerHTML = listaNotis.reverse().map((n, index) => `
                <div class="noti-item-movil">
                    <p>${n.texto}</p>
                    <div class="meta-noti">
                        <span>⏱️ ${n.fecha}</span>
                        <span style="color: #2d6f73; font-weight: bold;">Pendiente</span>
                    </div>
                </div>
            `).join('');
        }

        function borrarTodasLasNotis() {
            if(confirm("¿Seguro que quieres eliminar el historial de notificaciones?")) {
                localStorage.removeItem('technest_notificaciones');
                renderizarNotificacionesMovil();
            }
        }
    </script>

</body>
</html>