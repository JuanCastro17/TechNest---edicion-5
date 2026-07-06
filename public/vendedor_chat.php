<?php
// public/vendedor_chat.php - PARTE 1 DE 3 (LOGICA BACKEND PHP)
session_start();

// Control de seguridad corporativo: Si no es vendedor, regresa al login
if (!isset($_SESSION['vendedor_user'])) {
    header("Location: login_vendedor.php");
    exit();
}

include(__DIR__ . '/../config/conexion.php'); 

$id_vendedor_actual = $_SESSION['id_vendedor'];
$mensaje_alerta = "";

// 1. OBTENER INFORMACIÓN FRESCA DEL VENDEDOR (FOTO DE PERFIL)
$query_vend = "SELECT avatar FROM usuarios WHERE id_usuario = $id_vendedor_actual LIMIT 1";
$res_vend = mysqli_query($conexion, $query_vend);
$avatar_vendedor = "assets/user.ico"; 
if ($res_vend && mysqli_num_rows($res_vend) > 0) {
    $info_vend = mysqli_fetch_assoc($res_vend);
    if (!empty($info_vend['avatar']) && file_exists("uploads/" . $info_vend['avatar'])) {
        $avatar_vendedor = "uploads/" . $info_vend['avatar'];
    }
}

// 2. ESTILO GMAIL: LÓGICA PARA ARCHIVAR CHAT (DESAPARECE AL INSTANTE)
if (isset($_GET['archivar_chat'])) {
    $id_user_archivar = intval($_GET['archivar_chat']);
    $query_archivar = "UPDATE chat_soporte SET estado_chat = 'archivado' 
                       WHERE id_usuario = $id_user_archivar AND id_vendedor = $id_vendedor_actual";
    if (mysqli_query($conexion, $query_archivar)) {
        header("Location: vendedor_chat.php");
        exit();
    }
}

