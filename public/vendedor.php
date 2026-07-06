<?php
// public/vendedor.php - PARTE 1 DE 3 (LOGICA DE NEGOCIO PHP)
session_start();

// Control de seguridad institucional: Si no es vendedor, redirecciona
if (!isset($_SESSION['vendedor_user'])) {
    header("Location: login_vendedor.php");
    exit();
}

include(__DIR__ . '/../config/conexion.php'); // Incluye el conector general

// Bloque de respaldo: Si la variable de conexión falla, forzamos el enlace directo al puerto 3307
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

$mensaje = "";
$id_vendedor_actual = $_SESSION['id_vendedor'];

// 1. FOTO DE PERFIL DEL ASESOR
$query_vend = "SELECT avatar FROM usuarios WHERE id_usuario = $id_vendedor_actual LIMIT 1";
$res_vend = mysqli_query($conexion, $query_vend);
$avatar_vendedor = "assets/user.ico";
if ($res_vend && mysqli_num_rows($res_vend) > 0) {
    $info_vend = mysqli_fetch_assoc($res_vend);
    if (!empty($info_vend['avatar']) && file_exists("uploads/" . $info_vend['avatar'])) {
        $avatar_vendedor = "uploads/" . $info_vend['avatar'];
    }
}

// 2. REGISTRAR VENTA MANUAL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_venta'])) {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);
    $nombre_cliente = mysqli_real_escape_string($conexion, trim($_POST['nombre_cliente']));
    
    $query_stock = "SELECT precio, stock FROM productos WHERE id = $id_producto LIMIT 1";
    $res_stock = mysqli_query($conexion, $query_stock);
    
    if ($res_stock && mysqli_num_rows($res_stock) > 0) {
        $prod_info = mysqli_fetch_assoc($res_stock);
        $stock_actual = $prod_info['stock'];
        $precio_unitario = $prod_info['precio'];
        $total = $precio_unitario * $cantidad;
        
        if ($stock_actual >= $cantidad) {
            $query_venta = "INSERT INTO ventas (id_vendedor, id_producto, cantidad, precio_unitario, total, nombre_cliente) 
                            VALUES ($id_vendedor_actual, $id_producto, $cantidad, $precio_unitario, $total, '$nombre_cliente')";
            if (mysqli_query($conexion, $query_venta)) {
                $nuevo_stock = $stock_actual - $cantidad;
                mysqli_query($conexion, "UPDATE productos SET stock = $nuevo_stock WHERE id = $id_producto");
                $mensaje = "<div class='alert alert-success fw-bold' style='border-radius:8px;'>✓ Venta manual registrada con éxito.</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-danger fw-bold' style='border-radius:8px;'>⚠ Stock insuficiente ($stock_actual disponibles).</div>";
        }
    }
}

// 3. EDITAR TRANSACCIÓN MANUAL (SOLUCIÓN AL BUG RECALCULANDO STOCK REAL)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_venta'])) { 
    $id_venta_edit = intval($_POST['id_venta_edit']);
    $nueva_cant = intval($_POST['nueva_cantidad']);
    $nuevo_cliente = mysqli_real_escape_string($conexion, trim($_POST['nuevo_nombre_cliente']));
    
    $q_venta_ant = "SELECT id_producto, cantidad, precio_unitario FROM ventas WHERE id_venta = $id_venta_edit LIMIT 1";
    $r_venta_ant = mysqli_query($conexion, $q_venta_ant);
    
    if ($r_venta_ant && mysqli_num_rows($r_venta_ant) > 0) {
        $v_ant = mysqli_fetch_assoc($r_venta_ant);
        $id_prod = $v_ant['id_producto'];
        $cant_anterior = $v_ant['cantidad'];
        $precio_un = $v_ant['precio_unitario'];
        
        $q_prod = "SELECT stock FROM productos WHERE id = $id_prod LIMIT 1";
        $r_prod = mysqli_query($conexion, $q_prod);
        $p_info = mysqli_fetch_assoc($r_prod);
        $stock_real_prod = $p_info['stock'];
        
        // Devolución virtual del stock previo para validar límites reales de la tienda
        $stock_disponible_simulado = $stock_real_prod + $cant_anterior;
        
        if ($stock_disponible_simulado >= $nueva_cant) {
            $nuevo_total = $precio_un * $nueva_cant;
            $q_update_v = "UPDATE ventas SET cantidad = $nueva_cant, total = $nuevo_total, nombre_cliente = '$nuevo_cliente' WHERE id_venta = $id_venta_edit";
            if (mysqli_query($conexion, $q_update_v)) {
                $final_stock = $stock_disponible_simulado - $nueva_cant;
                mysqli_query($conexion, "UPDATE productos SET stock = $final_stock WHERE id = $id_prod");
                $mensaje = "<div class='alert alert-success fw-bold' style='border-radius:8px;'>✓ Transacción corregida e inventario actualizado.</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-danger fw-bold' style='border-radius:8px;'>⚠ Error: Excede el stock real.</div>";
        }
    }
}

