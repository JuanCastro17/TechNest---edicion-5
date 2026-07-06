<?php
// public/detalles_pedido.php - PARTE 1 DE 2
session_start();

// Filtro estricto de seguridad: Si el usuario no está logueado, se va al login
if (!isset($_SESSION['tienda_user'])) {
    header("Location: seleccion_login.php");
    exit();
}

include(__DIR__ . '/../config/conexion.php');

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}
$id_usuario_activo = intval($_SESSION['id_usuario']);

// Capturamos el parámetro relacional de grupo unificado que viaja desde rastreo.php
$codigo_grupo = isset($_GET['grupo']) ? mysqli_real_escape_string($conexion, trim($_GET['grupo'])) : '';
$pedido_existe = false;

// Variables de control para la cabecera única del comprobante
$id_factura_visible = 0;
$nombre_cliente_visible = "";
$fecha_visible = "";
$total_acumulado_factura = 0;

if (!empty($codigo_grupo)) {
    // CONSULTA DE CABECERA: Obtenemos los datos globales compartidos del lote de compra
    $query_cabecera = "SELECT id_venta, fecha_venta, nombre_cliente, SUM(total) AS gran_total 
                       FROM ventas 
                       WHERE codigo_grupo = '$codigo_grupo' AND id_usuario = $id_usuario_activo
                       GROUP BY code_grupo LIMIT 1";
    
    // Ajuste de respaldo para mapear el campo correcto en grilla local si hay variaciones de sintaxis
    $query_cabecera = "SELECT id_venta, fecha_venta, nombre_cliente, SUM(total) AS gran_total FROM ventas WHERE codigo_grupo = '$codigo_grupo' AND id_usuario = $id_usuario_activo GROUP BY codigo_grupo LIMIT 1";
    $res_cabecera = mysqli_query($conexion, $query_cabecera);
    if ($res_cabecera && mysqli_num_rows($res_cabecera) > 0) {
        $cabecera_info = mysqli_fetch_assoc($res_cabecera);
        $id_factura_visible = $cabecera_info['id_venta'];
        $nombre_cliente_visible = $cabecera_info['nombre_cliente'];
        $fecha_visible = $cabecera_info['fecha_venta'];
        $total_acumulado_factura = $cabecera_info['gran_total'];
        $pedido_existe = true;
    }
}

