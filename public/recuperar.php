<?php
// public/recuperar.php - PARTE 1 DE 2
session_start();

// CORRECCIÓN: Como tu archivo está guardado en public/, salimos con /../ para ir a la carpeta config/
include(__DIR__ . '/../config/conexion.php');

// Usamos tu variable de estado original
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $nueva_password = $_POST['password'];

    // Verificar si el correo existe en la base de datos
    $check_query = "SELECT id_usuario FROM usuarios WHERE correo = '$correo' LIMIT 1";
    $check_result = mysqli_query($conexion, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        if (strlen(trim($nueva_password)) < 6) {
            $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>La nueva contraseña debe tener mínimo 6 caracteres.</div>";
        } else {
            // Encriptar la clave de forma segura con BCRYPT
            $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);
            $update_query = "UPDATE usuarios SET password = '$password_hash' WHERE correo = '$correo'";
            
            if (mysqli_query($conexion, $update_query)) {
                $mensaje = "<div class='alert alert-success text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>¡Contraseña actualizada! Ya puedes <a href='login_usuario.php' class='fw-bold text-dark' style='text-decoration:underline;'>Iniciar Sesión</a>.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>Error en el servidor al actualizar la clave.</div>";
            }
        }
    } else {
        $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>El correo electrónico no se encuentra registrado.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña | TechNest</title>
    <!-- CORRECCIÓN: Apuntamos el ícono a la subcarpeta unificada assets/ -->
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #d3e9f1; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: sans-serif; }
        .card-recuperar { background: white; border: 1px solid #ccc; border-radius: 15px; width: 400px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .btn-yellow { background: #f2c300; border: none; font-weight: bold; border-radius: 25px; color: #111; transition: 0.2s; }
        .btn-yellow:hover { transform: scale(1.02); background: #dbb000; }
        .form-control { border-radius: 25px; padding: 10px 15px; }

        
        /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON RESTABLECER CLAVE */
        
        /* Contraste Seguro: Modulación de capas oscuras sin parches */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        
        /* Forzamos la tarjeta de recuperación y las cajas de texto a un tono grafito nítido */
        body.alto-contraste-activo .card-recuperar,
        body.alto-contraste-activo .form-control {
            background-color: #2d2d2d !important;
            background: #2d2d2d !important;
            border-color: #555555 !important;
            color: #ffffff !important;
        }

        /* Saneamiento de placeholders e inputs de texto */
        body.alto-contraste-activo .form-control::placeholder {
            color: #b3b3b3 !important;
            opacity: 1 !important;
        }

        /* Protegemos los enlaces de las alertas de éxito/error para que no queden negros */
        body.alto-contraste-activo .alert a {
            color: #f2c300 !important;
        }

        /* Forzado estricto de letras para evitar textos oscuros */
        body.alto-contraste-activo h4,
        body.alto-contraste-activo label,
        body.alto-contraste-activo a,
        body.alto-contraste-activo span {
            color: #ffffff !important;
        }

        /* Mantenemos el panel flotante del Widget siempre visible con sus botones legibles */
        body.alto-contraste-activo #panel-accesibilidad-global,
        body.alto-contraste-activo #panel-accesibilidad-global * {
            background-color: #ffffff !important;
            color: #111111 !important;
            border-color: #2d6f73 !important;
        }

        /* Tipografía Universal: Forzado de la fuente Verdana de baja visión en todo el portal */
        body.fuente-accesible-activa,
        body.fuente-accesible-activa * {
            font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
        }
    </style>
</head>
<body>

    <div class="card-recuperar text-center">
        <!-- CORRECCIÓN: La ruta del logo de TechNest se redirige hacia la carpeta assets/ -->
        <img src="assets/logo.png" alt="Logo" style="width: 60px; margin-bottom: 15px; object-fit: contain;">
        <h4 class="fw-bold mb-3" style="color: #2d6f73;">Restablecer Clave</h4>
        
        <!-- Renderizado de las alertas dinámicas en pantalla -->
        <?php echo $mensaje; ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label class="form-label small fw-bold" style="color: #333;">Tu Correo Registrado</label>
                <input type="email" name="correo" class="form-control" required placeholder="ejemplo@correo.com">
            </div>
            <div class="mb-4 text-start">
                <label class="form-label small fw-bold" style="color: #333;">Nueva Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
            </div>
            <!-- Corrección de la clase tipográfica para una altura de botón uniforme en Bootstrap -->
            <button type="submit" class="btn btn-yellow w-100 py-2">ACTUALIZAR CONTRASEÑA</button>
        </form>
        <div class="mt-4">
            <a href="login_usuario.php" style="color: #2d6f73; font-size: 14px; text-decoration: none; font-weight: bold;"> Volver al Login</a>
        </div>
    </div>

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