// BANDEJA GMAIL: Filtrar y traer solo clientes que tengan tickets en estado 'activo'
$clientes_activos_res = mysqli_query($conexion, "SELECT DISTINCT u.id_usuario, u.usuario FROM chat_soporte c JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE c.id_vendedor = $id_vendedor_actual AND c.estado_chat = 'activo'");
$id_usuario_seleccionado = isset($_GET['chat_con']) ? intval($_GET['chat_con']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>Bandeja de Asistencia Técnica | TechNest</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body { background-color: #f4f7f6; font-family: Arial, sans-serif; margin: 0; }
    .vendedor-header { background-color: #235457; padding: 12px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .vendedor-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
    .panel-chat-asistencia { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; display: flex; min-height: 500px; overflow: hidden; }
    .chat-listado-clientes { width: 300px; border-right: 1px solid #eee; background: #fafbfc; display: flex; flex-direction: column; }
    .gmail-sidebar-header { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f8faf9; }
    .gmail-item-link { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; text-decoration: none; color: #333; font-size: 14px; border-bottom: 1px solid #f1f3f4; font-weight: 500; transition: background 0.15s; }
    .gmail-item-link:hover { background: #f2f3f5; color: #111; }
    .gmail-item-active { background: #e8f0fe !important; color: #1a73e8 !important; border-left: 4px solid #1a73e8; }
    .chat-box-mensajes { flex: 1; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff; }
    .gmail-chat-topbar { padding: 15px 20px; background: #fdfdfd; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .chat-area-scroll { height: 320px; overflow-y: auto; padding: 20px; background: #ffffff; display: flex; flex-direction: column; gap: 12px; }
    .msg-globo { padding: 10px 14px; border-radius: 16px; max-width: 65%; font-size: 14px; word-wrap: break-word; line-height: 1.4; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .msg-vendedor { background: #235457; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
    .msg-cliente { background: #f1f3f4; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; }
    .perfil-vendedor-box { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.15); padding: 6px 14px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.25); }
    .avatar-vendedor-img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid white; background: white; }
    @media (max-width: 900px) { .panel-chat-asistencia { flex-direction: column; } .chat-listado-clientes { width: 100%; border-right: none; } }

    /* ========================================================================== */
    /* ♿ SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON BANDEJA ASISTENCIA */
    /* ========================================================================== */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo .vendedor-container {
        background-color: #141414 !important;
    }

    /* Modulamos el contenedor completo y las subcapas de mensajería a tono grafito corporativo */
    body.alto-contraste-activo .panel-chat-asistencia,
    body.alto-contraste-activo .chat-listado-clientes,
    body.alto-contraste-activo .gmail-sidebar-header,
    body.alto-contraste-activo .chat-box-mensajes,
    body.alto-contraste-activo .gmail-chat-topbar,
    body.alto-contraste-activo .chat-area-scroll,
    body.alto-contraste-activo #formChatVendedor {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #444444 !important;
    }

    /* 🛠️ REQUERIMIENTO CUMPLIDO: Saneamos por ID y Atributo el visor central para tumbar el parche blanco inline */
    body.alto-contraste-activo div[style*="background:#fcfdfe"],
    body.alto-contraste-activo div[style*="background: #fcfdfe"],
    body.alto-contraste-activo .chat-box-mensajes > div {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
    }

    /* Elementos de la lista lateral tipo Gmail en modo nocturno */
    body.alto-contraste-activo .gmail-item-link {
        background-color: #262626 !important;
        color: #ffffff !important;
        border-bottom: 1px solid #444444 !important;
    }
    body.alto-contraste-activo .gmail-item-link:hover {
        background-color: #333333 !important;
    }
    body.alto-contraste-activo .gmail-item-active {
        background-color: #1d3557 !important;
        color: #64b5f6 !important;
        border-left: 4px solid #64b5f6 !important;
    }

    /* Globos de texto asíncronos cruzados */
    body.alto-contraste-activo .msg-vendedor {
        background-color: #0f4c4e !important;
        color: #ffffff !important;
        border: 1px solid #235457 !important;
    }
    body.alto-contraste-activo .msg-cliente {
        background-color: #3a3a3a !important;
        color: #ffffff !important;
        border: 1px solid #555555 !important;
    }

    /* Inputs de respuesta rápida */
    body.alto-contraste-activo #inputMensajeVendedor {
        background-color: #1e1e1e !important;
        color: #ffffff !important;
        border-color: #555555 !important;
    }
    body.alto-contraste-activo #inputMensajeVendedor::placeholder {
        color: #b3b3b3 !important;
    }

    /* Saneamiento de textos generales de la bandeja de entrada */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo span,
    body.alto-contraste-activo p,
    body.alto-contraste-activo label,
    body.alto-contraste-activo div {
        color: #ffffff !important;
    }

    /* Resguardo del panel flotante del Widget */
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
    <header class="vendedor-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="assets/logo.png" alt="logo" style="width: 35px; height: 35px; object-fit: contain;">
            <h1 style="color: white; margin: 0; font-size: 20px; font-weight: bold;">TechNest Interno - Soporte</h1>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="perfil-vendedor-box">
                <img src="<?php echo $avatar_vendedor; ?>" alt="Vendedor" class="avatar-vendedor-img">
                <span style="color: white; font-size: 14px; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['vendedor_nombre']); ?></span>
            </div>
            <a href="vendedor.php" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 14px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 12px; text-decoration: none;">Ir a Ventas</a>
            <a href="logout.php" style="background: #ff3b30; color: white; padding: 8px 14px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 12px; text-decoration: none;">Salir</a>
        </div>
    </header>
    <main class="vendedor-container">
        <section class="panel-chat-asistencia">
            <!-- SUBPARTE B: BANDEJA DE ENTRADA TIPO GMAIL (CLIENTES ACTIVOS) -->
            <div class="chat-listado-clientes">
                <div class="gmail-sidebar-header">
                    <span style="font-weight: bold; font-size: 14px; color: #235457;"> Mensajes Activos</span>
                    <a href="chats_antiguos.php" class="btn btn-sm btn-outline-secondary" style="font-size: 11px; border-radius: 20px; text-decoration: none; font-weight: bold; padding: 4px 10px; border: 1px solid #ccc; color: #555;">Historial</a>
                </div>
                <?php if(mysqli_num_rows($clientes_activos_res) > 0): ?>
                    <?php while($c_row = mysqli_fetch_assoc($clientes_activos_res)): 
                        $active_class = ($c_row['id_usuario'] == $id_usuario_seleccionado) ? 'gmail-item-active' : '';
                    ?>
                        <a href="?chat_con=<?php echo $c_row['id_usuario']; ?>" class="gmail-item-link <?php echo $active_class; ?>">
                            <span><?php echo htmlspecialchars($c_row['usuario']); ?></span>
                            <span class="badge bg-danger rounded-pill" style="font-size: 10px; background-color: #dc3545; color: white; padding: 3px 8px; border-radius: 10px;">Nuevo</span>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="font-size:13px; color:#888; padding: 20px; text-align: center; margin: 0;">Bandeja de entrada vacía.</p>
                <?php endif; ?>
            </div>

            <!-- VISOR CENTRAL DE LA CONVERSACIÓN -->
            <div class="chat-box-mensajes">
                <?php if($id_usuario_seleccionado > 0): 
                    $user_q = mysqli_query($conexion, "SELECT usuario FROM usuarios WHERE id_usuario = $id_usuario_seleccionado LIMIT 1");
                    $u_data = mysqli_fetch_assoc($user_q);
                ?>
                    <div class="gmail-chat-topbar">
                        <span style="font-weight: bold; color: #333;">Conversación con: <?php echo htmlspecialchars($u_data['usuario']); ?></span>
                        <!-- ARCHIVAR TICKET: Al pulsarlo, el estado pasa a 'archivado' y desaparece de la vista activa -->
                        <a href="?archivar_chat=<?php echo $id_usuario_seleccionado; ?>" class="btn btn-sm btn-success fw-bold text-white px-3" style="font-size: 12px; border-radius: 4px; background-color: #198754; color: white; padding: 6px 12px; text-decoration: none;" onclick="return confirm('¿Marcar consulta como solucionada y archivar chat?')">✕ Resolver y Archivar</a>
                    </div>
                    <div class="chat-area-scroll" id="vendedorChatBody">
                        <!-- Las burbujas se inyectarán de forma asíncrona mediante JavaScript -->
                    </div>
                    <form id="formChatVendedor" style="display:flex; gap:10px; padding: 15px; border-top: 1px solid #eee;">
                        <input type="text" id="inputMensajeVendedor" required placeholder="Escribe una respuesta rápida..." style="flex:1; padding:10px 15px; border:1px solid #ccc; border-radius:25px; outline:none; font-size: 14px;">
                        <button type="submit" class="btn btn-primary px-4 fw-bold" style="border-radius: 25px; font-size: 14px; background-color: #23a02a; border: none; color: white; padding: 0 20px; cursor: pointer;">Enviar</button>
                    </form>
                <?php else: ?>
                    <div style="display:flex; flex-direction: column; justify-content:center; align-items:center; height:100%; color:#888; font-size:14px; background:#fcfdfe; padding: 40px; text-align: center; gap: 10px; id='visorVacio'">
                        <span style="font-size: 30px;"></span>
                        <span>Selecciona un ticket de soporte de la bandeja de entrada para asistir al ciudadano en tiempo real.</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
    const idSeleccionado = <?php echo $id_usuario_seleccionado; ?>;
    const chatBody = document.getElementById('vendedorChatBody');
    const formChat = document.getElementById('formChatVendedor');
    const inputMensaje = document.getElementById('inputMensajeVendedor');
    let ultimoCount = 0;

    function cargarMensajesVendedor() {
        if (idSeleccionado === 0) return;
        fetch(`api/mensajes_chat.php?chat_con=${idSeleccionado}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(msg => {
                    const claseGlobo = (msg.remitente === 'vendedor') ? 'msg-vendedor' : 'msg-cliente';
                    html += `<div class="msg-globo ${claseGlobo}">${escapeHTML(msg.mensaje)}</div>`;
                });
                chatBody.innerHTML = html;
                if (data.length !== ultimoCount) {
                    chatBody.scrollTop = chatBody.scrollHeight;
                    ultimoCount = data.length;
                }
            })
            .catch(err => console.error("Error en sincronización asíncrona:", err));
    }

    if (formChat) {
        formChat.addEventListener('submit', function(e) {
            e.preventDefault();
            const texto = inputMensaje.value.trim();
            if (!texto || idSeleccionado === 0) return;
            inputMensaje.value = '';
            
            fetch('api/mensajes_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensaje: texto, chat_con: idSeleccionado })
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.status === 'success') {
                    cargarMensajesVendedor();
                }
            });
        });
    }

    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag));
    }

    if (idSeleccionado > 0) {
        setInterval(cargarMensajesVendedor, 2000);
        cargarMensajesVendedor();
    }

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
                estiloFuentesDinamico.id = 'fuente-accesible-forzado';
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
