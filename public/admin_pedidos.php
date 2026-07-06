<?php
// public/admin_pedidos.php - PARTE 1 DE 3 (LOGICA BACKEND PHP)
session_start();

// Control de seguridad institucional: Solo administradores autorizados
if (!isset($_SESSION['admin_user'])) {
    header("Location: login_admin.php");
    exit();
}

include(__DIR__ . '/../config/conexion.php');

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// Datos dinámicos del administrador en sesión
$admin_nombre = $_SESSION['admin_user'];
$admin_avatar_file = $_SESSION['admin_avatar'];

$avatar_admin_real = "assets/" . $admin_avatar_file;
if (!file_exists($avatar_admin_real) || empty($admin_avatar_file)) {
    $avatar_admin_real = "assets/perro-admin.png"; 
}

// CONSULTA RELACIONAL AVANZADA: Cruzamos Ventas, Productos y Usuarios (Vendedores)
$query_pedidos = "SELECT v.id_venta, v.cantidad, v.precio_unitario, v.total, 
                         v.nombre_cliente, v.fecha_venta,
                         p.nombre AS producto_nombre,
                         CONCAT(u.nombre, ' ', u.apellido) AS vendedor_completo
                  FROM ventas v
                  JOIN productos p ON v.id_producto = p.id
                  JOIN usuarios u ON v.id_vendedor = u.id_usuario
                  ORDER BY v.id_venta DESC";
