<?php
// public/rastreo.php - PARTE 1 DE 2
session_start();

// REQUERIMIENTO COMPLETO: Bloqueo estricto de seguridad. Si el usuario no está logueado, se va al login
if (!isset($_SESSION['tienda_user'])) {
    echo "<script>
    alert('Acceso restringido. Debes iniciar sesión primeramente para rastrear tus pedidos.');
    window.location.href = 'seleccion_login.php';
    </script>";
    exit();
}

// Conectamos a tu base de datos unificada subiendo un nivel por capas
include(__DIR__ . '/../config/conexion.php');
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}
$id_cliente_activo = intval($_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Historial de Pedidos | TechNest</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body { background-color: #f2f5f6; font-family: sans-serif; margin: 0; padding: 0; }
    .rastreo-header { background-color: #2d6f73; padding: 10px 30px; width: 100%; box-sizing: border-box; }
    .rastreo-header-inner { display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; width: 100%; }
    .rastreo-logo-box { display: flex; align-items: center; gap: 12px; }
    .rastreo-logo-box img { width: 80px; height: 80px; object-fit: contain; } 
    .rastreo-logo-box h1 { color: white; font-size: 24px; margin: 0; font-weight: bold; }
    .btn-volver-catalogo { background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 18px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 13px; text-decoration: none; transition: 0.2s; }
    .btn-volver-catalogo:hover { background: rgba(255,255,255,0.3); }
    .rastreo-container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
    .ticket-status-box { background: white; border: 1px solid #ccc; border-radius: 15px; padding: 30px; margin-top: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .status-badge-tecnico { background: #2d6f73; color: white; padding: 6px 14px; border-radius: 20px; font-weight: bold; display: inline-block; font-size: 13px; margin-bottom: 15px; }
    .status-timeline { display: flex; flex-direction: column; gap: 15px; margin-top: 20px; position: relative; padding-left: 20px; border-left: 2px solid #2d6f73; text-align: left; }
    .timeline-node { font-size: 14px; color: #333; position: relative; }
    .timeline-node::before { content: '✓'; position: absolute; left: -27px; top: 0; background: #2d6f73; color: white; width: 16px; height: 16px; border-radius: 50%; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    .btn-detalles-pildora { display: block; text-align: center; background-color: #2d6f73; color: white; padding: 10px 20px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 13px; margin-top: 15px; transition: 0.2s; text-transform: uppercase; border: 1px solid #235457; }
    .btn-detalles-pildora:hover { background-color: #235457; transform: scale(1.01); }

    
    /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON BITÁCORA RASTREO */
    
    /* Contraste Seguro: Fuerza el entorno oscuro mitigando parches en la línea de tiempo */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo .rastreo-container {
        background-color: #141414 !important;
    }

    /* Modulamos los recibos de estado y los recuadros del lote a un elegante tono grafito */
    body.alto-contraste-activo .ticket-status-box,
    body.alto-contraste-activo div[style*="background: #f8fafb"],
    body.alto-contraste-activo div[style*="background:#f8fafb"],
    body.alto-contraste-activo div[style*="background: white"],
    body.alto-contraste-activo div[style*="background:white"] {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #555555 !important;
        color: #ffffff !important;
    }

    /* Conservamos y adaptamos el matiz de la barra superior del encabezado */
    body.alto-contraste-activo .rastreo-header {
        background-color: #2d6f73 !important;
    }

    /* Saneamiento de las burbujas internas y nodos de la línea de distribución */
    body.alto-contraste-activo .status-badge-tecnico {
        background-color: #0f4c4e !important;
        border: 1px solid #2d6f73 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo .status-timeline {
        border-left: 2px solid #64b5f6 !important;
    }
    body.alto-contraste-activo .timeline-node {
        color: #ffffff !important;
    }
    body.alto-contraste-activo .timeline-node::before {
        background: #64b5f6 !important;
        color: #111111 !important;
    }

    /* Forzado estricto de textos para evitar lecturas oscuras invisibles */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo h2,
    body.alto-contraste-activo h3,
    body.alto-contraste-activo p,
    body.alto-contraste-activo span,
    body.alto-contraste-activo label,
    body.alto-contraste-activo strong {
        color: #ffffff !important;
    }

    /* Ajuste selectivo de color para advertencias de transacciones pendientes */
    body.alto-contraste-activo div[style*="color: #ffaa00"] strong,
    body.alto-contraste-activo .timeline-node[style*="color: #ffaa00"] strong {
        color: #ffb74d !important;
    }

    /* Resguardo del panel flotante del Widget */
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
    <!-- HEADER DE RASTREO CORPORATIVO -->
    <header class="rastreo-header">
        <div class="rastreo-header-inner">
            <div class="rastreo-logo-box">
                <img src="assets/logo.png" alt="Logo TechNest">
                <h1 style="font-family: 'Oswald', sans-serif;">TechNest</h1>
            </div>
            <a href="index.php" class="btn-volver-catalogo">Volver al inicio</a>
        </div>
    </header>

    <!-- PORTAL CENTRAL DE CONSULTA DE LOTES EN TIEMPO REAL -->
    <main class="rastreo-container">
        <div style="margin-bottom: 25px; border-bottom: 2px solid #ccc; padding-bottom: 15px; text-align: left;">
            <h2 style="font-size: 24px; color: #2d6f73; margin: 0; font-weight: bold;">Mi Historial de Pedidos</h2>
            <p style="color: #666; font-size: 14px; margin: 4px 0 0 0;">Consulta el estado, número único y marcas de envío de tus transacciones comerciales en tiempo real.</p>
        </div>

        <?php
        // REQUERIMIENTO CUMPLIDO: Consulta relacional avanzada con GROUP_CONCAT
        $query_mis_pedidos = "SELECT v.id_venta, v.fecha_venta, v.nombre_cliente, v.codigo_grupo,
                               SUM(v.cantidad) AS cantidad_total,
                               SUM(v.total) AS subtotal_acumulado,
                               GROUP_CONCAT(p.nombre SEPARATOR ', ') AS productos_combinados
                        FROM ventas v 
                        JOIN productos p ON v.id_producto = p.id
                        WHERE v.id_usuario = $id_cliente_activo
                        GROUP BY v.codigo_grupo
                        ORDER BY v.id_venta DESC";
        $resultado_mis_pedidos = mysqli_query($conexion, $query_mis_pedidos);

        if ($resultado_mis_pedidos && mysqli_num_rows($resultado_mis_pedidos) > 0):
            while ($pedido = mysqli_fetch_assoc($resultado_mis_pedidos)):
                // Formateamos la lista para que aparezca entre paréntesis de forma estética
                $texto_lista_productos = "(" . $pedido['productos_combinados'] . ")";
        ?>
                <div class="ticket-status-box" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                        <span class="status-badge-tecnico" style="margin-bottom: 0;"> Transacción #<?php echo $pedido['id_venta']; ?></span>
                        <span style="font-size: 12px; color: #777; font-weight: bold;"><?php echo date("d/m/Y H:i", strtotime($pedido['fecha_venta'])); ?></span>
                    </div>
                    
                    <div style="text-align: left;">
                        <!-- Renderizado dinámico de la lista agrupada de MySQL -->
                        <h3 style="margin: 0; color: #111; font-size: 15px; font-weight: bold; line-height: 1.45; font-style: italic;"><?php echo htmlspecialchars($texto_lista_productos); ?></h3>
                        <p style="color: #666; font-size: 13px; margin: 6px 0 12px 0;">Destinatario registrado: <strong><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></strong></p>
                        
                        <div style="display: flex; justify-content: space-between; background: #f8fafb; padding: 10px 15px; border-radius: 8px; font-size: 13px; border: 1px solid #eee; margin-bottom: 15px;">
                            <span style="color: #555;">Unidades del paquete: <strong>x<?php echo $pedido['cantidad_total']; ?></strong></span>
                            <span style="color: #2d6f73; font-weight: bold;">Total Lote: $<?php echo number_format($pedido['subtotal_acumulado'], 0, ',', '.'); ?> COP</span>
                        </div>
                    </div>
                    <!-- LÍNEA DE TIEMPO DEL RASTREO -->
                    <div class="status-timeline">
                        <div class="timeline-node">
                            <strong>Pedido registrado en plataforma con éxito</strong>
                            <p style="margin: 2px 0 0 0; color: #666; font-size: 12px;">Tu orden ha ingresado de manera correcta en el inventario global de la tienda.</p>
                        </div>
                        <div class="timeline-node" style="color: #ffaa00; font-weight: bold;">
                            <strong>Aún se espera procesar pago en ventanilla bancaria</strong>
                            <p style="margin: 2px 0 0 0; color: #555; font-size: 12px; font-weight: normal;">Consigna el monto final usando tu número de convenio antes de que expire el plazo de 72 horas para habilitar la ruta de distribución.</p>
                        </div>
                    </div>

                    <!-- ENLACE DE GRUPO REPARADO -->
                    <a href="detalles_pedido.php?grupo=<?php echo urlencode($pedido['codigo_grupo']); ?>" class="btn-detalles-pildora">
                        Ver Detalles de Compra
                    </a>
                </div>
        <?php
            endwhile;
        else:
        ?>
            <!-- CONTROL DE CONTROLADOR VACÍO -->
            <div style="text-align: center; color: #888; padding: 40px 20px; background: white; border-radius: 15px; border: 1px solid #ccc;" id="ticketVacio">
                <img src="assets/campana.png" alt="vacio" style="width: 40px; opacity: 0.2; margin-bottom: 12px; object-fit: contain;"><br>
                <strong>No registras transacciones ni pedidos en el historial de esta cuenta.</strong>
            </div>
        <?php endif; ?>
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
