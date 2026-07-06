<?php
// public/chats_antiguos.php - PARTE 1 DE 2
session_start();

// Control de seguridad institucional: Si no es vendedor, redirecciona de inmediato
if (!isset($_SESSION['vendedor_user'])) {
    header("Location: login_vendedor.php");
    exit();
}

include(__DIR__ . '/../config/conexion.php');

// Bloque de respaldo para garantizar conectividad local directa en el puerto 3307
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

$id_vendedor_actual = $_SESSION['id_vendedor'];

// 1. FOTO DE PERFIL DEL ASESOR COMMERCIAL
$query_vend = "SELECT avatar FROM usuarios WHERE id_usuario = $id_vendedor_actual LIMIT 1";
$res_vend = mysqli_query($conexion, $query_vend);
$avatar_vendedor = "assets/user.ico";
if ($res_vend && mysqli_num_rows($res_vend) > 0) {
    $info_vend = mysqli_fetch_assoc($res_vend);
    if (!empty($info_vend['avatar']) && file_exists("assets/" . $info_vend['avatar'])) {
        $avatar_vendedor = "assets/" . $info_vend['avatar'];
    }
}

// 2. CONSULTA RELACIONAL SUBQUERIES: Obtenemos el último mensaje de cada cliente único para el historial
$query_historial = "SELECT c1.id_usuario, c1.mensaje, c1.fecha_envio, c1.remitente,
                    u.usuario AS cliente_username, 
                    CONCAT(u.nombre, ' ', u.apellido) AS cliente_nombre
                    FROM chat_soporte c1
                    JOIN usuarios u ON c1.id_usuario = u.id_usuario
                    WHERE c1.id_mensaje = (
                        SELECT MAX(c2.id_mensaje) FROM chat_soporte c2 
                        WHERE c2.id_usuario = c1.id_usuario
                    )
                    ORDER BY c1.id_mensaje DESC";
