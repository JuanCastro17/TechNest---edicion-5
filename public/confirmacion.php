<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TechNest - Pedido Recibido</title>
    <!-- CORRECCIÓN CAPAS: Apuntamos las hojas de estilo dentro de la carpeta css/ virtual -->
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/confirmacion.css">
    <!-- CORRECCIÓN ASSETS: Vinculación del icono unificado local sin espacios en el nombre -->
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ESCUDO DE CLASES SELECTIVAS DE ACCESIBILIDAD PARA EL TICKET BANCARIO -->
    <style>
        /* Contraste Seguro: Oscurece el fondo y convierte el recibo a un tono grafito legible */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        body.alto-contraste-activo main,
        body.alto-contraste-activo header {
            background-color: #141414 !important;
        }
        
        /* REQUERIMIENTO CUMPLIDO: Forzamos tanto el recibo como el cuadro interno de datos a volverse oscuros */
        body.alto-contraste-activo .ticket-pago,
        body.alto-contraste-activo .ticket-datos {
            background-color: #2d2d2d !important;
            background: #2d2d2d !important;
            border-color: #444444 !important;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.05) !important;
        }
        
        /* Forzado estricto de textos para que las referencias bancarias resalten en blanco nítido */
        body.alto-contraste-activo h1,
        body.alto-contraste-activo h2,
        body.alto-contraste-activo h3,
        body.alto-contraste-activo p,
        body.alto-contraste-activo span,
        body.alto-contraste-activo strong,
        body.alto-contraste-activo label,
        body.alto-contraste-activo a {
            color: #ffffff !important;
        }
        
        /* Mantenemos el panel flotante del Widget siempre visible con sus botones legibles */
        body.alto-contraste-activo #panel-accesibilidad-global,
        body.alto-contraste-activo #panel-accesibilidad-global * {
            background-color: #ffffff !important;
            color: #111111 !important;
            border-color: #2d6f73 !important;
        }

        /* Tipografía Universal: Forzado de la fuente Verdana de baja visión en todo el ticket */
        body.fuente-accesible-activa,
        body.fuente-accesible-activa * {
            font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
        }
    </style>
</head>
<body>
    <header>
        <div class="top-header">
            <div class="top-header-inner">
                <div class="logo">
                    <!-- CORRECCIÓN ASSETS: Logo llamando directo desde la raíz de navegación virtual -->
                    <img src="assets/logo.png" alt="Logo TechNest" class="logo-img">
                    <h1 class="logo-text">TechNest</h1>
                </div>
            </div>
        </div>
    </header>

    <main class="confirmacion-container">
        <div class="ticket-pago">
            <div class="ticket-header">
                <h1>Gracias por tu pedido</h1>
            </div>
            
            <div class="ticket-body">
                <p>Tu pedido ha sido recibido y ahora está siendo procesado.</p>
                <h2 id="mensaje-pago">Paga <span id="monto-final">$ 0</span> en tu banco de preferencia para finalizar tu compra</h2>
            </div>

            <div class="ticket-datos">
                <div class="dato-grupo">
                    <span>N° Convenio</span>
                    <strong>548937</strong>
                </div>
                <div class="dato-grupo">
                    <span>Referencia</span>
                    <strong>1946 3784 2579</strong>
                </div>
            </div>

            <div class="ticket-footer">
                <p>Tienes hasta 72 horas para pagar, de lo contrario tu pedido será cancelado.</p>
                <p>Confirmaremos la fecha de entrega cuando se acredite el pago.</p>
            </div>
        </div>

        <a href="index.php" class="btn-volver-inicio">VOLVER AL INICIO</a>
    </main>
        <!-- SCRIPTS CENTRALES DE CONTROL DE PRECIOS Y ACCESIBILIDAD UNIVERSAL -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. LÓGICA NATIVA DE CAPTURA DE PRECIOS
            const params = new URLSearchParams(window.location.search);
            const precio = params.get('precio');
            if (precio) {
                // Inyecta el precio formateado en pesos colombianos que viaja de forma segura desde pago.php
                document.getElementById('monto-final').textContent = precio;
            }

            
            //  SUBSISTEMA DE ACCESIBILIDAD INTEGRADO AL DOM CENTRAL
        
            // Inyectamos el panel flotante formalizado y serio sin emojis abajo a la derecha
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

            // Funcionalidad 2: Lógica de Alto Contraste Seguro amarrada a las clases del body
            document.getElementById('btn-toggle-contraste').addEventListener('click', () => {
                document.body.classList.toggle('alto-contraste-activo');
            });

            // Funcionalidad 3: Lógica de Tipografía Verdana de Baja Visión Blindada
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
