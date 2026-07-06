<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Acceso a la Plataforma | TechNest</title>
        <link rel="stylesheet" href="css/admin.css">
        <link rel="icon" type="image/png" href="assets/logoicono.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            .select-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background: #f2f5f6;
                padding: 20px;
            }
            .select-box {
                background: rgba(77, 125, 128, 0.94);
                border-radius: 12px;
                padding: 40px 30px;
                border: 8px solid #2d6f73;
                width: 100%;
                max-width: 420px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                text-align: center;
            }
            .select-box h2 {
                color: #ffffff;
                font-size: 30px;
                margin-bottom: 10px;
            }
            .select-box p {
                color: #fbeeee;
                font-size: 16px;
                margin-bottom: 30px;
                font-weight: 600;
            }
            .btn-choice {
                display: block;
                width: 100%;
                padding: 14px;
                margin-bottom: 15px;
                border-radius: 10px;
                font-size: 17px;
                font-weight: bold;
                text-decoration: none;
                text-align: center;
                transition: 0.2s;
                box-sizing: border-box;
            }
            .btn-user-route {
                background: white;
                color: #2d6f73;
            }
            .btn-user-route:hover {
                background: #eef5f6;
                transform: scale(1.02);
            }
            .btn-admin-route {
                background: #f2c300;
                color: black;
                border: none;
            }
            .btn-admin-route:hover {
                transform: scale(1.02);
            }

            
            /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON LOGIN SELECTOR */
           
            /* Contraste Seguro: Modulación de la caja de roles sin parches */
            body.alto-contraste-activo {
                background-color: #141414 !important;
                color: #ffffff !important;
            }
            body.alto-contraste-activo main,
            body.alto-contraste-activo .select-wrapper {
                background-color: #141414 !important;
            }
            body.alto-contraste-activo .select-box {
                background-color: #1e1e1e !important;
                background: #1e1e1e !important;
                border-color: #444444 !important;
                box-shadow: 0 4px 15px rgba(255, 255, 255, 0.05) !important;
            }
            
            /* Forzado simétrico de los botones para mantener el contraste del texto */
            body.alto-contraste-activo .btn-choice {
                background-color: #2d2d2d !important;
                color: #ffffff !important;
                border: 2px solid #555555 !important;
            }
            
            /* Saneamiento Absoluto de Textos */
            body.alto-contraste-activo h2,
            body.alto-contraste-activo p {
                color: #ffffff !important;
            }

            /* Widget flotante siempre legible en blanco y negro nítido */
            body.alto-contraste-activo #panel-accesibilidad-global,
            body.alto-contraste-activo #panel-accesibilidad-global * {
                background-color: #ffffff !important;
                color: #111111 !important;
                border-color: #2d6f73 !important;
            }

            /* Tipografía Universal: Forzado de Verdana para baja visión */
            body.fuente-accesible-activa,
            body.fuente-accesible-activa * {
                font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
            }
        </style>
    </head>
    <body>
        <main class="select-wrapper">
            <div class="select-box">
                <h2>TechNest</h2>
                <p>¿Deseas iniciar sesión como usuario o como admin?</p>
                <!-- Redirección al login de cliente -->
                <a href="login_usuario.php" class="btn-choice btn-user-route">Iniciar como Usuario</a>
                <!-- Redirección al login de administración corporativa -->
                <a href="login_admin.php" class="btn-choice btn-admin-route">Iniciar como Administrador</a>
            </div>
        </main>
        <!-- SCRIPTS CENTRALES DE CONTROL Y ACCESIBILIDAD UNIVERSAL -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // ==========================================================================
                // ♿ SUBSISTEMA DE ACCESIBILIDAD INTEGRADO AL DOM CENTRAL
                // ==========================================================================
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