$resultado_historial = mysqli_query($conexion, $query_historial);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Chats Antiguos | TechNest</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body { background-color: #f4f7f6; font-family: Arial, sans-serif; margin: 0; }
    .vendedor-header { background-color: #193f41; padding: 12px 30px; display: flex; justify-content: space-between; align-items: center; }
    .perfil-vendedor-box { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.15); padding: 6px 14px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.25); }
    .avatar-vendedor-img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid white; background: white; }
    .historial-container { max-width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
    .btn-control-chat { background: #95bcbe; color: white; border: none; padding: 8px 14px; border-radius: 20px; font-weight: bold; text-decoration: none; display: inline-block; font-size: 14px; transition: 0.2s; }
    .btn-control-chat:hover { background: #193f41; }
    .chat-row-card { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; border-bottom: 1px solid #eee; transition: background 0.2s; text-decoration: none; color: inherit; }
    .chat-row-card:hover { background: #f8faf9; }
    .chat-row-card:last-child { border-bottom: none; }
    .client-avatar-circle { width: 45px; height: 45px; background: #e8f0fe; color: #235457; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; border: 1px solid #c2dbdf; }
    .preview-msg { color: #666; font-size: 14px; margin: 4px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 500px; }
    .time-badge { color: #999; font-size: 12px; text-align: right; display: block; }

    
    /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON BITÁCORA HISTORIAL */
    
    /* Contraste Seguro: Fuerza el fondo de la bitácora mitigando parches rígidos */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo .historial-container {
        background-color: #141414 !important;
    }

    /* Modulamos la tarjeta envolvente principal y las filas de auditoría a tono grafito */
    body.alto-contraste-activo .historial-container > div,
    body.alto-contraste-activo .chat-row-card {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #444444 !important;
    }
    body.alto-contraste-activo .chat-row-card:hover {
        background-color: #383838 !important;
    }

    /* Saneamiento de los avatares circulares del cliente para alta legibilidad */
    body.alto-contraste-activo .client-avatar-circle {
        background-color: #1d3557 !important;
        color: #ffffff !important;
        border-color: #235457 !important;
    }

    /* Ajuste de badges de estados de tiempo y cierres */
    body.alto-contraste-activo .time-badge,
    body.alto-contraste-activo .preview-msg {
        color: #cccccc !important;
    }
    body.alto-contraste-activo span[style*="background: #e2f0d9"] {
        background-color: #1e4620 !important;
        color: #ffffff !important;
    }

    /* Forzado estricto de textos generales del historial corporativo */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo h2,
    body.alto-contraste-activo p,
    body.alto-contraste-activo span,
    body.alto-contraste-activo strong,
    body.alto-contraste-activo a {
        color: #ffffff !important;
    }

    /* Panel flotante del Widget siempre legible */
    body.alto-contraste-activo #panel-accesibilidad-global,
    body.alto-contraste-activo #panel-accesibilidad-global * {
        background-color: #ffffff !important;
        color: #111111 !important;
        border-color: #2d6f73 !important;
    }

    /* Tipografía Inclusiva: Verdana de baja visión rompe fuentes externas */
    body.fuente-accesible-activa,
    body.fuente-accesible-activa * {
        font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
    }
    </style>
</head>
<body>
    <!-- BARRA SUPERIOR INSTITUCIONAL CON IDENTIFICACIÓN DEL ASESOR -->
    <header class="vendedor-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="assets/logo.png" alt="logo" style="width: 35px; height: 35px; object-fit: contain;">
            <h1 style="color: white; margin: 0; font-size: 20px; font-weight: bold;">TechNest Soporte</h1>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="perfil-vendedor-box">
                <img src="<?php echo $avatar_vendedor; ?>" alt="Vendedor" class="avatar-vendedor-img">
                <span style="color: white; font-size: 14px; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['vendedor_nombre']); ?></span>
            </div>
            <a href="vendedor_chat.php" class="btn-control-chat"> Regresar a Bandeja Activa</a>
        </div>
    </header>

    <!-- PANEL CENTRAL: HISTORIAL DE CHATS ANTIGUOS -->
    <main class="historial-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
            <div>
                <h2 style="font-size: 22px; color: #235457; margin: 0; font-weight: bold;">Auditoría de Conversaciones</h2>
                <p style="color: #666; font-size: 14px; margin: 4px 0 0 0;">Historial completo de clientes que han abierto un canal de soporte técnico con la plataforma.</p>
            </div>
        </div>
        <div style="background: white; border-radius: 8px; overflow: hidden; border: 1px solid #eee;">
            <?php if ($resultado_historial && mysqli_num_rows($resultado_historial) > 0): ?>
            <?php while ($chat = mysqli_fetch_assoc($resultado_historial)): 
            // Extraemos la primera letra del nombre del cliente para armar un avatar estético tipo píldora
            $inicial = strtoupper(substr($chat['cliente_username'], 0, 1));
            // Formateamos la hora del último mensaje
            $hora_mensaje = date("d/m/Y H:i", strtotime($chat['fecha_envio']));
            // Indicamos visualmente si el último mensaje fue del cliente o del asesor
            $prefijo_remitente = ($chat['remitente'] == 'vendedor') ? 'Tú: ' : '';
            ?>
            <!-- Al hacer clic, redirige a la bandeja y carga la conversación asíncrona -->
            <a href="vendedor_chat.php?chat_con=<?php echo $chat['id_usuario']; ?>" class="chat-row-card">
            <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                <div class="client-avatar-circle">
                    <?php echo $inicial; ?>
                </div>
                <div style="display: flex; flex-direction: column; text-align: left; overflow: hidden;">
                    <span style="font-weight: bold; font-size: 15px; color: #111;">
                    <?php echo htmlspecialchars($chat['cliente_nombre']); ?>
                    <span style="font-weight: normal; color: #888; font-size: 13px;">(@<?php echo htmlspecialchars($chat['cliente_username']); ?>)</span>
                    </span>
                    <p class="preview-msg">
                    <strong><?php echo $prefijo_remitente; ?></strong><?php echo htmlspecialchars($chat['mensaje']); ?>
                    </p>
                </div>
            </div>
            <div style="min-width: 120px; text-align: right;">
                <span class="time-badge"><?php echo $hora_mensaje; ?></span>
                <span style="font-size: 11px; background: #e2f0d9; color: #1e4620; padding: 2px 8px; border-radius: 10px; font-weight: bold; margin-top: 4px; display: inline-block;">Cerrado</span>
            </div>
            </a>
            <?php endwhile; ?>
            <?php else: ?>
            <div style="text-align: center; color: #888; padding: 50px 20px; font-size: 15px;">
                <img src="assets/campana.png" alt="vacio" style="width: 40px; opacity: 0.2; margin-bottom: 12px; object-fit: contain;"><br>
                <strong>No se registran bitácoras ni conversaciones antiguas archivadas en la base de datos.</strong>
            </div>
            <?php endif; ?>
        </div>
    </main>

        <!-- SCRIPTS CENTRALES DE CONTROL Y ACCESIBILIDAD UNIVERSAL -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                
                //  SUBSISTEMA DE ACCESIBILIDAD INTEGRADO AL DOM CENTRAL
                
                const widgetAccesible = document.createElement('div');
                widgetAccesible.id = 'panel-accesibilidad-global';
                widgetAccesible.style = "position: fixed; bottom: 85px; right: 20px; background: #ffffff; border: 2px solid #2d6f73; border-radius: 12px; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); z-index: 1000000; font-family: sans-serif; display: flex; flex-direction: column; gap: 10px; width: 180px;";
                
                widgetAccesible.innerHTML = `
                    <span style="font-weight: bold; color: #2d6f73; font-size: 13px; text-align: center; display: block; border-bottom: 1px solid #eee; padding-bottom: 5px; font-family: sans-serif;">ACCESIBILIDAD</span>
                    <button id="btn-zoom-mas" style="background: #2d6f73; color: white; border: none; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Zoom +</button>
                    <button id="btn-zoom-menos" style="background: #f2f5f6; color: #333; border: 1px solid #ccc; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Zoom -</button>
                    <button id="btn-toggle-contraste" style="background: #111111; color: white; border: none; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Contraste</button>
                    <button id="btn-toggle-fuente" style="background: #f2c300; color: #111; border: none; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Tipografía</button>
                `;
                document.body.appendChild(widgetAccesible);

                // Funcionalidad 1: Escalamiento de Zoom Proporcional Directo
                let escalaZoomActual = 1.0;
                document.body.style.transformOrigin = 'top left';
                document.body.style.transition = 'transform 0.15s ease';
                
                document.getElementById('btn-zoom-mas').addEventListener('click', () => { 
                    if (escalaZoomActual < 1.3) { 
                        escalaZoomActual += 0.05; 
                        document.body.style.transform = `scale(${escalaZoomActual})`; 
                        document.body.style.width = (100 / escalaZoomActual) + '%'; 
                    } 
                });
                
                document.getElementById('btn-zoom-menos').addEventListener('click', () => { 
                    if (escalaZoomActual > 0.9) { 
                        escalaZoomActual -= 0.05; 
                        if (escalaZoomActual === 1.0) { 
                            document.body.style.removeProperty('transform'); 
                            document.body.style.removeProperty('width'); 
                        } else { 
                            document.body.style.transform = `scale(${escalaZoomActual})`; 
                            document.body.style.width = (100 / escalaZoomActual) + '%'; 
                        } 
                    } 
                });

                // Funcionalidad 2: Lógica de Alto Contraste Seguro
                document.getElementById('btn-toggle-contraste').addEventListener('click', () => {
                    document.body.classList.toggle('alto-contraste-activo');
                });

                // Funcionalidad 3: Lógica de Tipografía Verdana de Baja Visión
                let fuenteAccesibleActiva = false;
                let estiloFuentesDinamico = null;
                
                document.getElementById('btn-toggle-fuente').addEventListener('click', () => {
                    fuenteAccesibleActiva = !fuenteAccesibleActiva;
                    if (fuenteAccesibleActiva) {
                        estiloFuentesDinamico = document.createElement('style');
                        estiloFuentesDinamico.id = 'fuente-accessible-forzado';
                        estiloFuentesDinamico.innerHTML = `
                            * { 
                                font-family: 'Verdana', 'Trebuchet MS', sans-serif !important; 
                                letter-spacing: 0.5px !important; 
                            }
                        `;
                        document.head.appendChild(estiloFuentesDinamico);
                    } else {
                        if (estiloFuentesDinamico) { 
                            estiloFuentesDinamico.remove(); 
                            estiloFuentesDinamico = null; 
                        }
                    }
                });
            });
        </script>
</body>
</html>
