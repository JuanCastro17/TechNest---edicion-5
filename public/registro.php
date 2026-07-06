<?php
session_start();

// CORRECCIÓN: Como tu archivo está guardado en public/, salimos con /../ para ir a la carpeta config/
include(__DIR__ . '/../config/conexion.php'); 

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $apellido = mysqli_real_escape_string($conexion, trim($_POST['apellido']));
    $correo = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $usuario = mysqli_real_escape_string($conexion, trim($_POST['usuario']));
    $password = $_POST['password'];
    
    // 1. FILTRO DE SEGURIDAD: Validar longitud mínima de la contraseña
    if (strlen(trim($password)) < 6) {
        $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>La contraseña debe tener al menos 6 caracteres reales.</div>";
    } 
    // 2. FILTRO DE SEGURIDAD: Evitar caracteres extraños o scripts en el nombre de usuario
    elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)) {
        $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>El nombre de usuario contiene caracteres no permitidos.</div>";
    }
    else {
        // Ciframos la contraseña de forma segura con BCRYPT
        $password_encriptada = password_hash($password, PASSWORD_BCRYPT);
        $rol = 'usuario';

        // Verificar duplicados usando mysqli
        $buscar_query = "SELECT id_usuario FROM usuarios WHERE usuario = '$usuario' OR correo = '$correo' LIMIT 1";
        $buscar_resultado = mysqli_query($conexion, $buscar_query);
        
        if (mysqli_num_rows($buscar_resultado) > 0) {
            $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>El usuario o el correo ya están registrados.</div>";
        } else {
            // Insertar datos reales en el sistema
            $insertar_query = "INSERT INTO usuarios (nombre, apellido, correo, usuario, password, rol) VALUES ('$nombre', '$apellido', '$correo', '$usuario', '$password_encriptada', '$rol')";
            
            if (mysqli_query($conexion, $insertar_query)) {
                $mensaje = "<div class='alert alert-success text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>¡Registro exitoso! Ya puedes <a href='login_usuario.php' class='fw-bold text-dark' style='text-decoration:underline;'>Volver al Login</a>.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger text-center rounded-pill py-2' style='font-size:14px; font-weight:bold;'>Error al registrar usuario en el sistema.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crea tu cuenta | TechNest</title>
    <!-- CORRECCIÓN: Quitamos el prefijo 'public/' de tus archivos estáticos y apuntamos a assets/ -->
    <link rel="icon" type="image/png" href="assets/logo icono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f2f5f6; font-family: sans-serif; }
        .login-header { background: #2d6f73; padding: 15px 30px; gap: 15px; }
        .login-header img { width: 45px; height: 45px; object-fit: contain; }
        .btn-yellow { background: #f2c300; border: none; font-weight: bold; border-radius: 25px; transition: 0.2s; }
        .btn-yellow:hover { transform: scale(1.02); background: #dbb100; }
        .form-control { border-radius: 25px; padding: 10px 15px; }
        .form-control:focus { border-color: #2d6f73; box-shadow: 0 0 0 0.25rem rgba(45, 111, 115, 0.25); }
        .card-form { border-radius: 15px; border: 1px solid #ccc; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }

        
        /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON EL REGISTRO GLOBAL */
        
        /* Contraste Seguro: Modulación de fondos oscuros respetando la cuadrícula de Bootstrap */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        body.alto-contraste-activo main,
        body.alto-contraste-activo header,
        body.alto-contraste-activo section {
            background-color: #141414 !important;
        }

        /* Forzamos la tarjeta de registro y las cajas de texto a un tono grafito nítido */
        body.alto-contraste-activo .card-form,
        body.alto-contraste-activo .form-control {
            background-color: #2d2d2d !important;
            background: #2d2d2d !important;
            border-color: #555555 !important;
            color: #ffffff !important;
        }

        /* Saneamiento de textos de ejemplo (placeholders) internos para evitar opacidad */
        body.alto-contraste-activo .form-control::placeholder {
            color: #b3b3b3 !important;
            opacity: 1 !important;
        }

        /* Protegemos los hipervínculos de redirección en las alertas de éxito de PHP */
        body.alto-contraste-activo .alert a {
            color: #f2c300 !important;
        }

        /* Forzado estricto de textos para que las etiquetas no queden oscuras */
        body.alto-contraste-activo h1,
        body.alto-contraste-activo h2,
        body.alto-contraste-activo label,
        body.alto-contraste-activo a,
        body.alto-contraste-activo span {
            color: #ffffff !important;
        }

        /* Mantenemos el panel flotante del Widget siempre visible con máxima nitidez */
        body.alto-contraste-activo #panel-accesibilidad-global,
        body.alto-contraste-activo #panel-accesibilidad-global * {
            background-color: #ffffff !important;
            color: #111111 !important;
            border-color: #2d6f73 !important;
        }

        /* Tipografía Universal: Forzado de la fuente Verdana de baja visión en todo el registro */
        body.fuente-accesible-activa,
        body.fuente-accesible-activa * {
            font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
        }
    </style>
</head>
<body>

    <header class="login-header d-flex align-items-center w-100">
        <!-- CORRECCIÓN: La ruta del logo de TechNest se redirige hacia la carpeta assets/ -->
        <img src="assets/logo.png" alt="logo">
        <h1 class="text-white m-0 h3">TechNest</h1>
    </header>

    <main class="container my-5">
        <div class="row align-items-center justify-content-center g-5">
            <!-- Lado Izquierdo: Ilustración -->
            <section class="col-lg-6 col-md-5 text-start">
                <h2 class="fw-bold mb-4" style="font-size: 32px; color: #111;">Crea tu cuenta en la plataforma</h2>
                <!-- CORRECCIÓN: Apuntamos la imagen de la barra lateral directo a assets/ -->
                <img src="assets/dispositivos.png" alt="TechNest Items" class="img-fluid my-3" style="max-height: 400px; object-fit: contain;">
                <br>
                <a href="login_usuario.php" class="text-decoration-none fw-bold" style="color: #2d6f73;">Volver al Login</a>
            </section>

            <!-- Lado Derecho: Tarjeta Formulario -->
            <section class="col-lg-5 col-md-7">
                <div class="card card-form bg-white p-4 p-sm-5">
                    
                    <!-- Renderizado de las alertas dinámicas en pantalla -->
                    <?php echo $mensaje; ?>

                    <form method="POST" action="">
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6 text-start">
                                <label class="form-label fw-bold text-dark small">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-sm-6 text-start">
                                <label class="form-label fw-bold text-dark small">Apellido</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold text-dark small">Correo electrónico</label>
                            <input type="email" name="correo" class="form-control" placeholder="usuario@gmail.com" required>
                        </div>

                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold text-dark small">Nombre de Usuario</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Ej: Leo596" required>
                        </div>

                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold text-dark small">Contraseña</label>
                            <input type="password" name="password" class="form-control" minlength="6" placeholder="Mínimo 6 caracteres" required>
                        </div>

                        <button type="submit" class="btn btn-yellow w-100 py-2 text-uppercase">Registrarse</button>
                    </form>
                </div>
            </section>
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

