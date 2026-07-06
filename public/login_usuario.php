<?php
session_start();

// CORRECCIÓN: Como estás en public, salimos un nivel con /../ para ir a la carpeta config/
include(__DIR__ . '/../config/conexion.php'); 

if (isset($_SESSION['tienda_user'])) {
    // CORRECCIÓN: Al estar ya en public/, el index.php está al mismo nivel
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = mysqli_real_escape_string($conexion, trim($_POST['usuario_publico']));
    $password_ingresada = $_POST['password_publica'];

    // Busca por usuario, correo OR celular de forma simultánea para el rol usuario
    $query = "SELECT * FROM usuarios WHERE (usuario = '$usuario_ingresado' 
              OR correo = '$usuario_ingresado' OR celular = '$usuario_ingresado') AND rol = 'usuario' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        $usuario_db = mysqli_fetch_assoc($resultado);
        if ($usuario_db && password_verify($password_ingresada, $usuario_db['password'])) {
            $_SESSION['id_usuario'] = $usuario_db['id_usuario'];
            $_SESSION['tienda_user'] = $usuario_db['usuario'];
            
            // Buscar si tiene foto real
            $foto_usuario = $usuario_db['avatar'];
            if (!empty($foto_usuario) && file_exists("uploads/" . $foto_usuario)) {
                $_SESSION['tienda_avatar'] = "uploads/" . $foto_usuario;
            } else {
                $_SESSION['tienda_avatar'] = "assets/user.ico";
            }
            
            header("Location: index.php"); 
            exit();
        } else {
            $error = "<div class='alert alert-danger text-center py-1 small' style='border-radius:25px; background:#fbebeb; color:#ff3b30; padding:10px; margin-bottom:15px; font-weight:bold;'>Credenciales incorrectas o el usuario no existe.</div>";
        }
    } else {
        $error = "<div class='alert alert-danger text-center py-1 small' style='border-radius:25px; background:#fbebeb; color:#ff3b30; padding:10px; margin-bottom:15px; font-weight:bold;'>Error al consultar la base de datos.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ingresa tu e-mail o teléfono | TechNest</title>
        <link rel="stylesheet" href="css/admin.css">
        <link rel="icon" type="image/png" href="assets/logo icono.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
        body { background: #f2f5f6; margin: 0; padding: 0; font-family: sans-serif; }
        .login-header { width: 100%; background: #2d6f73; display: flex; align-items: center; padding: 15px 30px; gap: 15px; box-sizing: border-box; }
        .login-header img { width: 45px; height: 45px; object-fit: contain; }
        .login-header h1 { color: white; font-size: 24px; margin: 0; }
        .main-container { display: flex; justify-content: center; align-items: center; gap: 60px; max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .left-side { flex: 1.2; max-width: 550px; text-align: left; }
        .left-side h2 { font-size: 32px; color: #111; margin-bottom: 20px; line-height: 1.3; }
        .left-side img { width: 100%; max-height: 380px; height: auto; object-fit: contain; margin: 20px 0; }
        .right-side { flex: 1; max-width: 420px; width: 100%; }
        .card-form { background: white; border: 1px solid #ccc; border-radius: 15px; padding: 40px 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .input-box { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
        .input-box label { font-weight: bold; color: #333; font-size: 15px; text-align: left; }
        .input-box input { width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 25px; font-size: 15px; outline: none; box-sizing: border-box; }
        .input-box input:focus { border-color: #2d6f73; }
        .btn-yellow { width: 100%; background: #f2c300; border: none; padding: 12px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; margin-top: 10px; }
        .btn-yellow:hover { transform: scale(1.02); }
        .links-section { text-align: center; margin-top: 15px; }
        .links-section a { color: #333; font-size: 13px; font-weight: bold; text-decoration: none; }
        .divider { text-align: center; margin: 20px 0; font-weight: bold; font-size: 14px; position: relative; color: #777; }
        .social-btn { width: 100%; background: white; border: 1px solid #ccc; padding: 10px; border-radius: 25px; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: bold; font-size: 14px; cursor: pointer; margin-bottom: 12px; box-sizing: border-box; }
        .social-btn img { width: 18px; height: 18px; }
        .btn-vendedor-link { width: 100%; background: #2d6f73; color: white; border: none; padding: 11px; border-radius: 25px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; cursor: pointer; text-transform: none; text-decoration: none; transition: 0.2s; box-sizing: border-box; margin-bottom: 20px; }
        .btn-vendedor-link:hover { background: #225473; transform: scale(1.02); }
        .help-link { color: #2d6f73; text-decoration: none; font-weight: bold; font-size: 15px; display: inline-block; margin-top: 20px; }
        @media(max-width: 800px) { .main-container { flex-direction: column; gap: 30px; text-align: center; } .left-side { text-align: center; } .input-box label { text-align: left; } }

        
        /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON LOGIN DE USUARIO */
        
        /* Contraste Seguro: Modulación de capas oscuras respetando la grilla dividida */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        body.alto-contraste-activo main,
        body.alto-contraste-activo .main-container,
        body.alto-contraste-activo .left-side {
            background-color: #141414 !important;
        }

        /* Forzamos la tarjeta del login, botones de redes y entradas a un tono grafito nítido */
        body.alto-contraste-activo .card-form,
        body.alto-contraste-activo .social-btn,
        body.alto-contraste-activo .input-box input {
            background-color: #2d2d2d !important;
            background: #2d2d2d !important;
            border-color: #555555 !important;
            color: #ffffff !important;
        }

        /* Saneamiento de textos informativos y placeholders internos */
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

        /* Mantenemos el panel flotante del Widget siempre visible con sus botones legibles */
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
        <!-- HEADER INSTITUCIONAL UNIFICADO -->
        <header class="login-header">
            <img src="assets/logo.png" alt="logo">
            <h1>TechNest</h1>
        </header>

        <!-- CUERPO PRINCIPAL DOS COLUMNAS RESPONSIVO -->
        <main class="main-container">
            <section class="left-side">
                <h2>Ingresa tu e-mail o número de teléfono celular</h2>
                <img src="assets/dispositivos.png" alt="TechNest Items">
                <a href="ayuda.php" class="help-link">¿Necesitas ayuda?</a>
            </section>

            <section class="right-side">
                <div class="card-form">
                    <?php echo $error; ?>
                    <form method="POST" action="">
                        <!-- Campo: Usuario / Teléfono -->
                        <div class="input-box">
                            <label>E-mail o teléfono</label>
                            <input type="text" name="usuario_publico" required placeholder="usuario@gmail.com">
                        </div>
                        
                        <!-- Campo: Contraseña -->
                        <div class="input-box">
                            <label>Contraseña</label>
                            <input type="password" name="password_publica" required placeholder="••••••••">
                        </div>

                        <!-- Botón de Envío -->
                        <button type="submit" class="btn-yellow">INICIAR SESIÓN</button>
                        
                        <div class="links-section">
                            <a href="recuperar.php">¿Has olvidado la contraseña?</a>
                        </div>
                        
                        <div class="divider">o</div>
                        
                        <!-- Botoneras de Franquicia Social -->
                        <div class="social-btn">
                            <img src="assets/google-icon.png" alt="G">
                            Iniciar sesión con Google
                        </div>
                        <div class="social-btn">
                            <img src="assets/facebook-icon.png" alt="F">
                            Iniciar sesión con Facebook
                        </div>
                        
                        <div class="text-center mt-3" style="text-align: center; margin-top: 20px;">
                            <p style="font-size: 14px; color: #333;">
                                ¿No tienes una cuenta? 
                                <a href="registro.php" style="color: #2d6f73; font-weight: bold; text-decoration: none; margin-left: 5px;">Regístrate aquí</a>
                            </p>
                        </div>

                        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 25px 0 20px 0; width: 100%;">
                        
                        <!-- Enlace de Acceso Alternativo de Personal Corporativo -->
                        <div class="divider" style="margin: 15px 0 10px;">¿Eres parte de nuestro equipo?</div>
                        <a href="login_vendedor.php" class="btn-vendedor-link">Acceso Vendedor corporativo</a>
                    </form>
                </div>
            </section>
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

