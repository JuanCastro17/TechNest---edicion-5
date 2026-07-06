<?php
// public/login_admin.php - PARTE 1 DE 2
session_start();

// CORRECCIÓN CAPAS: Como el archivo está en public/, salimos un nivel para buscar config/
include(__DIR__ . '/../config/conexion.php'); 

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// Si ya inició sesión como admin, mandarlo directo al panel unificado
if (isset($_SESSION['admin_user'])) {
    header("Location: admin.php");
    exit();
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpiamos los datos recibidos corporativos
    $email_ingresado = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $password_ingresada = $_POST['password'];

    // Consultamos si existe un usuario con ese correo Y que su rol sea estrictamente 'admin'
    $query = "SELECT * FROM usuarios WHERE correo = '$email_ingresado' AND rol = 'admin' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        $admin_db = mysqli_fetch_assoc($resultado);
        // Verificamos si encontramos el registro y si la contraseña coincide usando password_verify
        if ($admin_db && password_verify($password_ingresada, $admin_db['password'])) {
            // Creamos las variables de sesión usando los datos reales de la BD
            $_SESSION['id_admin'] = $admin_db['id_usuario'];
            $_SESSION['admin_user'] = $admin_db['usuario']; 
            $_SESSION['admin_avatar'] = !empty($admin_db['avatar']) ? $admin_db['avatar'] : "perro-admin.png";
            $_SESSION['admin_rol'] = $admin_db['rol'];
            
            // Redirección interna hacia el backend administrativo
            header("Location: admin.php");
            exit();
        } else {
            $error_msg = "<div style='background: #f8d7da; color: #721c24; padding: 12px; border-radius: 25px; font-size: 14px; text-align: center; margin-bottom: 15px; font-weight: bold; border: 1px solid #f5c6cb;'>E-mail, contraseña incorrectos o no tienes rango Admin.</div>";
        }
    } else {
        $error_msg = "<div style='background: #f8d7da; color: #721c24; padding: 12px; border-radius: 25px; font-size: 14px; text-align: center; margin-bottom: 15px; font-weight: bold; border: 1px solid #f5c6cb;'>Error de conexión con el servidor.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>Ingresa tu e-mail de administrador | TechNest</title>
    <!-- CORRECCIÓN ASSETS: Eliminamos rutas rotas apuntando directo a carpetas locales relativas -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body { background: #f2f5f6; margin: 0; padding: 0; font-family: sans-serif; }
    .login-header { width: 100%; background: #2d6f73; display: flex; align-items: center; padding: 15px 30px; gap: 15px; box-sizing: border-box; }
    .login-header img { width: 45px; height: 45px; object-fit: contain; }
    .login-header h1 { color: white; font-size: 24px; margin: 0; font-weight: bold; }
    .main-container { display: flex; justify-content: center; align-items: center; gap: 60px; max-width: 1000px; margin: 60px auto; padding: 0 20px; }
    .left-side { flex: 1.2; max-width: 550px; text-align: left; }
    .left-side h2 { font-size: 32px; color: #111; margin-bottom: 20px; line-height: 1.3; font-weight: bold; }
    .left-side img { width: 100%; max-width: 390px; height: auto; object-fit: contain; margin: 20px 0; }
    .right-side { flex: 1; max-width: 420px; width: 100%; }
    .card-form { background: white; border: 1px solid #ccc; border-radius: 15px; padding: 40px 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .input-box { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
    .input-box label { font-weight: bold; color: #333; font-size: 15px; text-align: left; }
    .input-box input { width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 25px; font-size: 15px; outline: none; box-sizing: border-box; background-color: #ffffff; color: #333; }
    .input-box input:focus { border-color: #2d6f73; }
    .btn-yellow { width: 100%; background: #f2c300; border: none; padding: 12px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; margin-top: 10px; }
    .btn-yellow:hover { transform: scale(1.02); }
    .links-section { text-align: center; margin-top: 15px; }
    .links-section a { color: #333; font-size: 13px; font-weight: bold; text-decoration: none; }
    .divider { text-align: center; margin: 20px 0; font-weight: bold; font-size: 14px; position: relative; color: #777; }
    .social-btn { width: 100%; background: white; border: 1px solid #ccc; padding: 10px; border-radius: 25px; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: bold; font-size: 14px; cursor: pointer; margin-bottom: 12px; box-sizing: border-box; color: #333; }
    .social-btn img { width: 18px; height: 18px; }
    .help-link { color: #2d6f73; text-decoration: none; font-weight: bold; font-size: 15px; display: inline-block; margin-top: 20px; }
    @media(max-width: 800px) { .main-container { flex-direction: column; gap: 30px; text-align: center; } .left-side { text-align: center; } .input-box label { text-align: left; } }

   
    /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON LOGIN ADMIN */
    
    /* Contraste Seguro: Modulación de capas oscuras respetando la grilla responsiva */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo .main-container,
    body.alto-contraste-activo .left-side {
        background-color: #141414 !important;
    }

    /* Forzamos la tarjeta de control, botones de redes y entradas a un tono grafito nítido */
    body.alto-contraste-activo .card-form,
    body.alto-contraste-activo .social-btn,
    body.alto-contraste-activo .input-box input {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #555555 !important;
        color: #ffffff !important;
    }

    /* Saneamiento de marcadores de posición (*placeholders*) internos */
    body.alto-contraste-activo .input-box input::placeholder {
        color: #b3b3b3 !important;
        opacity: 1 !important;
    }

    /* Forzado estricto de textos para evitar el desvanecimiento negro */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo h2,
    body.alto-contraste-activo p,
    body.alto-contraste-activo span,
    body.alto-contraste-activo label,
    body.alto-contraste-activo a,
    body.alto-contraste-activo div {
        color: #ffffff !important;
    }

    /* Mantenemos el panel flotante del Widget siempre visible con máxima claridad */
    body.alto-contraste-activo #panel-accesibilidad-global,
    body.alto-contraste-activo #panel-accesibilidad-global * {
        background-color: #ffffff !important;
        color: #111111 !important;
        border-color: #2d6f73 !important;
    }

    /* Tipografía Universal: Forzado de la fuente Verdana de baja visión en todo el login */
    body.fuente-accesible-activa,
    body.fuente-accesible-activa * {
        font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
    }
    </style>
</head>
<body>
    <header class="login-header">
        <!-- CORRECCIÓN ASSETS: Logo llamando directo desde la raíz de navegación virtual -->
        <img src="assets/logo.png" alt="logo">
        <h1>TechNest</h1>
    </header>

    <main class="main-container">
        <!-- LADO IZQUIERDO: ILUSTRACIÓN DE CONTROLADORES -->
        <section class="left-side">
            <h2>Ingresa tu e-mail de administrador</h2>
            <!-- CORRECCIÓN ASSETS: Imagen apuntando directo a assets/ de la raíz virtual -->
            <img src="assets/servidores.png" alt="Servidores Administrativos">
            <a href="ayuda.php" class="help-link">¿Necesitas ayuda?</a>
        </section>

        <!-- LADO DERECHO: FORMULARIO CORPORATIVO DE AUTENTICACIÓN -->
        <section class="right-side">
            <div class="card-form">
                <!-- Renderizado de las alertas de credenciales incorrectas en pantalla -->
                <?php echo $error_msg; ?>
                
                <form method="POST" action="">
                    <div class="input-box">
                        <label>E-mail</label>
                        <input type="email" name="email" required placeholder="admin@technest.com" autocomplete="off">
                    </div>
                    <div class="input-box">
                        <label>Contraseña</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-yellow">INICIAR SESIÓN</button>
                    
                    <div class="links-section">
                        <a href="recuperar.php">¿Has olvidado la contraseña?</a>
                    </div>
                    
                    <div class="divider">o</div>
                    
                    <div class="social-btn">
                        <img src="assets/google-icon.png" alt="G"> Iniciar sesión con Google
                    </div>
                    <div class="social-btn">
                        <img src="assets/facebook-icon.png" alt="F"> Iniciar sesión con Facebook
                    </div>
                </form>
            </div>
        </section>
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