$resultado_pedidos = mysqli_query($conexion, $query_pedidos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Visualización de Pedidos Manuales | TechNest</title>
<!-- CORRECCIÓN ASSETS: Eliminamos prefijos rotos llamando directo desde la raíz virtual -->
<link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/png" href="assets/logoicono.ico">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* Estilos inline para asegurar la adaptabilidad responsiva del tablero de pedidos */
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
.vendedor-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.vendedor-table th { background: #f8faf9; padding: 12px 10px; text-align: left; color: #777; border-bottom: 2px solid #eee; }
.vendedor-table td { padding: 14px 10px; border-bottom: 1px solid #eee; color: #333; }
.vendedor-badge { background-color: #235457; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; display: inline-block; }
@media (max-width: 950px) { .vendedor-container { flex-direction: column; } .col-sidebar { width: 100%; min-width: 100%; box-sizing: border-box; } }

/* ========================================================================== */
/* ♿ SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - AUDITORÍA DE PEDIDOS REPARADA */
/* ========================================================================== */
body.alto-contraste-activo {
    background-color: #141414 !important;
    color: #ffffff !important;
}
body.alto-contraste-activo main,
body.alto-contraste-activo .vendedor-container {
    background-color: #141414 !important;
}

/* Modulamos la barra lateral, la caja de la tabla de auditoría y los marcos contenedores */
body.alto-contraste-activo .col-sidebar,
body.alto-contraste-activo .col-tabla-box,
body.alto-contraste-activo div[style*="overflow-x: auto"] {
    background-color: #2d2d2d !important;
    background: #2d2d2d !important;
    border-color: #444444 !important;
}

/* 🛠️ REQUERIMIENTO CUMPLIDO: Forzamos la celda del estado vacío (con fondo inline #fafafa) a tornarse oscura */
body.alto-contraste-activo .vendedor-table tbody tr td,
body.alto-contraste-activo td[style*="background: #fafafa"],
body.alto-contraste-activo td[style*="background:#fafafa"] {
    background-color: #2d2d2d !important;
    background: #2d2d2d !important;
    color: #ffffff !important;
}

/* Saneamiento de las filas y cabeceras de la tabla de pedidos */
body.alto-contraste-activo .vendedor-table tr {
    border-bottom: 1px solid #444444 !important;
}
body.alto-contraste-activo .vendedor-table tr:hover {
    background-color: #333333 !important;
}
body.alto-contraste-activo .vendedor-table th {
    background-color: #3a3a3a !important;
    color: #b3b3b3 !important;
    border-bottom: 2px solid #555555 !important;
}
body.alto-contraste-activo .vendedor-table td {
    color: #ffffff !important;
}

/* Menú de navegación de la barra lateral */
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

body.alto-contraste-activo .vendedor-badge {
    background-color: #0f4c4e !important;
    color: #ffffff !important;
    border: 1px solid #2d6f73 !important;
}

/* Icono del diamante: Le inyectamos un filtro de inversión blanco para que resalte en el fondo oscuro */
body.alto-contraste-activo img[src*="diamante"] {
    filter: brightness(0) invert(1) !important;
    opacity: 0.8 !important;
}

/* Forzado estricto de todos los textos informativos */
body.alto-contraste-activo h1,
body.alto-contraste-activo h2,
body.alto-contraste-activo h3,
body.alto-contraste-activo p,
body.alto-contraste-activo span,
body.alto-contraste-activo label,
body.alto-contraste-activo strong {
    color: #ffffff !important;
}

body.alto-contraste-activo #panel-accesibilidad-global,
body.alto-contraste-activo #panel-accesibilidad-global * {
    background-color: #ffffff !important;
    color: #111111 !important;
    border-color: #2d6f73 !important;
}

body.fuente-accesible-activa,
body.fuente-accesible-activa * {
    font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
}
</style>
</head>
<body>
<!-- BARRA SUPERIOR CON CREDENCIALES DINÁMICAS -->
<header class="top-header">
<div class="top-header-inner">
<div class="logo">
<img src="assets/logo.png" alt="logo" class="logo-img">
<h1 class="logo-text">TechNest</h1>
</div>
<div class="admin-info">
<div class="admin-user">
<img src="<?php echo $avatar_admin_real; ?>" alt="admin" class="avatar" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
<span><?php echo htmlspecialchars($admin_nombre); ?></span>
</div>
<a href="logout.php" style="text-decoration: none;">
<button style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 13px;">
Cerrar sesión
</button>
</a>
</div>
</div>
</header>

<!-- TABLERO CENTRAL -->
<main class="vendedor-container">
<!-- SIDEBAR DE NAVEGACIÓN CORPORATIVA -->
<aside class="col-sidebar">
<h4 style="color: #777; font-size: 11px; text-transform: uppercase; margin-bottom: 12px; font-weight: bold; letter-spacing: 0.5px;">Navegación</h4>
<nav>
<a href="admin.php" class="nav-menu-link">
<img src="assets/calendario.ico" alt="Inventario">
Gestión de inventario
</a>
<a href="admin_pedidos.php" class="nav-menu-link nav-menu-active">
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
</aside>

<!-- PANEL DE VISUALIZACIÓN DE PEDIDOS MANUALES -->
<section class="col-tabla-box">
<div style="margin-bottom: 25px;">
<h3 style="font-size: 18px; color: #111; margin: 0; font-weight: bold;">Auditoría de Ventas Manuales</h3>
<p style="color: #666; font-size: 14px; margin: 5px 0 0 0;">Historial de transacciones registradas de forma directa por el equipo de asesores comerciales.</p>
</div>
<div style="width: 100%; overflow-x: auto; background: white;">
<table class="vendedor-table">
<thead>
<tr>
<th style="width: 70px;">ID Venta</th>
<th>Asesor / Vendedor</th>
<th>Cliente Atendido</th>
<th>Artículo Vendido</th>
<th style="text-align: center; width: 60px;">Cant.</th>
<th style="width: 130px;">Total Cobrado</th>
<th style="width: 140px;">Fecha/Hora</th>
</tr>
</thead>
<tbody>
<?php if ($resultado_pedidos && mysqli_num_rows($resultado_pedidos) > 0): ?>
<?php while($pedido = mysqli_fetch_assoc($resultado_pedidos)): ?>
<tr style="border-bottom: 1px solid #eee;">
<td style="font-weight: bold; color: #2d6f73;">#<?php echo $pedido['id_venta']; ?></td>
<td>
<!-- Distintivo visual para identificar al vendedor -->
<span class="vendedor-badge">
<?php echo htmlspecialchars($pedido['vendedor_completo']); ?>
</span>
</td>
<td style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></td>
<td style="color: #555;"><?php echo htmlspecialchars($pedido['producto_nombre']); ?></td>
<td style="text-align: center; font-weight: bold; color: #111;"><?php echo $pedido['cantidad']; ?></td>
<td style="color: #009e49; font-weight: bold;">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?> COP</td>
<td style="color: #777; font-size: 12px;"><?php echo $pedido['fecha_venta']; ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="7" style="text-align: center; color: #777; padding: 50px 20px; font-size: 15px; background: #fafafa; border-radius: 8px;">
<img src="assets/diamante.ico" alt="vacio" style="width: 45px; opacity: 0.25; margin-bottom: 12px; object-fit: contain;"><br>
<span style="font-weight: 500; color: #666;">No se registran transacciones manuales facturadas por vendedores en el sistema.</span>
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
