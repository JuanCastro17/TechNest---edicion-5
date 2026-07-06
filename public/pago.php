<?php
// public/pago.php - PARTE 1 DE 4 (BACKEND DE PROCESAMIENTO)
session_start();

// CORRECCIÓN CAPAS: Salimos un nivel con /../ para ir a la carpeta config/ de la tienda
include(__DIR__ . '/../config/conexion.php');

// Bloque de respaldo para garantizar conectividad en el puerto 3307 de localhost
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// Filtro estricto de control: Si intenta entrar colado sin sesión, va a la selección de login
if (!isset($_SESSION['tienda_user'])) {
    header("Location: seleccion_login.php");
    exit();
}

// Cargamos las credenciales activas del comprador para la pasarela
$usuario_activo = $_SESSION['tienda_user'];
$id_usuario_activo = intval($_SESSION['id_usuario']);

// Escudo protector de avatar de triple validación unificada para Gato19
if (isset($_SESSION['tienda_avatar']) && !empty($_SESSION['tienda_avatar'])) {
    $avatar_crudo = $_SESSION['tienda_avatar'];
    if (strpos($avatar_crudo, 'assets/') === 0) {
        $avatar_activo = $avatar_crudo;
    } else {
        $avatar_activo = "assets/" . $avatar_crudo;
    }
    if (!file_exists(__DIR__ . "/" . $avatar_activo)) {
        $avatar_activo = 'assets/user.ico';
    }
} else {
    $avatar_activo = 'assets/user.ico';
}

$direccion_envio = isset($_SESSION['tienda_direccion']) && !empty($_SESSION['tienda_direccion']) ? $_SESSION['tienda_direccion'] : "";