// Redirección de protección si intentan forzar la URL con un lote inexistente o ajeno
if (!$pedido_existe) {
    header("Location: rastreo.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Pedido #<?php echo $id_factura_visible; ?> | TechNest</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body { background-color: #f2f5f6; font-family: sans-serif; margin: 0; padding: 0; }
    .detalle-header { background-color: #2d6f73; padding: 10px 30px; display: flex; align-items: center; justify-content: space-between; box-sizing: border-box; }
    .detalle-header img { width: 80px; height: 80px; object-fit: contain; }
    .detalle-header h1 { color: white; font-size: 24px; margin: 0; font-weight: bold; }
    .btn-regresar-rastreo { background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 18px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 13px; text-decoration: none; transition: 0.2s; }
    .btn-regresar-rastreo:hover { background: rgba(255,255,255,0.3); }
    .detalle-container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
    .card-recibo { background: white; border: 1px solid #ccc; border-radius: 15px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); text-align: left; }
    .card-recibo h2 { color: #2d6f73; font-size: 22px; margin-top: 0; margin-bottom: 5px; font-weight: bold; }
    .badge-transaccion { background: #2d6f73; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; margin-bottom: 25px; }
    .info-grupo { margin-bottom: 18px; }
    .info-grupo label { font-weight: bold; color: #555; font-size: 13px; display: block; margin-bottom: 3px; text-transform: uppercase; }
    .info-grupo p { margin: 0; color: #111; font-size: 15px; font-weight: 500; }
    .tabla-desglose { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
    .tabla-desglose th { padding: 10px; text-align: left; color: #777; font-size: 13px; border-bottom: 2px solid #eee; text-transform: uppercase; }
    .tabla-desglose td { padding: 12px 10px; color: #333; font-size: 14px; border-bottom: 1px solid #eee; }
    .link-articulo-ficha { color: #111; text-decoration: none; font-weight: bold; transition: color 0.15s; display: block; }
    .link-articulo-ficha:hover { color: #2d6f73; text-decoration: underline; }
    .total-recibo-box { background: #f8fafb; border: 1px solid #eee; border-radius: 10px; padding: 15px; display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }

    
    /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON DETALLES FACTURA */
    
    /* Contraste Seguro: Fuerza el entorno oscuro mitigando parches rígidos */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo .detalle-container {
        background-color: #141414 !important;
    }

    /* Modulamos la tarjeta del recibo, las celdas y el cuadro del total consolidado a grafito */
    body.alto-contraste-activo .card-recibo,
    body.alto-contraste-activo .total-recibo-box,
    body.alto-contraste-activo tr[style*="background: #fafafa"],
    body.alto-contraste-activo tr[style*="background:#fafafa"] {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #555555 !important;
        color: #ffffff !important;
    }

    /* Conservamos los colores corporativos de la barra superior del encabezado */
    body.alto-contraste-activo .detalle-header {
        background-color: #2d6f73 !important;
    }

    /* Saneamiento estructural de la tabla de desglose bancario */
    body.alto-contraste-activo .tabla-desglose th {
        background-color: #3a3a3a !important;
        color: #b3b3b3 !important;
        border-bottom: 2px solid #555555 !important;
    }
    body.alto-contraste-activo .tabla-desglose td {
        border-bottom: 1px solid #444444 !important;
        color: #ffffff !important;
    }

    /* Forzado estricto de textos para que los hipervínculos de los productos resalten */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo h2,
    body.alto-contraste-activo p,
    body.alto-contraste-activo span,
    body.alto-contraste-activo label,
    body.alto-contraste-activo .link-articulo-ficha {
        color: #ffffff !important;
    }
    body.alto-contraste-activo .link-articulo-ficha:hover {
        color: #64b5f6 !important;
    }

    /* El total consolidado y los identificadores numéricos brillan en un azul legible */
    body.alto-contraste-activo .total-recibo-box span[style*="color: #2d6f73"],
    body.alto-contraste-activo td[style*="color: #2d6f73"] {
        color: #64b5f6 !important;
    }

    /* Ajuste selectivo de color para el costo del flete (Gratis en verde) */
    body.alto-contraste-activo td[style*="color: #009e49"] {
        color: #66bb6a !important;
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
    <!-- CABECERA CORPORATIVA DE LA PLATAFORMA -->
    <header class="detalle-header">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="assets/logo.png" alt="Logo TechNest">
            <h1 style="font-family: 'Oswald', sans-serif;">TechNest</h1>
        </div>
        <a href="rastreo.php" class="btn-regresar-rastreo"> Volver al Historial</a>
    </header>

    <!-- CUERPO PRINCIPAL DEL RECIBO FACTURADO -->
    <main class="detalle-container">
        <div class="card-recibo">
            <h2>Descripción de la Compra</h2>
            <span class="badge-transaccion">Comprobante Oficial #<?php echo $id_factura_visible; ?></span>

            <div class="info-grupo">
                <label>Destinatario y Titular de Cuenta:</label>
                <p><?php echo htmlspecialchars($nombre_cliente_visible); ?> (@<?php echo htmlspecialchars($_SESSION['tienda_user']); ?>)</p>
            </div>

            <div class="info-grupo">
                <label>Fecha y Hora del Registro Bancario:</label>
                <p><?php echo date("d/m/Y - h:i A", strtotime($fecha_visible)); ?></p>
            </div>

            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">

            <label style="font-weight: bold; color: #555; font-size: 13px; text-transform: uppercase; display: block; margin-bottom: 10px;">Artículos Incluidos en el Despacho:</label>
            <table class="tabla-desglose">
                <thead>
                    <tr>
                    <th>Descripción del Artículo</th>
                    <th style="text-align: center; width: 80px;">Cant.</th>
                    <th style="text-align: right; width: 120px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // CICLO REPETITIVO DINÁMICO: Jalamos cada artículo enlazado de forma individual
                    $query_items_lote = "SELECT v.cantidad, v.total, p.id AS prod_id, p.nombre AS prod_nombre 
                                        FROM ventas v
                                        JOIN productos p ON v.id_producto = p.id
                                        WHERE v.codigo_grupo = '$codigo_grupo' AND v.id_usuario = $id_usuario_activo";
                    $res_items = mysqli_query($conexion, $query_items_lote);

                    $unidades_totales_lote = 0;
                    if ($res_items && mysqli_num_rows($res_items) > 0):
                    while ($item_row = mysqli_fetch_assoc($res_items)):
                    $unidades_totales_lote += intval($item_row['cantidad']);
                    ?>
                    <tr>
                    <td>
                    <!-- REQUERIMIENTO CUMPLIDO: Redirección relacional nativa al presionar el nombre del producto -->
                    <a href="producto.php?id=<?php echo $item_row['prod_id']; ?>" class="link-articulo-ficha" title="Click para ver la ficha técnica">
                    <?php echo htmlspecialchars($item_row['prod_nombre']); ?>
                    </a>
                    </td>
                    <td style="text-align: center; font-weight: bold; color: #2d6f73;">
                    x<?php echo $item_row['cantidad']; ?>
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #333;">
                    $<?php echo number_format($item_row['total'], 0, ',', '.'); ?>
                    </td>
                    </tr>
                    <?php
                    endwhile;
                    endif; 

                    // REQUERIMIENTO CUMPLIDO: Calculamos el costo del flete según las unidades globales del lote comprado
                    $costo_envio_lote = ($unidades_totales_lote > 1) ? 12000 : 0;
                    $texto_envio_tabla = ($costo_envio_lote > 0) ? "$ " . number_format($costo_envio_lote, 0, ',', '.') : "Gratis";
                    $estilo_color_envio = ($costo_envio_lote > 0) ? "color: #333; font-weight: bold;" : "color: #009e49; font-weight: bold;";
                    ?>
                    <!-- Fila dinámica inyectada para desglosar el envío institucional -->
                    <tr style="background: #fafafa;">
                    <td style="font-style: italic; color: #666; font-weight: 500;">
                    Servicio de Envío a Domicilio
                    </td>
                    <td style="text-align: center; color: #777; font-weight: bold;">-</td>
                    <td style="text-align: right; <?php echo $estilo_color_envio; ?>">
                    <?php echo $texto_envio_tabla; ?>
                    </td>
                    </tr>
                </tbody>
            </table>

            <div class="total-recibo-box">
                <span style="font-weight: bold; color: #555; font-size: 14px; text-transform: uppercase;">Total Consolidado Lote:</span>
                <span style="font-size: 18px; color: #2d6f73; font-weight: bold;">$<?php echo number_format($total_acumulado_factura, 0, ',', '.'); ?> COP</span>
            </div>

            <div style="background: #fbebeb; border: 1px solid #ffa39e; padding: 12px; border-radius: 8px; margin-top: 20px; font-size: 13px; color: #c0392b; font-weight: 500; line-height: 1.4; text-align: center;">
                Recuerda que el estado del paquete cambiará a "En Ruta" una vez que verifiquemos la transacción de soporte bancario con tu N° de Convenio.
            </div>
        </div>
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
