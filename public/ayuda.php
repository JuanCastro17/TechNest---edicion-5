<?php
session_start();
$usuario_activo = isset($_SESSION['tienda_user']) ? $_SESSION['tienda_user'] : null;
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Aquí procesarías el envío del formulario de soporte (guardar en BD o enviar por correo)
    $mensaje = "<div class='alert alert-success text-center rounded-pill py-2'>¡Tu solicitud ha sido enviada con éxito! Nos comunicaremos contigo pronto.</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ayuda / PQR | TechNest</title>
    <link rel="icon" type="image/png" href="logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body { background: #f2f5f6; font-family: sans-serif; }
    .help-header { background: #2d6f73; padding: 15px 30px; }
    .card-help { border-radius: 15px; border: 1px solid #ccc; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .btn-yellow { background: #f2c300; border: none; font-weight: bold; border-radius: 25px; color: #111; }
    .btn-yellow:hover { background: #dbb100; transform: scale(1.02); }
    .form-control, .form-select { border-radius: 25px; padding: 10px 15px; }
    .accordion-item { border-radius: 15px !important; overflow: hidden; border: 1px solid #ccc; margin-bottom: 10px; }

    
    /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON BOOTSTRAP 5 */
    
    /* Contraste Seguro: Fuerza fondos oscuros corporativos en el DOM de Bootstrap */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo section,
    body.alto-contraste-activo header {
        background-color: #141414 !important;
    }

    /* Forzamos las tarjetas, acordeones y campos de entrada de Bootstrap a un tono grafito legible */
    body.alto-contraste-activo .card-help,
    body.alto-contraste-activo .accordion-item,
    body.alto-contraste-activo .accordion-button,
    body.alto-contraste-activo .accordion-body,
    body.alto-contraste-activo .form-control,
    body.alto-contraste-activo .form-select {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #555555 !important;
    }

    /* REQUERIMIENTO CUMPLIDO: Forzado estricto de textos de entrada, selecciones y placeholders */
    body.alto-contraste-activo .form-control,
    body.alto-contraste-activo .form-select,
    body.alto-contraste-activo .form-select option {
        color: #ffffff !important;
    }

    /* Escudo para textos de ejemplo (placeholders): Forzamos a que cambien a gris claro nítido */
    body.alto-contraste-activo .form-control::placeholder,
    body.alto-contraste-activo textarea::placeholder {
        color: #b3b3b3 !important;
        opacity: 1 !important;
    }

    /* Saneamiento de letras generales en el Centro de Ayuda */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo h2,
    body.alto-contraste-activo h3,
    body.alto-contraste-activo h4,
    body.alto-contraste-activo p,
    body.alto-contraste-activo span,
    body.alto-contraste-activo label,
    body.alto-contraste-activo a,
    body.alto-contraste-activo button,
    body.alto-contraste-activo div {
        color: #ffffff !important;
    }

    /* Mantenemos el panel flotante del Widget siempre visible en blanco/negro nítido */
    body.alto-contraste-activo #panel-accesibilidad-global,
    body.alto-contraste-activo #panel-accesibilidad-global * {
        background-color: #ffffff !important;
        color: #111111 !important;
        border-color: #2d6f73 !important;
    }

    /* Tipografía Universal: Forzado de la fuente Verdana de baja visión en todo el centro de ayuda */
    body.fuente-accesible-activa,
    body.fuente-accesible-activa * {
        font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
    }
    </style>
</head>
<body>

    <header class="help-header d-flex justify-content-between align-items-center text-white">
        <div class="d-flex align-items-center gap-3">
            <img src="assets/logo.png" alt="logo" style="width: 50px; height: 50px; object-fit: contain;">
            <h1 class="m-0 h4">TechNest - Centro de Ayuda</h1>
        </div>
        <a href="index.php" class="btn btn-outline-light rounded-pill btn-sm px-4 fw-bold text-decoration-none">Volver al Inicio</a>
    </header>

    <main class="container my-5">
        <div class="row g-5">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-4" style="color: #2d6f73;">Preguntas Frecuentes</h3>
                <div class="accordion" id="accordionFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                ¿Cómo puedo recuperar mi contraseña?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body text-muted">
                                Puedes hacer clic en "¿Has olvidado la contraseña?" en la pantalla de inicio de sesión e ingresar tu correo electrónico registrado para restablecerla.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                ¿Cuáles son los métodos de pago?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body text-muted">
                                Actualmente aceptamos tarjetas de crédito, débito, transferencias bancarias locales y pagos contra entrega.
                            </div>
                        </div>
                    </div>
                    <div class="card card-help bg-white p-4 mb-4 text-center" style="border: 2px dashed #2d6f73;">
                        <h4 class="fw-bold mb-2" style="color: #2d6f73;">¿Prefieres soporte inmediato?</h4>
                        <p class="text-muted small mb-3">Conéctate de forma independiente con un asesor en nuestro canal directo.</p>
                        <a href="chat_autenticacion.php" class="btn btn-yellow px-4 py-2 text-uppercase fw-bold text-decoration-none d-inline-block">
                             Abrir Chat-TechNest
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-help bg-white p-4">
                    <h3 class="fw-bold text-center mb-3" style="color: #333;">Radicar PQR / Soporte</h3>
                    <?php echo $mensaje; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tipo de Solicitud</label>
                            <select class="form-select" required>
                                <option value="">Selecciona una opción</option>
                                <option value="peticion">Petición</option>
                                <option value="queja">Queja</option>
                                <option value="reclamo">Reclamo</option>
                                <option value="soporte">Soporte Técnico</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Asunto</label>
                            <input type="text" class="form-control" placeholder="Ej: Problema con mi pedido" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Descripción del caso</label>
                            <textarea class="form-control" rows="4" style="border-radius:15px;" placeholder="Detalla lo sucedido..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-yellow w-100 py-2.5 text-uppercase">Enviar Formulario</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
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
                
                // Forzado de texto complementario para los botones del acordeón de Bootstrap que tienen opacidad por CSS externo
                document.querySelectorAll('.accordion-button').forEach(btn => {
                    if (document.body.classList.contains('alto-contraste-activo')) {
                        btn.style.setProperty('color', '#ffffff', 'important');
                    } else {
                        btn.style.removeProperty('color');
                    }
                });
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>