// Consultas base optimizadas
$productos_res = mysqli_query($conexion, "SELECT id, nombre, precio, stock FROM productos ORDER BY nombre ASC");
$historial_res = mysqli_query($conexion, "SELECT v.*, p.nombre AS producto_nombre FROM ventas v JOIN productos p ON v.id_producto = p.id WHERE v.id_vendedor = $id_vendedor_actual ORDER BY v.id_venta DESC");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>Panel de Ventas | TechNest</title>
    <!-- Rutas de la raíz virtual de la capa pública sin prefijos de carpetas -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body { background-color: #f4f7f6; font-family: Arial, sans-serif; margin: 0; }
    .vendedor-header { background-color: #235457; padding: 12px 30px; display: flex; justify-content: space-between; align-items: center; }
    .vendedor-container { max-width: 1200px; margin: 30px auto; display: flex; gap: 25px; padding: 0 20px; }
    .col-formulario { flex: 1; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
    .col-historial { flex: 1.5; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
    .form-group { margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-weight: bold; color: #333; font-size: 14px; }
    .form-group input, .form-group select { padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; outline: none; }
    .btn-submit { background: #235457; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; width: 100%; }
    .btn-submit:hover { background: #193f41; }
    .btn-chat-link { background: #1a73e8; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: bold; font-size: 15px; text-decoration: none; display: block; text-align: center; margin-bottom: 25px; transition: 0.2s; box-shadow: 0 2px 5px rgba(26,115,232,0.2); }
    .btn-chat-link:hover { background: #1557b0; color: white; transform: scale(1.01); }
    .perfil-vendedor-box { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.15); padding: 6px 14px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.25); }
    .avatar-vendedor-img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid white; background: white; }
    .vendedor-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .vendedor-table th { background: #f8faf9; padding: 12px; text-align: left; color: #777; border-bottom: 2px solid #eee; }
    .vendedor-table td { padding: 12px; border-bottom: 1px solid #eee; color: #333; }
    .modal-edit { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; }
    .modal-edit-content { background:white; padding:30px; border-radius:12px; width:100%; max-width:400px; box-shadow:0 4px 20px rgba(0,0,0,0.2); }
    @media (max-width: 900px) { .vendedor-container { flex-direction: column; } }

    
    /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE PANEL OPERATIVO */
    
    /* Contraste Seguro: Fuerza el entorno a negro mitigando parches en las tablas */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo .vendedor-container {
        background-color: #141414 !important;
    }

    /* Modulamos las dos columnas de control y el formulario modal a grafito con bordes claros */
    body.alto-contraste-activo .col-formulario,
    body.alto-contraste-activo .col-historial,
    body.alto-contraste-activo .modal-edit-content,
    body.alto-contraste-activo .form-group input,
    body.alto-contraste-activo .form-group select {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #555555 !important;
        color: #ffffff !important;
    }

    /* Saneamiento estructural de la tabla de facturación para que no borre las líneas */
    body.alto-contraste-activo .vendedor-table th {
        background-color: #3a3a3a !important;
        color: #b3b3b3 !important;
        border-bottom: 2px solid #555555 !important;
    }
    body.alto-contraste-activo .vendedor-table td {
        border-bottom: 1px solid #444444 !important;
        color: #ffffff !important;
    }

    /* Forzado de textos generales del panel de ventas */
    body.alto-contraste-activo h1,
    body.alto-contraste-activo h2,
    body.alto-contraste-activo h3,
    body.alto-contraste-activo p,
    body.alto-contraste-activo label,
    body.alto-contraste-activo span,
    body.alto-contraste-activo strong {
        color: #ffffff !important;
    }

    /* Resguardo táctico del Widget flotante de exclusión */
    body.alto-contraste-activo #panel-accesibilidad-global,
    body.alto-contraste-activo #panel-accesibilidad-global * {
        background-color: #ffffff !important;
        color: #111111 !important;
        border-color: #2d6f73 !important;
    }

    /* Tipografía Inclusiva: Verdana para baja visión tumba fuentes del archivo style.css */
    body.fuente-accesible-activa,
    body.fuente-accesible-activa * {
        font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
    }
    </style>
    </head>
<body>
    <!-- BARRA SUPERIOR CON FOTO DE PERFIL DEL VENDEDOR CORPORATIVO -->
    <header class="vendedor-header">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="assets/logo.png" alt="logo" style="width: 35px; height: 35px; object-fit: contain;">
            <h1 style="color: white; margin: 0; font-size: 20px; font-weight: bold;">TechNest Interno</h1>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="perfil-vendedor-box">
                <img src="<?php echo $avatar_vendedor; ?>" alt="Vendedor" class="avatar-vendedor-img">
                <span style="color: white; font-size: 14px; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['vendedor_nombre']); ?></span>
            </div>
            <a href="logout.php" style="background: #ff3b30; color: white; padding: 8px 14px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 12px; text-decoration: none;">Salir</a>
        </div>
    </header>
    <main class="vendedor-container">
        <!-- COLUMNA IZQUIERDA: REGISTRO DE TRANSACCIÓN MANUAL -->
        <section class="col-formulario">
            <!-- Botón de redirección limpio a la bandeja asíncrona -->
            <a href="vendedor_chat.php" class="btn-chat-link">
            Ir a Bandeja de Asistencia al Cliente (Gmail)
            </a>
            <h2 style="font-size: 18px; margin-top: 0; margin-bottom: 20px; color: #235457; font-weight: bold;">Registrar Venta Manual</h2>
            <?php echo $mensaje; ?>
            <form method="POST" action="">
            <div class="form-group">
                <label>Nombre del Cliente</label>
                <input type="text" name="nombre_cliente" required placeholder="Ej: Juan Mendoza">
            </div>
            <div class="form-group">
                <label>Seleccionar Producto (ID Sincronizado)</label>
                <select name="id_producto" required>
                    <option value="">-- Seleccione un artículo --</option>
                    <?php mysqli_data_seek($productos_res, 0); ?>
                    <?php while($prod = mysqli_fetch_assoc($productos_res)): ?>
                    <option value="<?php echo $prod['id']; ?>">
                    [ID: <?php echo $prod['id']; ?>] <?php echo htmlspecialchars($prod['nombre']); ?> (Stock: <?php echo $prod['stock']; ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" name="cantidad" min="1" value="1" required>
            </div>
            <button type="submit" name="registrar_venta" class="btn-submit">REGISTRAR TRANSACCIÓN</button>
            </form>
        </section>
            <!-- COLUMNA DERECHA: HISTORIAL DE TRANSACCIONES OPERATIVAS -->
        <section class="col-historial">
            <h2 style="font-size: 18px; margin-top: 0; margin-bottom: 20px; color: #333; font-weight: bold;">Mis Transacciones Recientes</h2>
            <div style="overflow-x: auto;">
                <table class="vendedor-table">
                    <thead>
                        <tr>
                        <th>ID Venta</th>
                        <th>Cliente</th>
                        <th>Artículo</th>
                        <th>Cant.</th>
                        <th>Total</th>
                        <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($historial_res) > 0): ?>
                        <?php while($venta = mysqli_fetch_assoc($historial_res)): ?>
                        <tr>
                        <td style="font-weight: bold;">#<?php echo $venta['id_venta']; ?></td>
                        <td><?php echo htmlspecialchars($venta['nombre_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($venta['producto_nombre']); ?></td>
                        <td style="text-align: center; font-weight: bold;"><?php echo $venta['cantidad']; ?></td>
                        <td style="color: #2d6f73; font-weight: bold;">$<?php echo number_format($venta['total'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;">
                        <!-- Envía los valores de la fila directamente a la ventana modal -->
                        <button onclick="abrirModalEdicion(<?php echo $venta['id_venta']; ?>, '<?php echo htmlspecialchars($venta['nombre_cliente'], ENT_QUOTES); ?>', <?php echo $venta['cantidad']; ?>)" style="background: #f2c300; border: none; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; cursor: pointer;">Editar</button>
                        </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                        <td colspan="6" style="text-align: center; color: #888; padding: 20px;">No has registrado transacciones manuales.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- FORMULARIO MODAL DE EDICIÓN FLOTANTE (CORRECCIÓN DE ERRORES) -->
    <div id="modal-edicion-venta" class="modal-edit">
        <div class="modal-edit-content">
            <h3 style="margin-top:0; margin-bottom: 20px; color: #235457; font-weight: bold;">Corregir Transacción</h3>
            <form method="POST" action="">
                <input type="hidden" id="id_venta_edit" name="id_venta_edit">
                <div class="form-group">
                    <label>Nombre del Cliente</label>
                    <input type="text" id="nuevo_nombre_cliente" name="nuevo_nombre_cliente" required>
                </div>
                <div class="form-group">
                    <label>Cantidad Real Vendida</label>
                    <input type="number" id="nueva_cantidad" name="nueva_cantidad" min="1" required>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" onclick="cerrarModalEdicion()" style="background:#eee; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:bold;">Cancelar</button>
                    <button type="submit" name="editar_venta" style="background:#235457; color:white; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:bold;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPTS CENTRALES DE CONTROL Y ACCESIBILIDAD UNIVERSAL --> 
    <script>
    // Carga los datos existentes en las cajas de texto de la ventana flotante
    function abrirModalEdicion(id, cliente, cantidad) {
        document.getElementById('id_venta_edit').value = id;
        document.getElementById('nuevo_nombre_cliente').value = cliente;
        document.getElementById('nueva_cantidad').value = cantidad;
        document.getElementById('modal-edicion-venta').style.display = 'flex';
    }
    // Oculta la ventana modal
    function cerrarModalEdicion() {
        document.getElementById('modal-edicion-venta').style.display = 'none';
    }

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
