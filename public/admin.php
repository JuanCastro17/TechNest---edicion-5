<?php
// public/admin.php - PARTE 1 DE 3 (LOGICA BACKEND PHP)
session_start();

// REQUERIMIENTO COMPLETO: Blindaje de seguridad real contra intrusos
if (!isset($_SESSION['admin_user'])) {
    header("Location: login_admin.php");
    exit();
}

// CORRECCIÓN CAPAS: Como el archivo está en public/, subimos un nivel para conectar con config/
include(__DIR__ . "/../config/conexion.php");

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// Inyectamos las credenciales reales del administrador activo
$admin_nombre = $_SESSION['admin_user'];
$admin_avatar_file = $_SESSION['admin_avatar'];

// Evaluamos si el avatar existe físicamente en las cargas, sino dejamos el comodín
$avatar_admin_real = "assets/" . $admin_avatar_file;
if (!file_exists($avatar_admin_real) || empty($admin_avatar_file)) {
    $avatar_admin_real = "assets/perro-admin.png"; 
}

// Consulta SQL dinámica para traer los productos reales del inventario
$query = "SELECT * FROM productos ORDER BY id ASC";
$resultado = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración | TechNest</title>
    <!-- CORRECCIÓN ASSETS: Eliminamos prefijos rotos llamando directo desde la raíz virtual -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    /* Estilos inline para asegurar la adaptabilidad responsiva del tablero administrativo */
    body { background-color: #f4f7f6; font-family: Arial, sans-serif; margin: 0; }
    .top-header { background-color: #2d6f73; padding: 12px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .top-header-inner { display: flex; justify-content: space-between; align-items: center; max-width: 1300px; margin: 0 auto; }
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo img { width: 35px; height: 35px; object-fit: contain; }
    .logo h1 { color: white; margin: 0; font-size: 20px; font-weight: bold; }
    .admin-info { display: flex; align-items: center; gap: 15px; }
    .admin-user { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.15); padding: 6px 14px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.2); }
    .admin-user img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background: white; border: 2px solid white; }
    .admin-user span { color: white; font-size: 14px; font-weight: bold; }
    .vendedor-container { max-width: 1300px; margin: 30px auto; display: flex; gap: 25px; padding: 0 20px; }
    .col-sidebar { flex: 1; min-width: 260px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; height: fit-content; }
    .col-tabla-box { flex: 3; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
    .nav-menu-link { display: flex; align-items: center; gap: 12px; padding: 12px 15px; text-decoration: none; color: #444; font-size: 14px; font-weight: 500; border-radius: 8px; margin-bottom: 5px; transition: 0.15s; }
    .nav-menu-link:hover { background: #f2f5f6; color: #2d6f73; }
    .nav-menu-active { background: #e8f0fe; color: #2d6f73; font-weight: bold; }
    .nav-menu-link img { width: 18px; height: 18px; object-fit: contain; }
    @media (max-width: 950px) { .vendedor-container { flex-direction: column; } .col-sidebar { width: 100%; min-width: 100%; box-sizing: border-box; } }

    
    /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - TABLERO ADMINISTRATIVO */
    
    /* Contraste Seguro: Fuerza el fondo general mitigando parches en la grilla */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo .vendedor-container {
        background-color: #141414 !important;
    }

    /* Modulamos la barra lateral, la caja de la tabla de inventario y las celdas a tono grafito */
    body.alto-contraste-activo .col-sidebar,
    body.alto-contraste-activo .col-tabla-box,
    body.alto-contraste-activo div[style*="overflow-x: auto"] {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #444444 !important;
    }

    /* Saneamiento estructural del menú de navegación y equipos de trabajo */
    body.alto-contraste-activo .nav-menu-link {
        color: #ffffff !important;
    }
    body.alto-contraste-activo .nav-menu-link:hover {
        background-color: #3d3d3d !important;
        color: #f2c300 !important;
    }
    body.alto-contraste-activo .nav-menu-active {
        background-color: #1d3557 !important;
        color: #64b5f6 !important;
    }
    body.alto-contraste-activo div[style*="background: #f9f9f9"] {
        background-color: #1e1e1e !important;
        background: #1e1e1e !important;
        border-color: #444444 !important;
    }

    /* Saneamiento de la tabla de inventario: Evitamos bordes o textos claros perdidos */
    body.alto-contraste-activo table tr {
        border-bottom: 1px solid #444444 !important;
    }
    body.alto-contraste-activo table tr:hover {
        background-color: #333333 !important;
    }
    body.alto-contraste-activo table th {
        color: #b3b3b3 !important;
        border-bottom: 2px solid #555555 !important;
    }
    body.alto-contraste-activo table td {
        color: #ffffff !important;
    }

    /* Forzado estricto de textos generales del core administrativo */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo h2,
    body.alto-contraste-activo h3,
    body.alto-contraste-activo h4,
    body.alto-contraste-activo p,
    body.alto-contraste-activo span,
    body.alto-contraste-activo label,
    body.alto-contraste-activo strong {
        color: #ffffff !important;
    }

    /* Mantenemos los botones de control de edición nativa legibles */
    body.alto-contraste-activo button style*="background-color: #f2f5f6" {
        background-color: #444444 !important;
        color: #ffffff !important;
    }

    /* Resguardo táctico del Widget flotante */
    body.alto-contraste-activo #panel-accesibilidad-global,
    body.alto-contraste-activo #panel-accesibilidad-global * {
        background-color: #ffffff !important;
        color: #111111 !important;
        border-color: #2d6f73 !important;
    }

    /* Tipografía Inclusiva: Verdana de baja visión domina sobre Oswald o Arial */
    body.fuente-accesible-activa,
    body.fuente-accesible-activa * {
        font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
    }
    </style>
</head>
<body>
    <!-- BARRA SUPERIOR INSTITUTIONAL CON CREDENCIALES DINÁMICAS -->
    <header class="top-header">
        <div class="top-header-inner">
            <div class="logo">
                <img src="assets/logo.png" alt="logo" class="logo-img">
                <h1 class="logo-text">TechNest</h1>
            </div>
            <div class="admin-info">
                <!-- REQUERIMIENTO CUMPLIDO: Avatar y Nombre real extraídos de la base de datos de manera dinámica -->
                <div class="admin-user">
                    <img src="<?php echo $avatar_admin_real; ?>" alt="admin" class="avatar" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                    <span><?php echo htmlspecialchars($admin_nombre); ?></span>
                </div>
                <a href="logout.php" style="text-decoration: none;">
                <button class="logout-btn" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 13px; transition: 0.2s;">
                Cerrar sesión
                </button>
                </a>
            </div>
        </div>
    </header>

    <!-- CUERPO PRINCIPAL DEL TABLERO DE CONTROL -->
    <main class="vendedor-container">
        <!-- SIDEBAR DE NAVEGACIÓN Y CONFIGURACIÓN -->
        <aside class="col-sidebar">
            <h4 style="color: #777; font-size: 11px; text-transform: uppercase; margin-bottom: 12px; font-weight: bold; letter-spacing: 0.5px;">Navegación</h4>
            <nav>
            <a href="admin.php" class="nav-menu-link nav-menu-active">
            <img src="assets/calendario.ico" alt="Inventario">
            Gestión de inventario
            </a>
            <a href="admin_pedidos.php" class="nav-menu-link">
            <img src="assets/diamante.ico" alt="Pedidos">
            Visualización de pedidos
            </a>
            <a href="#" class="nav-menu-link">
            <img src="assets/casa.ico" alt="Dashboard">
            Dashboard
            </a>
            <a href="#" class="nav-menu-link">
            <img src="assets/reporte.png" alt="Reportes">
            Reportes
            </a>
            <a href="#" class="nav-menu-link">
            <img src="assets/configuracion.ico" alt="Configuración">
            Configuración
            </a>
            </nav>
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            <h4 style="color: #777; font-size: 11px; text-transform: uppercase; margin-bottom: 12px; font-weight: bold; letter-spacing: 0.5px;">Equipos de trabajo</h4>
            <div style="display: flex; gap: 12px; align-items: center; background: #f9f9f9; padding: 12px; border-radius: 10px; border: 1px solid #f0f0f0;">
                <img src="assets/perro-grupo.png" alt="grupo" style="width: 42px; height: 42px; border-radius: 8px; object-fit: cover;">
                <div>
                    <p style="font-weight: bold; font-size: 14px; color: #333; margin: 0; line-height: 1.2;">Proyecto Grow</p>
                    <div style="display: flex; align-items: center; gap: 5px; color: #777; font-size: 12px; margin-top: 3px;">
                        <img src="assets/user.ico" alt="integrantes" style="width: 12px; height: 14px; opacity: 0.6;">
                        <span>6 integrantes</span>
                    </div>
                </div>
            </div>
        </aside>
        <!-- COLUMNA DERECHA: TABLA DE PRODUCTOS EN TIEMPO REAL -->
        <section class="col-tabla-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
            <h3 style="font-size: 18px; color: #111; margin: 0; font-weight: bold;">Tabla de productos</h3>
            <a href="crear.php" style="text-decoration: none;">
            <button style="background-color: #2d6f73; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; white-space: nowrap; transition: background 0.2s;">
            + Agregar producto
            </button>
            </a>
        </div>
        <div style="width: 100%; overflow-x: auto; background: white;">
            <table style="width: 100%; min-width: 600px; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                    <th style="padding: 12px 10px; color: #777; width: 60px;">ID</th>
                    <th style="padding: 12px 10px; color: #777;">Producto</th>
                    <th style="padding: 12px 10px; color: #777; width: 140px;">Precio</th>
                    <th style="padding: 12px 10px; color: #777; width: 90px; text-align: center;">Stock</th>
                    <th style="padding: 12px 10px; color: #777; width: 180px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultado && mysqli_num_rows($resultado) > 0):
                    while($row = mysqli_fetch_assoc($resultado)): 
                    ?>
                    <tr style="border-bottom: 1px solid #eee; transition: background 0.15s;">
                    <td style="padding: 14px 10px; font-weight: bold; color: #333;">#<?php echo $row['id']; ?></td>
                    <td style="padding: 14px 10px; color: #555;"><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td style="padding: 14px 10px; font-weight: bold; color: #111;">$<?php echo number_format($row['precio'], 0, ',', '.'); ?> COP</td>
                    <td style="padding: 14px 10px; color: #555; text-align: center; font-weight: bold;"><?php echo $row['stock']; ?></td>
                    <td style="padding: 14px 10px;">
                    <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                    <a href="editar.php?id=<?php echo $row['id']; ?>" style="text-decoration: none;">
                    <button style="background-color: #f2f5f6; color: #2d6f73; border: 1px solid #ddd; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; transition: 0.15s;">Editar</button>
                    </a>
                    <a href="eliminar.php?id=<?php echo $row['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este producto permanentemente del inventario?');" style="text-decoration: none;">
                    <button style="background-color: #fff1f0; color: #c0392b; border: 1px solid #ffa39e; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; transition: 0.15s;">Eliminar</button>
                    </a>
                    </div>
                    </td>
                    </tr>
                    <?php
                    endwhile; 
                    else:
                    ?>
                    <!-- REQUERIMIENTO CUMPLIDO: Estado vacío controlado con redirección de recursos unificados -->
                    <tr>
                    <td colspan="5" style="text-align: center; color: #777; padding: 50px 20px; font-size: 15px; background: #fafafa; border-radius: 8px;">
                    <img src="assets/reporte.png" alt="vacio" style="width: 45px; opacity: 0.25; margin-bottom: 12px; object-fit: contain;"><br>
                    <span style="font-weight: 500; color: #666;">No hay productos registrados en el inventario actual.</span>
                    </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
                        estiloFuentesDinamico.id = 'fuente-accessible-forzado';
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