//  MOTOR MULTI-INSERCIÓN REPARADO: Guarda una fila por cada artículo del carro

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ejecutar_transaccion'])) {
    header("Content-Type: application/json; charset=UTF-8");
    
    // Generamos un código de grupo único basado en tiempo para enlazar este lote de compra
    $codigo_grupo_compra = "TN_" . time() . "_" . rand(1000, 9999);
    $nombre_comprador = mysqli_real_escape_string($conexion, $usuario_activo);
    $json_carrito = isset($_POST['items_carrito_json']) ? $_POST['items_carrito_json'] : '';
    
    // Decodificamos la lista estructurada que viaja desde el Frontend de JavaScript
    $items = json_decode($json_carrito, true);

    if (!empty($items) && is_array($items)) {
        $error_detectado = false;
        $msg_error = "";
        
        foreach ($items as $item) {
            $id_prod = intval($item['id']);
            $cant = intval($item['cantidad']);
            $precio_unitario = floatval($item['precio']);
            $total_item = $precio_unitario * $cant;

            if ($id_prod > 0 && $cant > 0) {
                // REQUERIMIENTO CUMPLIDO: Insertamos una fila independiente por artículo amarrados al mismo grupo
                $query_insert = "INSERT INTO ventas (id_producto, id_usuario, codigo_grupo, cantidad, total, nombre_cliente, fecha_venta, id_vendedor) 
                                 VALUES ($id_prod, $id_usuario_activo, '$codigo_grupo_compra', $cant, $total_item, '$nombre_comprador', NOW(), 0)";
                
                if (!mysqli_query($conexion, $query_insert)) {
                    $error_detectado = true;
                    $msg_error = mysqli_error($conexion);
                    break;
                }
            }
        }

        if (!$error_detectado) {
            // Devolvemos el código de grupo común al Frontend de forma exitosa
            echo json_encode(["status" => "success", "grupo" => $codigo_grupo_compra]);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Fallo de inserción mixta: " . $msg_error]);
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "El carrito de compras está vacío o corrupto."]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>TechNest - Finalizar Compra</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/pago.css">
        <script src="js/script.js" defer></script>
        <link rel="icon" type="image/png" href="assets/logoicono.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <header>
            <div class="top-header">
                <div class="top-header-inner">
                    <div class="logo">
                        <img src="assets/logo.png" alt="Logo TechNest" class="logo-img">
                        <h1 class="logo-text">TechNest</h1>
                    </div>
                    <div class="search-wrapper">
                        <input type="text" id="buscador" class="search" placeholder="Buscar productos..." autocomplete="off">
                        <div id="search-sugerencias" class="search-sugerencias"></div>
                    </div>
                    <div class="right-section" style="display: flex; align-items: center; gap: 15px;">
                        <div class="UserSection" id="btn-avatar-click" style="position: relative;">
                            <img src="<?php echo $avatar_activo; ?>" alt="Perfil" class="avatar" onclick="toggleMenuPerfil(event)" style="cursor: pointer; width:36px; height:36px; border-radius:50%; object-fit:cover;">
                            <a href="#" class="user" onclick="toggleMenuPerfil(event)"><?php echo htmlspecialchars($usuario_activo); ?></a>
                            <div class="carrito-panel" id="perfil-panel">
                            <div class="notificaciones-header">
                                <span>Mi perfil</span>
                                <button onclick="cerrarPerfil()" class="cerrar-panel-btn">✕</button>
                            </div>
                            <div class="notificaciones-body">
                                <p style="font-weight: bold; margin-bottom: 5px; color: #333; font-size: 16px;">Cuenta Activa</p>
                                <hr style="border: none; border-top: 1px solid #eee; margin: 10px 0 15px 0;">
                                <a href="perfil.php" style="color: #2d6f73; text-decoration: none; font-weight: bold; font-size: 15px; display: block; margin-bottom: 12px;">Configurar perfil</a>
                                <a href="logout.php" style="color: #c0392b; text-decoration: none; font-weight: bold; font-size: 15px; display: block;">Cerrar sesión</a>
                            </div>
                        </div>
                    </div>
                    <a href="carrito.php" class="shopping">Mis compras</a>
                        <div style="position: relative; display: inline-block;">
                        <div class="notificacion-contenedor">
                            <a href="#" id="btn-campana" onclick="toggleNotificaciones(event)">
                            <img src="assets/campana.png" alt="notifi" class="icono">
                            </a>
                            <span class="punto-notificacion" id="punto-alerta"></span>
                        </div>
                        <div class="notificaciones-panel" id="notificaciones-panel">
                            <div class="notificaciones-header">
                                <span>Notificaciones</span>
                                <button onclick="cerrarNotificaciones()">✕</button>
                                </div>
                                <div class="notificaciones-body">
                                    <p style="margin:0;">No tienes notificaciones nuevas</p>
                                </div>
                            </div>
                        </div>
                        <div style="position: relative; display: inline-block;">
                            <a href="#" id="btn-carrito" onclick="toggleCarrito(event)">
                            <img src="assets/carrito.png" alt="shop-car" class="icono_dos">
                            </a>
                            <div class="carrito-panel" id="carrito-panel">
                                <div class="notificaciones-header">
                                    <span>Carrito de compras</span>
                                    <button onclick="cerrarCarrito()">✕</button>
                                </div>
                                <div class="notificaciones-body">
                                    <p style="margin:0;">Tu carrito está vacío</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <div class="sidabar-wrapper">
                <div class="sidabar">
                    <a href="#">Explorar Catalogo</a>
                    <a href="#">Ofertas</a>
                    <a href="index.php">Inicio</a>
                    <a href="ayuda.php">Ayuda / PQR</a>
                    <a href="#">Enviar a <?php echo htmlspecialchars($usuario_activo); ?></a>
                </div>
            </div>
        </header>
        <main class="pago-container" style="font-family: sans-serif;">
            <!-- COLUMNA IZQUIERDA: REVISIÓN DE ENTREGA Y MÉTODOS DE PAGO COLOMBIANOS -->
            <div class="seccion-entrega">
                
                <!-- Bloque 1: Forma de entrega original idéntica a tu captura -->
                <div class="opcion-envio" style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <h2 style="font-size: 18px; color: #111; margin-top: 0; margin-bottom: 15px; font-weight: bold; font-family: sans-serif;">Revisa la forma de entrega</h2>
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        <div class="radio-container"><input type="radio" checked name="envio"></div>
                        <div class="info-direccion" style="text-align: left; flex: 1;">
                            <div class="header-envio" style="display: flex; justify-content: space-between; font-weight: bold; font-size: 15px;">
                                <span class="metodo">Enviar a domicilio</span>
                                <span class="costo-gratis" style="color: #009e49;">Gratis</span>
                            </div>
                            <p id="display-direccion" style="margin: 5px 0; color: #111; font-weight: bold; font-size: 14px;">Cargando dirección...</p>
                            <p id="display-descripcion" style="margin: 0 0 10px 0; color: #666; font-size: 12px;">Cargando detalles...</p>
                            <a href="#" class="modificar" onclick="abrirModal(event)" style="color: #2d6f73; font-weight: bold; text-decoration: none; font-size: 13px;">Modificar domicilio o elegir otro</a>
                        </div>
                    </div>
                </div>

                <!-- REQUERIMIENTO CUMPLIDO - Bloque 2: Métodos de Pago en Lista Justo Debajo del Domicilio e Igual de Grande -->
                <div class="opcion-envio" style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left;">
                    <h2 style="font-size: 18px; color: #111; margin-top: 0; margin-bottom: 5px; font-weight: bold; font-family: sans-serif;">Selecciona tu método de pago</h2>
                    <p style="color: #666; font-size: 12px; margin-top: 0; margin-bottom: 15px;">Selecciona la entidad o corresponsal bancario donde deseas facturar la compra.</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid #eee; border-radius: 6px; cursor: pointer; background: #fafafa; transition: background 0.15s;">
                            <input type="radio" name="metodo_pago_seleccionado" value="PSE" style="accent-color: #2d6f73;">
                            <span style="font-weight: 600; color: #333; font-size: 14px;"> PSE (Pago Seguro en Línea)</span>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid #eee; border-radius: 6px; cursor: pointer; background: #fafafa; transition: background 0.15s;">
                            <input type="radio" name="metodo_pago_seleccionado" value="Efecty" style="accent-color: #2d6f73;">
                            <span style="font-weight: 600; color: #333; font-size: 14px;"> Efecty (Corresponsal Bancario)</span>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid #eee; border-radius: 6px; cursor: pointer; background: #fafafa; transition: background 0.15s;">
                            <input type="radio" name="metodo_pago_seleccionado" value="Vía Baloto" style="accent-color: #2d6f73;">
                            <span style="font-weight: 600; color: #333; font-size: 14px;"> Vía Baloto</span>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid #eee; border-radius: 6px; cursor: pointer; background: #fafafa; transition: background 0.15s;">
                            <input type="radio" name="metodo_pago_seleccionado" value="Puntos Redeban" style="accent-color: #2d6f73;">
                            <span style="font-weight: 600; color: #333; font-size: 14px;"> Puntos Redeban</span>
                        </label>
                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA: RESUMEN FINANCIERO -->
            <aside class="resumen-compra">
                <div class="resumen-card" style="background: white; border: 1px solid #ccc; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; font-size: 16px; font-weight: bold; color: #111; margin-bottom: 15px;">Resumen de compra</h3>
                    <div class="resumen-fila" style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; color: #555;">
                        <span>Producto</span>
                        <span id="precio-producto">$ 0</span>
                    </div>
                    <div class="resumen-fila" style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; color: #555;">
                        <span>Envío</span>
                        <span class="costo-gratis" style="color: #009e49; font-weight: bold;">Gratis</span>
                    </div>
                    <hr class="separador" style="border: none; border-top: 1px solid #eee; margin: 15px 0;">
                    <div class="resumen-fila total" style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; color: #111; margin-bottom: 15px;">
                        <span>Total</span>
                        <span id="precio-total">$ 0</span>
                    </div>

                    <!-- ENTRADAS TRANSACCIONALES CONTROLADAS -->
                    <input type="hidden" id="db_id_producto" value="0">
                    <input type="hidden" id="db_cantidad" value="0">
                    <input type="hidden" id="db_total" value="0">
                    
                    <button type="button" class="btn-continuar" id="btn-procesar-pago" style="background-color: #3b82f6; border-radius: 6px;">Continuar</button>
                </div>
            </aside>
        </main>

        <div id="modal-direccion" class="modal">
            <div class="modal-content">
                <h3>Datos de entrega</h3>
                <div class="form-group">
                    <label>Dirección exacta:</label>
                    <input type="text" id="input-direccion" placeholder="Ej: Calle 10 # 5-20">
                </div>
                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea id="input-descripcion" placeholder="Ej: Apto 201..."></textarea>
                </div>
                <div class="modal-botones">
                    <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                    <button class="btn-guardar" onclick="guardarDireccion()">Guardar</button>
                </div>
            </div>
        </div>

        <!-- ESCUDO DE ESTILOS CSS REPARADO PARA EVITAR LOS BUGS DE CONTRASTE Y TIPOGRAFÍA -->
        <style>
            /*  CONTRASTE SEGURO: Cambia solo el fondo general y las tarjetas sin desarmar las grillas */
            body.alto-contraste-activo {
                background-color: #141414 !important;
                color: #ffffff !important;
            }
            
            /* Modificamos selectivamente tus bloques de pago sin alterar anchos, márgenes ni floats */
            body.alto-contraste-activo main,
            body.alto-contraste-activo .seccion-entrega,
            body.alto-contraste-activo aside,
            body.alto-contraste-activo .opcion-envio,
            body.alto-contraste-activo .resumen-card,
            body.alto-contraste-activo .tarjeta-blanca,
            body.alto-contraste-activo .modal-content {
                background-color: #222222 !important;
                background: #222222 !important;
                border-color: #444444 !important;
            }
            
            /* Forzamos de forma estricta a los textos a volverse blanco brillante */
            body.alto-contraste-activo h1,
            body.alto-contraste-activo h2,
            body.alto-contraste-activo h3,
            body.alto-contraste-activo p,
            body.alto-contraste-activo span,
            body.alto-contraste-activo label,
            body.alto-contraste-activo a,
            body.alto-contraste-activo td,
            body.alto-contraste-activo th {
                color: #ffffff !important;
            }
            
            /* El label de los métodos de pago adopta un fondo grafito para que resalten las letras */
            body.alto-contraste-activo .opcion-envio label {
                background-color: #333333 !important;
                border-color: #555555 !important;
            }
            
            /* Evita que los botones del widget se vuelvan invisibles en modo oscuro */
            body.alto-contraste-activo #panel-accesibilidad-global,
            body.alto-contraste-activo #panel-accesibilidad-global * {
                background-color: #ffffff !important;
                color: #111111 !important;
                border-color: #2d6f73 !important;
            }

            /*  TIPOGRAFÍA UNIVERSAL: Forzado estricto sobre el comodín * cuando la clase esté encendida */
            body.fuente-accesible-activa,
            body.fuente-accesible-activa * {
                font-family: Arial, Helvetica, sans-serif !important;
            }
        </style>

        <!-- SCRIPTS DE VALIDACIÓN, CONTROLADOR TRANSACCIONAL Y ACCESIBILIDAD UNIVERSAL -->
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const id = params.get('id');
            
            let subtotalProductos = 0;
            let costoEnvio = 0;
            itemsParaGuardar = []; // Inicializador del payload global

            if (id && !isNaN(id)) {
                // Flujo 1: Compra directa de un solo producto desde la ficha técnica
                fetch(`api/consultar.php?id=${id}`)
                    .then(res => res.json())
                    .then(producto => {
                        if (producto && producto.precio) {
                            let precioNumerico = 0;
                            if (typeof producto.precio === 'string') {
                                let textoLimpio = producto.precio.replace(/[^\d]/g, "");
                                precioNumerico = parseFloat(textoLimpio);
                                if (textoLimpio.length >= 7 && (producto.precio.includes('.') || producto.precio.includes(','))) {
                                    if (precioNumerico > 50000000) { precioNumerico = precioNumerico / 100; }
                                }
                            } else {
                                precioNumerico = parseFloat(producto.precio);
                            }

                            subtotalProductos = precioNumerico;
                            costoEnvio = 0; 
                            itemsParaGuardar.push({ id: parseInt(producto.id), cantidad: 1, precio: precioNumerico });
                            inyectarPreciosEnPantalla(subtotalProductos, costoEnvio);
                        } else {
                            cargarDesdeCarritoLocal();
                        }
                    })
                    .catch(() => cargarDesdeCarritoLocal());
            } else {
                cargarDesdeCarritoLocal();
            }

            function cargarDesdeCarritoLocal() {
                const carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
                if (carrito.length === 0) { inyectarPreciosEnPantalla(0, 0); return; }

                subtotalProductos = 0;
                let totalUnidades = 0;
                itemsParaGuardar = []; 
                
                carrito.forEach(prod => {
                    const cantidad = parseInt(prod.cantidad || 1);
                    totalUnidades += cantidad;
                    let precioNumerico = 0;
                    if (typeof prod.precio === 'string') {
                        let textoLimpio = prod.precio.replace(/[^\d]/g, "");
                        precioNumerico = parseFloat(textoLimpio);
                        if (textoLimpio.length >= 8 && precioNumerico > 50000000) { precioNumerico = precioNumerico / 100; }
                    } else {
                        precioNumerico = parseFloat(prod.precio);
                    }
                    subtotalProductos += (precioNumerico * cantidad);
                    itemsParaGuardar.push({ id: parseInt(prod.id), cantidad: cantidad, precio: precioNumerico });
                });

                costoEnvio = (totalUnidades > 1) ? 12000 : 0;
                inyectarPreciosEnPantalla(subtotalProductos, costoEnvio);
            }
            function inyectarPreciosEnPantalla(subtotal, envio) {
                if (subtotal > 50000000) { subtotal = subtotal / 100; }
                const totalFinal = subtotal + envio;

                const elPrecioProducto = document.getElementById('precio-producto');
                const elPrecioTotal = document.getElementById('precio-total');
                const elDbId = document.getElementById('db_id_producto');
                const elDbCant = document.getElementById('db_cantidad');
                const elDbTotal = document.getElementById('db_total');

                if (subtotal > 0) {
                    if (elPrecioProducto) elPrecioProducto.textContent = `$ ${subtotal.toLocaleString('es-CO')}`;
                    
                    const etiquetasEnvio = document.querySelectorAll('.costo-gratis');
                    etiquetasEnvio.forEach(etiqueta => {
                        if (envio > 0) {
                            etiqueta.textContent = `$ ${envio.toLocaleString('es-CO')}`;
                            etiqueta.style.color = '#333'; 
                        } else {
                            etiqueta.textContent = "Gratis";
                            etiqueta.style.color = ""; 
                        }
                    });
                    if (elPrecioTotal) elPrecioTotal.textContent = `$ ${totalFinal.toLocaleString('es-CO')}`;
                    
                    // Inyección blindada validando la existencia física de las cajas ocultas del DOM
                    if (elDbId) elDbId.value = itemsParaGuardar.length > 0 ? itemsParaGuardar[0].id : 0;
                    if (elDbCant) elDbCant.value = itemsParaGuardar.reduce((acc, curr) => acc + curr.cantidad, 0);
                    if (elDbTotal) elDbTotal.value = totalFinal;
                } else {
                    if (elPrecioProducto) elPrecioProducto.textContent = "$ 0";
                    if (elPrecioTotal) elPrecioTotal.textContent = "$ 0";
                }
            }

            const phpDireccion = "<?php echo $direccion_envio; ?>";
            let dir = phpDireccion;
            let desc = "Detalles de cuenta activos";
            if (dir === "") {
                dir = localStorage.getItem('user_direccion') || "Sin dirección asignada";
                desc = localStorage.getItem('user_descripcion') || "Haz clic abajo para añadir detalles";
            }
            
            const elDispDir = document.getElementById('display-direccion');
            const elDispDesc = document.getElementById('display-descripcion');
            if (elDispDir) elDispDir.textContent = dir;
            if (elDispDesc) elDispDesc.textContent = desc;

            // INTERCEPTOR CON VALIDACIÓN DE MÉTODOS DE PAGO COLOMBIANOS
            document.getElementById('btn-procesar-pago').addEventListener('click', function(e) {
                e.preventDefault(); 
                
                const metodoSeleccionado = document.querySelector('input[name="metodo_pago_seleccionado"]:checked');
                if (!metodoSeleccionado) {
                    alert("Por favor, selecciona un método de pago de la lista (PSE, Efecty, Vía Baloto o Redeban) antes de continuar.");
                    return;
                }

                if (itemsParaGuardar.length === 0) { alert("No tienes productos válidos para procesar la compra."); return; }
                const direccionActual = document.getElementById('display-direccion').textContent.trim();
                if (direccionActual === "Sin dirección asignada" || direccionActual === "") {
                    alert("Por favor, asigna una dirección de entrega."); abrirModal(); return;
                }

                const totalTexto = document.getElementById('precio-total').textContent;
                const ahora = new Date();
                const horaFormateada = ahora.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const mensajeNotificacion = `Pedido por ${totalTexto} registrado vía ${metodoSeleccionado.value}. Esperando confirmación bancaria.`;

                let notificaciones = JSON.parse(localStorage.getItem('technest_notificaciones')) || [];
                notificaciones.unshift({ texto: mensajeNotificacion, fecha: horaFormateada });
                localStorage.setItem('technest_notificaciones', JSON.stringify(notificaciones));
                localStorage.setItem('technest_nuevas_notificaciones', 'true');

                const datosEnvio = new FormData();
                datosEnvio.append('items_carrito_json', JSON.stringify(itemsParaGuardar));
                datosEnvio.append('ejecutar_transaccion', '1');

                fetch('pago.php', { method: 'POST', body: datosEnvio })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        localStorage.removeItem('technest_carrito');
                        window.location.href = `confirmacion.php?precio=${encodeURIComponent(totalTexto)}&grupo=${data.grupo}`;
                    } else {
                        alert("Error al procesar la inserción en la base de datos: " + data.message);
                    }
                }).catch(() => alert("Ocurrió un error de comunicación con el servidor al intentar guardar tu pedido."));
            });
            
            // ACCESIBILIDAD UNIVERSAL - MOTOR DE CONTROL MASIVO SOBRE EL BODY
            
            const widgetAccesible = document.createElement('div');
            widgetAccesible.id = 'panel-accesibilidad-global';
            widgetAccesible.style = "position: fixed; bottom: 85px; right: 20px; background: #ffffff; border: 2px solid #2d6f73; border-radius: 12px; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); z-index: 1000000; font-family: sans-serif; display: flex; flex-direction: column; gap: 10px; width: 180px;";
            
            // Formato sobrio y profesional sin emojis para tu entorno corporativo
            widgetAccesible.innerHTML = `
                <span style="font-weight: bold; color: #2d6f73; font-size: 13px; text-align: center; display: block; border-bottom: 1px solid #eee; padding-bottom: 5px; font-family: sans-serif;">ACCESIBILIDAD</span>
                <button id="btn-zoom-mas" style="background: #2d6f73; color: white; border: none; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Zoom +</button>
                <button id="btn-zoom-menos" style="background: #f2f5f6; color: #333; border: 1px solid #ccc; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Zoom -</button>
                <button id="btn-toggle-contraste" style="background: #111111; color: white; border: none; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Contraste</button>
                <button id="btn-toggle-fuente" style="background: #f2c300; color: #111; border: none; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase; font-family: sans-serif;">Tipografía</button>
            `;
            document.body.appendChild(widgetAccesible);

            // Escalamiento de Zoom Real: Transformación proporcional de la vista completa
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

                // 1. ALTO CONTRASTE CORREGIDO: Cambia a gris oscuro y fuerza textos en blanco brillante
            let modoContrasteActivo = false;
            document.getElementById('btn-toggle-contraste').addEventListener('click', () => {
                modoContrasteActivo = !modoContrasteActivo;
                
                if (modoContrasteActivo) {
                    // Fondo general del body
                    document.body.style.setProperty('background-color', '#1a1a1a', 'important');
                    document.body.style.setProperty('color', '#ffffff', 'important');
                    
                    // Transformamos tus tarjetas blancas (envío, métodos de pago y resumen) a un tono grafito legible
                    document.querySelectorAll('.opcion-envio, .resumen-card, .tarjeta-blanca, .modal-content').forEach(el => {
                        el.style.setProperty('background-color', '#2d2d2d', 'important');
                        el.style.setProperty('background', '#2d2d2d', 'important');
                        el.style.setProperty('border-color', '#555555', 'important');
                    });
                    
                    // Forzamos a TODOS los textos internos a volverse blanco nítido
                    document.querySelectorAll('h1, h2, h3, p, span, label, a, td, th').forEach(el => {
                        // Protegemos el panel de accesibilidad para que mantenga sus colores de botones originales
                        if (!el.closest('#panel-accesibilidad-global')) {
                            el.style.setProperty('color', '#ffffff', 'important');
                        }
                    });
                    
                    // Los recuadros de la lista de pagos pasan a un fondo oscuro suave
                    document.querySelectorAll('.opcion-envio label').forEach(el => {
                        el.style.setProperty('background-color', '#3a3a3a', 'important');
                        el.style.setProperty('border-color', '#555555', 'important');
                    });
                    
                    // Ajustamos el Widget flotante para que se mantenga perfectamente visible con fondo blanco
                    const panelAcc = document.getElementById('panel-accesibilidad-global');
                    if (panelAcc) {
                        panelAcc.style.setProperty('background-color', '#ffffff', 'important');
                        panelAcc.querySelectorAll('*').forEach(child => {
                            child.style.setProperty('color', '#111111', 'important');
                            child.style.setProperty('background-color', '#ffffff', 'important');
                        });
                    }
                } else {
                    // RESTAURACIÓN TOTAL: Quitamos las propiedades inline para volver al diseño original
                    document.body.style.removeProperty('background-color');
                    document.body.style.removeProperty('color');
                    
                    document.querySelectorAll('.opcion-envio, .resumen-card, .tarjeta-blanca, .modal-content').forEach(el => {
                        el.style.removeProperty('background-color');
                        el.style.removeProperty('background');
                        el.style.removeProperty('border-color');
                    });
                    
                    document.querySelectorAll('h1, h2, h3, p, span, label, a, td, th').forEach(el => {
                        el.style.removeProperty('color');
                    });
                    
                    document.querySelectorAll('.opcion-envio label').forEach(el => {
                        el.style.removeProperty('background-color');
                        el.style.removeProperty('border-color');
                    });

                    // Refrescamos visualmente las etiquetas de precios nativas
                    if (typeof inyectarPreciosEnPantalla === 'function') {
                        inyectarPreciosEnPantalla(subtotalProductos, costoEnvio);
                    }
                }
            });

            // 2. TIPOGRAFÍA REPARADA: Inyección dinámica de hoja de estilos global
                // 2. TIPOGRAFÍA REPARADA: Sincronizada con el ID real de tu botón del Widget
                // 2. TIPOGRAFÍA REPARADA: Inyección absoluta con comillas invertidas de fuerza
            let fuenteAccesibleActiva = false;
            let estiloFuentesDinamico = null;

            document.getElementById('btn-toggle-fuente').addEventListener('click', () => {
                fuenteAccesibleActiva = !fuenteAccesibleActiva;
                
                if (fuenteAccesibleActiva) {
                    // Creamos la etiqueta style en caliente en la memoria
                    estiloFuentesDinamico = document.createElement('style');
                    estiloFuentesDinamico.id = 'fuente-accesible-forzado';
                    
                    // IMPORTANTE: Usamos comillas invertidas (backticks) para que el navegador procese el CSS real
                    estiloFuentesDinamico.innerHTML = `
                        * { 
                            font-family: 'Verdana', 'Trebuchet MS', sans-serif !important; 
                            letter-spacing: 0.5px !important; 
                        }
                    `;
                    
                    // Lo incrustamos al inicio del head para que domine todo tu catálogo
                    document.head.appendChild(estiloFuentesDinamico);
                } else {
                    // Si la vuelven a presionar, removemos la regla y todo vuelve al Oswald original
                    if (estiloFuentesDinamico) {
                        estiloFuentesDinamico.remove();
                        estiloFuentesDinamico = null;
                    }
                }
            });



        });

        // MANIPULADORES DE VENTANAS MODALES DE DOMICILIO
        function abrirModal(e) { 
            if(e) e.preventDefault(); 
            document.getElementById('modal-direccion').style.display = 'flex'; 
        }

        function cerrarModal() { 
            document.getElementById('modal-direccion').style.display = 'none'; 
        }

        function guardarDireccion() {
            const dir = document.getElementById('input-direccion').value;
            const desc = document.getElementById('input-descripcion').value;
            
            if(dir.trim() === "") {
                return alert("La dirección es obligatoria");
            }
            
            document.getElementById('display-direccion').textContent = dir;
            document.getElementById('display-descripcion').textContent = desc;
            
            localStorage.setItem('user_direccion', dir); 
            localStorage.setItem('user_descripcion', desc); 
            cerrarModal();
        }
        </script>
    </body>
</html>
