<?php
// public/login_vendedor.php - PARTE 1 DE 2
session_start();

// Sincronización con la base de datos corporativa saliendo un nivel a config/
include(__DIR__ . '/../config/conexion.php'); 

if (isset($_SESSION['vendedor_user'])) {
    header("Location: vendedor.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = mysqli_real_escape_string($conexion, trim($_POST['usuario_vendedor']));
    $password_ingresada = $_POST['password_vendedor'];

    // PANEL VENTAS: El filtro SQL restringe estrictamente el acceso solo a cuentas con rol = 'vendedor'
    $query = "SELECT * FROM usuarios WHERE (usuario = '$usuario_ingresado' 
              OR correo = '$usuario_ingresado') AND rol = 'vendedor' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        $vendedor_db = mysqli_fetch_assoc($resultado);
        // Validación con hashing seguro
        if ($vendedor_db && password_verify($password_ingresada, $vendedor_db['password'])) {
            $_SESSION['id_vendedor'] = $vendedor_db['id_usuario'];
            $_SESSION['vendedor_user'] = $vendedor_db['usuario'];
            $_SESSION['vendedor_nombre'] = $vendedor_db['nombre'] . " " . $vendedor_db['apellido'];
            
            // Redirección directa a su panel de gestión manual de ventas
            header("Location: vendedor.php"); 
            exit();
        } else {
            $error = "<div class='alert alert-danger text-center py-1 small' style='border-radius:25px; background:#fbebeb; color:#ff3b30; padding:10px; margin-bottom:15px; font-weight:bold;'>Acceso denegado. Credenciales incorrectas o no eres un vendedor autorizado.</div>";
        }
    } else {
        $error = "<div class='alert alert-danger text-center py-1 small' style='border-radius:25px; background:#fbebeb; color:#ff3b30; padding:10px; margin-bottom:15px; font-weight:bold;'>Error de comunicación con los servicios internos.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Acceso Personal de Ventas | TechNest</title>
        <link rel="stylesheet" href="css/admin.css">
        <link rel="icon" type="image/png" href="assets/logoicono.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
        body { background: #eef2f3; margin: 0; padding: 0; font-family: sans-serif; }
        .vendedor-header { width: 100%; background: #2d6f73; display: flex; align-items: center; padding: 15px 30px; gap: 15px; box-sizing: border-box; }
        .vendedor-header img { width: 45px; height: 45px; object-fit: contain; }
        .vendedor-header h1 { color: white; font-size: 24px; margin: 0; }
        .main-container { display: flex; justify-content: center; align-items: center; gap: 60px; max-width: 1000px; margin: 50px auto; padding: 0 20px; }
        .left-side { flex: 1.2; max-width: 550px; text-align: left; }
        .left-side h2 { font-size: 32px; color: #2d6f73; margin-bottom: 20px; line-height: 1.3; font-weight: bold; }
        .left-side p { color: #555; font-size: 16px; line-height: 1.6; }
        .right-side { flex: 1; max-width: 420px; width: 100%; }
        .card-form { background: white; border: 2px solid #979797; border-radius: 15px; padding: 40px 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .input-box { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
        .input-box label { font-weight: bold; color: #333; font-size: 15px; text-align: left; }
        .input-box input { width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 25px; font-size: 15px; outline: none; box-sizing: border-box; background-color: #ffffff; color: #333; }
        .input-box input:focus { border-color: #2d6f73; }
        .btn-vendedor { width: 100%; background: #f2c300; color: black; border: none; padding: 12px; border-radius: 25px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; margin-top: 10px; }
        .btn-vendedor:hover { background: #c59e00; transform: scale(1.02); }
        .back-link { display: inline-block; margin-top: 20px; color: #333; text-decoration: none; font-weight: bold; font-size: 14px; }
        @media(max-width: 800px) { .main-container { flex-direction: column; gap: 30px; text-align: center; } .left-side { text-align: center; } .input-box label { text-align: left; } }

        
        /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON LOGIN VENDEDOR */
        
        /* Contraste Seguro: Modulación de fondos oscuros manteniendo floats limpios */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        body.alto-contraste-activo main,
        body.alto-contraste-activo .main-container,
        body.alto-contraste-activo .left-side {
            background-color: #141414 !important;
        }

        /* Forzamos la tarjeta corporativa y los inputs internos a volverse grafito legible */
        body.alto-contraste-activo .card-form,
        body.alto-contraste-activo .input-box input {
            background-color: #2d2d2d !important;
            background: #2d2d2d !important;
            border-color: #555555 !important;
            color: #ffffff !important;
        }

        /* Saneamiento de textos informativos y marcadores de posición (*placeholders*) */
        body.alto-contraste-activo .input-box input::placeholder {
            color: #b3b3b3 !important;
            opacity: 1 !important;
        }

        /* Forzado estricto de textos para que los títulos y descripciones no queden invisibles */
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

        /* Tipografía Universal: Forzado de la fuente Verdana de baja visión en el módulo */
        body.fuente-accesible-activa,
        body.fuente-accesible-activa * {
            font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
        }
        </style>
    </head>
    <body>
    <header class="vendedor-header">
        <img src="assets/logo.png" alt="logo">
        <h1 style="font-size: 22px; font-weight: bold; margin: 0; color: white;">TechNest Interno</h1>
    </header>
    <main class="main-container">
        <section class="left-side">
            <h2>Módulo de Ventas Corporativas</h2>
            <p>Bienvenido al canal interno de TechNest. Desde este panel podrás registrar transacciones manuales, gestionar la facturación directa de clientes y sincronizar los reportes con la administración central de forma inmediata.</p>
        </section>
        <section class="right-side">
            <div class="card-form">
                <?php echo $error; ?>
                <form method="POST" action="">
                    <div class="input-box">
                        <label>Usuario o Correo Corporativo</label>
                        <input type="text" name="usuario_vendedor" required placeholder="vendedor_id o email">
                    </div>
                    <div class="input-box">
                        <label>Contraseña Operativa</label>
                        <input type="password" name="password_vendedor" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-vendedor">INGRESAR AL PANEL</button>
                </form>
                <div style="text-align: center;">
                    <a href="login_usuario.php" class="back-link"> Volver al acceso general</a>
                </div>
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
