<?php
session_start();

// Control de seguridad: Si no validó credenciales en la pantalla anterior, regresa
if (!isset($_SESSION['id_usuario'])) {
    header("Location: chat_autenticacion.php");
    exit();
}

include(__DIR__ . '/../config/conexion.php');

$id_usuario_logueado = $_SESSION['id_usuario'];
$usuario_nombre = $_SESSION['tienda_user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sala de Asistencia | Chat-TechNest</title>
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background-color: #f4f7f6; font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .chat-header { width: 100%; background-color: #2d6f73; display: flex; align-items: center; justify-content: space-between; padding: 15px 30px; box-sizing: border-box; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .chat-header img { width: 40px; height: 40px; object-fit: contain; }
        .chat-header h1 { color: white; font-size: 22px; margin: 0; font-weight: bold; }
        .sala-container { flex: 1; max-width: 650px; width: 100%; margin: 30px auto; padding: 0 20px; display: flex; flex-direction: column; }
        .chat-wrapper-box { background: white; border: 1px solid #ccc; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; flex-direction: column; height: 500px; justify-content: space-between; }
        .chat-stream-scroll { height: 380px; overflow-y: auto; background: #f8fafb; border: 1px solid #eef2f3; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; gap: 12px; }
        .globo-msg { padding: 10px 14px; border-radius: 15px; max-width: 75%; font-size: 14px; word-wrap: break-word; line-height: 1.4; }
        .globo-cliente { background: #2d6f73; color: white; align-self: flex-end; border-bottom-right-radius: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .globo-vendedor { background: #e4ebed; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .form-control { border-radius: 25px; padding: 10px 15px; font-size: 15px; border: 1px solid #ccc; background-color: #ffffff; color: #333; }
        .form-control:focus { border-color: #2d6f73; box-shadow: none; }
        .btn-send { background-color: #009e49; color: white; border: none; padding: 0 25px; border-radius: 25px; font-weight: bold; font-size: 15px; transition: background 0.2s; cursor: pointer; }
        .btn-send:hover { background-color: #00803a; }
        .input-group { display: flex; gap: 10px; width: 100%; }
        .input-group .form-control { flex: 1; }

        
        /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON SALA DE CHAT */
        
        /* Contraste Seguro: Modulación de fondos oscuros e hilos de chat */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        body.alto-contraste-activo main,
        body.alto-contraste-activo header,
        body.alto-contraste-activo .sala-container {
            background-color: #141414 !important;
        }
        body.alto-contraste-activo .chat-wrapper-box {
            background-color: #1e1e1e !important;
            border-color: #444444 !important;
        }
        body.alto-contraste-activo .chat-stream-scroll {
            background-color: #121212 !important;
            border-color: #333333 !important;
        }

        /* REQUERIMIENTO CUMPLIDO: Diferenciación de burbujas en alto contraste para evitar bugs visuales */
        body.alto-contraste-activo .globo-cliente {
            background-color: #0f4c4e !important; /* Tono verde pino oscuro nítido */
            color: #ffffff !important;
            border: 1px solid #2d6f73 !important;
        }
        body.alto-contraste-activo .globo-vendedor {
            background-color: #2d2d2d !important; /* Tono grafito suave para el asesor */
            color: #ffffff !important;
            border: 1px solid #555555 !important;
        }

        /* Inputs de escritura en modo nocturno */
        body.alto-contraste-activo .form-control {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border-color: #555555 !important;
        }
        body.alto-contraste-activo .form-control::placeholder {
            color: #b3b3b3 !important;
        }

        /* Letras e identificadores del chat superior */
        body.alto-contraste-activo h1,
        body.alto-contraste-activo span,
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

        /* Tipografía Universal: Forzado estricto de Verdana para baja visión */
        body.fuente-accesible-activa,
        body.fuente-accesible-activa * {
            font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR CON TU MARCA -->
    <header class="chat-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="assets/logo.png" alt="logo">
            <h1>Chat-TechNest Asistencia</h1>
        </div>
        <div>
            <span style="color: white; font-size: 14px; font-weight: bold; margin-right: 15px;"> <?php echo htmlspecialchars($usuario_nombre); ?></span>
            <a href="logout.php" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 6px 14px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 12px; text-decoration: none;">Salir</a>
        </div>
    </header>

    <!-- CONTENEDOR CENTRAL DE LA SALA -->
    <main class="sala-container">
        <div class="chat-wrapper-box">
            
            <!-- CANAL DE MENSAJES ASÍNCRONO -->
            <div class="chat-stream-scroll" id="salaChatBody">
                <!-- Se cargará mediante JavaScript sin pestañear -->
            </div>

            <!-- FORMULARIO INTERCEPTADO POR JAVASCRIPT -->
            <form id="formChatCliente" style="margin-top: 15px;">
                <div class="input-group">
                    <input type="text" id="inputMensajeCliente" required class="form-control" placeholder="Escribe tu mensaje aquí..." autocomplete="off">
                    <button type="submit" class="btn-send text-uppercase">Enviar</button>
                </div>
            </form>

        </div>
    </main>
        <!-- MOTOR SCRIPT ASÍNCRONO DEL CHAT Y ACCESIBILIDAD UNIVERSAL -->
    <script>
        const chatBody = document.getElementById('salaChatBody');
        const formChat = document.getElementById('formChatCliente');
        const inputMensaje = document.getElementById('inputMensajeCliente');
        let ultimoCount = 0;

        // FUNCIÓN UNIFICADA: TRAER MENSAJES DE LA API EN JSON
        function cargarLosMensajesDelSala() {
            fetch('../api/mensajes_chat.php')
                .then(response => response.json())
                .then(data => {
                    // Si no hay mensajes, ponemos el aviso por defecto
                    if (data.length === 0) {
                        chatBody.innerHTML = `<p style="text-align: center; color: #888; font-size: 13px; margin: auto;">¡Validación exitosa! Describe tu problema aquí abajo para que un asesor te asista.</p>`;
                        return;
                    }

                    // Limpiamos y redibujamos las burbujas
                    let html = '';
                    data.forEach(msg => {
                        const claseGlobo = (msg.remitente === 'cliente') ? 'globo-cliente' : 'globo-vendedor';
                        html += `<div class="globo-msg ${claseGlobo}">${escapeHTML(msg.mensaje)}</div>`;
                    });
                    
                    chatBody.innerHTML = html;

                    // Si entraron mensajes nuevos, forzamos el auto-scroll abajo
                    if (data.length !== ultimoCount) {
                        chatBody.scrollTop = chatBody.scrollHeight;
                        ultimoCount = data.length;
                    }
                })
                .catch(err => console.error("Error en sincronización de chat:", err));
        }

        // ENVIAR MENSAJE CON FETCH (SIN RECARGAR PÁGINA)
        if (formChat) {
            formChat.addEventListener('submit', function(e) {
                e.preventDefault();
                const texto = inputMensaje.value.trim();
                if (!texto) return;

                inputMensaje.value = ''; // Limpiamos la caja al instante

                fetch('../api/mensajes_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mensaje: texto })
                })
                .then(res => res.json())
                .then(resData => {
                    if (resData.status === 'success') {
                        cargarLosMensajesDelSala(); // Refresca rápido al enviar
                    }
                });
            });
        }

        // Limpieza de caracteres para evitar inyecciones de código
        function escapeHTML(str) {
            return str.replace(/[&<>'"]/g, 
                tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
            );
        }

        // BUCLE EN TIEMPO REAL: Consulta la base de datos automáticamente cada 2 segundos
        setInterval(cargarLosMensajesDelSala, 2000);
        cargarLosMensajesDelSala(); // Carga inicial al abrir la página

        
        //  WIDGET DE ACCESIBILIDAD UNIVERSAL ADAPTATIVO
        
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

        // Funcionalidad 2: Alto Contraste Seguro sin pisar las variables del Interval
        document.getElementById('btn-toggle-contraste').addEventListener('click', () => {
            document.body.classList.toggle('alto-contraste-activo');
        });

        // Funcionalidad 3: Lógica de Tipografía Verdana masiva para baja visión
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
    </script>
</body>
</html>
