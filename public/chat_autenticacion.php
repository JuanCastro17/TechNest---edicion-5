<?php
// public/chat_autenticacion.php - PARTE 1 DE 2
session_start();

// Conexión segura a la arquitectura por capas saliendo un nivel a config/
include(__DIR__ . '/../config/conexion.php');

$error = "";

// Si el usuario ya está logueado en la tienda principal, rellenamos su campo automáticamente
$usuario_precargado = isset($_SESSION['tienda_user']) ? $_SESSION['tienda_user'] : "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['validar_soporte'])) {
    $usuario_ingresado = mysqli_real_escape_string($conexion, trim($_POST['usuario_chat']));
    $password_ingresada = $_POST['password_chat'];

    // Validamos que exista el usuario y que pertenezca al rol 'usuario' público
    $query = "SELECT * FROM usuarios WHERE (usuario = '$usuario_ingresado' OR correo = '$usuario_ingresado') AND rol = 'usuario' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $usuario_db = mysqli_fetch_assoc($resultado);
        
        if (password_verify($password_ingresada, $usuario_db['password'])) {
            // Guardamos las credenciales temporales del chat en la sesión
            $_SESSION['id_usuario'] = $usuario_db['id_usuario'];
            $_SESSION['tienda_user'] = $usuario_db['usuario'];
            
            // Lo redirigimos a la pantalla final exclusiva del chat
            header("Location: chat_sala.php");
            exit();
        } else {
            $error = "<div class='alert alert-danger text-center small fw-bold' style='border-radius:12px; background:#fbebeb; color:#ff3b30; padding:12px; margin-bottom:20px;'>Contraseña incorrecta.</div>";
        }
    } else {
        $error = "<div class='alert alert-danger text-center small fw-bold' style='border-radius:12px; background:#fbebeb; color:#ff3b30; padding:12px; margin-bottom:20px;'>El usuario no existe o no está registrado.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validación de Soporte | Chat-TechNest</title>
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            background-color: #ffffff; 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
        }

        /* BARRA SUPERIOR INSTITUCIONAL TECHNEST */
        .chat-header {
            width: 100%;
            background-color: #2d6f73;
            display: flex;
            align-items: center;
            padding: 15px 30px;
            gap: 15px;
            box-sizing: border-box;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .chat-header img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .chat-header h1 {
            color: white;
            font-size: 22px;
            margin: 0;
            font-weight: bold;
        }

        /* BANNER DE HORARIO (Centrado sobre el fondo blanco) */
        .horario-banner { 
            background-color: #ffffff; 
            color: #333333; 
            padding: 15px 20px; 
            text-align: center; 
            font-size: 13px; 
            font-weight: bold; 
            line-height: 1.5;
            max-width: 500px;
            margin: 20px auto 10px auto;
        }

        /* CONTENEDOR ENVOLVENTE DEL FORMULARIO */
        .wrapper-centro { 
            max-width: 500px; 
            margin: 0 auto; 
            padding: 0 20px 40px 20px; 
        }

        /* ESTRUCTURA DEL FORMULARIO */
        .card-autenticacion { 
            background: #ffffff; 
            text-align: left; 
        }

        .mb-4-custom {
            margin-bottom: 24px;
        }

        /* LABELS CON EL INDICADOR DE LÍNEA NARANJA DE LA REFERENCIA */
        .form-label-custom {
            display: block;
            font-size: 15px;
            color: #333333;
            margin-bottom: 8px;
            position: relative;
            padding-left: 10px;
            font-weight: normal;
        }
        .form-label-custom::before {
            content: "";
            position: absolute;
            left: 0;
            top: 3px;
            width: 3px;
            height: 16px;
            background-color: #e07212; /* Borde naranja de campos obligatorios */
        }

        /* INPUTS CON CURVATURA IDÉNTICA A LA IMAGEN */
        .form-control-custom { 
            width: 100%;
            padding: 12px 14px; 
            border: 1px solid #555555; 
            border-radius: 12px; 
            font-size: 15px; 
            color: #333333;
            box-sizing: border-box;
            outline: none;
            background-color: #ffffff;
            transition: border-color 0.2s;
        }
        .form-control-custom:focus { 
            border-color: #f2c300; 
            box-shadow: none;
        }

        /* AVISO LEGAL INFERIOR */
        .aviso-calidad {
            font-size: 13px;
            color: #333333;
            margin-top: 25px;
            margin-bottom: 25px;
            text-align: left;
        }

        /* BOTÓN SIGUIENTE VERDE DE LA REFERENCIA */
        .btn-green-submit { 
            display: block;
            width: auto;
            min-width: 140px;
            background-color: #f2c300; 
            color: white; 
            border: none; 
            padding: 8px 30px; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 16px; 
            cursor: pointer; 
            transition: background 0.2s, transform 0.1s; 
            margin: 0 auto; 
            text-align: center;
        }
        .btn-green-submit:hover { 
            background-color: #c59e00; 
        }
        .btn-green-submit:active {
            transform: scale(0.98);
        }

        
        /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON AUTENTICACIÓN */
        
        /* Contraste Seguro: Modulación de fondos oscuros sin parches */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        body.alto-contraste-activo main,
        body.alto-contraste-activo header,
        body.alto-contraste-activo .horario-banner,
        body.alto-contraste-activo .wrapper-centro,
        body.alto-contraste-activo .card-autenticacion {
            background-color: #141414 !important;
            background: #141414 !important;
        }

        /* Forzamos a las cajas de texto a volverse grafito con letras en blanco nítido */
        body.alto-contraste-activo .form-control-custom {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border-color: #555555 !important;
        }

        /* Saneamiento Absoluto de Letras e Indicadores */
        body.alto-contraste-activo h1,
        body.alto-contraste-activo h2,
        body.alto-contraste-activo p,
        body.alto-contraste-activo span,
        body.alto-contraste-activo label,
        body.alto-contraste-activo div,
        body.alto-contraste-activo .aviso-calidad,
        body.alto-contraste-activo .form-label-custom {
            color: #ffffff !important;
        }

        /* Evita que los botones del widget se vuelvan invisibles en modo oscuro */
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

    <!-- BARRA SUPERIOR INTEGRADA CON TU LOGO E IDENTIDAD -->
    <header class="chat-header">
        <img src="assets/logo.png" alt="logo">
        <h1>Chat-TechNest</h1>
    </header>

    <!-- HORARIO DE ATENCIÓN DE LA IMAGEN -->
    <div class="horario-banner">
        Recuerda que nuestro horario de atención al ciudadano es de lunes a viernes<br>
        de 7:00 am a 7:00 pm y sábados de 8:00 am a 1:00 pm
    </div>

    <!-- CUERPO DEL FORMULARIO DE ACCESO -->
    <main class="wrapper-centro">
        <div class="card-autenticacion">
            
            <?php echo $error; ?>

            <form method="POST" action="">
                <!-- Campo: Usuario / Correo -->
                <div class="mb-4-custom">
                    <label class="form-label-custom">Usuario o Correo:</label>
                    <input type="text" name="usuario_chat" class="form-control-custom" value="<?php echo htmlspecialchars($usuario_precargado); ?>" required autocomplete="off">
                </div>
                
                <!-- Campo: Contraseña -->
                <div class="mb-4-custom">
                    <label class="form-label-custom">Contraseña:</label>
                    <input type="password" name="password_chat" class="form-control-custom" required>
                </div>

                <!-- Aviso de Calidad -->
                <div class="aviso-calidad">
                    Para efectos de calidad, tu interacción puede ser grabada o monitoreada
                </div>

                <!-- Botón de Envío Verde -->
                <button type="submit" name="validar_soporte" class="btn-green-submit">Siguiente</button>
            </form>
        </div>
    </main>

    <!-- SCRIPTS CENTRALES DE CONTROL DE PRECIOS Y ACCESIBILIDAD UNIVERSAL -->
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
