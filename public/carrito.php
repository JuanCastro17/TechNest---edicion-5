<?php
// public/carrito.php - PARTE 1 DE 3 (LOGICA PHP Y HOJAS DE ESTILO)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORRECCIÓN CAPAS: Como estás en public/, salimos un nivel con /../ para ir a la carpeta config/
include(__DIR__ . '/../config/conexion.php');

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// Validar si el usuario NO ha iniciado sesión en la tienda
if (!isset($_SESSION['tienda_user'])) {
    echo "<script>
    alert('Primeramente necesitas iniciar sesión para acceder a tus compras.');
    window.location.href = 'seleccion_login.php';
    </script>";
    exit(); 
}

// Sincronización EXACTA con las llaves de sesión y escudo de avatar
$usuario_activo = $_SESSION['tienda_user'];
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TechNest - Mis Compras</title>
    <!-- Vinculación limpia con tus hojas de estilo y scripts unificados -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/carrito.css">
    <link rel="icon" type="image/png" href="assets/logoicono.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        
        /*  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON CESTA DE COMPRAS */
        
        /* Contraste Seguro: Fuerza el fondo de la cesta mitigando parches rígidos */
        body.alto-contraste-activo {
            background-color: #141414 !important;
            color: #ffffff !important;
        }
        body.alto-contraste-activo main,
        body.alto-contraste-activo header,
        body.alto-contraste-activo .carrito-container {
            background-color: #141414 !important;
        }

        /*  REQUERIMIENTO CUMPLIDO: Forzamos el bloque del checkbox superior a tornarse grafito oscuro */
        body.alto-contraste-activo .seleccion-todos,
        body.alto-contraste-activo .producto-card-carrito,
        body.alto-contraste-activo .resumen-flotante,
        body.alto-contraste-activo .carrito-panel,
        body.alto-contraste-activo .notificaciones-panel {
            background-color: #2d2d2d !important;
            background: #2d2d2d !important;
            border-color: #555555 !important;
            color: #ffffff !important;
        }

        /* 🛠️ CORRECCIÓN ENCABEZADO: Preservamos los colores y grillas originales de la barra superior */
        body.alto-contraste-activo .top-header,
        body.alto-contraste-activo .sidabar-wrapper,
        body.alto-contraste-activo .sidabar {
            background-color: #2d6f73 !important; /* Mantiene tu hermoso verde institucional */
        }
        body.alto-contraste-activo .sidabar a {
            color: #ffffff !important;
        }
        body.alto-contraste-activo .sidabar a:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
        }

        /* Saneamiento de las imágenes de los artículos dentro de la cuadrícula de la cesta */
        body.alto-contraste-activo .producto-card-carrito img {
            background-color: #3a3a3a !important;
            border: 1px solid #555555 !important;
        }

        /* Forzado estricto de textos para evitar el desvanecimiento negro */
        body.alto-contraste-activo h1,
        body.alto-contraste-activo h2,
        body.alto-contraste-activo h3,
        body.alto-contraste-activo p,
        body.alto-contraste-activo span,
        body.alto-contraste-activo label,
        body.alto-contraste-activo a {
            color: #ffffff !important;
        }

        /* Los botones nativos de cantidad (+ y -) adoptan un relieve claro legible */
        body.alto-contraste-activo .controles-cantidad button {
            background-color: #444444 !important;
            color: #ffffff !important;
            border-color: #666666 !important;
        }
        body.alto-contraste-activo .controles-cantidad input {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border-color: #666666 !important;
        }

        /* Marcación nítida del precio total del lateral */
        body.alto-contraste-activo .prod-precio,
        body.alto-contraste-activo .total-monto {
            color: #64b5f6 !important;
        }

        /* Resguardo del panel flotante del Widget */
        body.alto-contraste-activo #panel-accesibilidad-global,
        body.alto-contraste-activo #panel-accesibilidad-global * {
            background-color: #ffffff !important;
            color: #111111 !important;
            border-color: #2d6f73 !important;
        }

        /* Tipografía Inclusiva: Verdana de baja visión domina sobre el catálogo */
        body.fuente-accesible-activa,
        body.fuente-accesible-activa * {
            font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
        }
    </style>

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
        <input type="text" class="search" id="buscador" placeholder="Buscar productos..." autocomplete="off">
        <div class="search-sugerencias" id="search-sugerencias"></div>
        </div>
        <div class="right-section" style="display: flex; align-items: center; gap: 15px;">
        <div class="UserSection" id="btn-avatar-click" style="position: relative;">
        <!-- El avatar ahora lee de forma obligatoria la ruta blindada de assets -->
        <img src="<?php echo htmlspecialchars($avatar_activo); ?>" alt="Perfil" class="avatar" onclick="toggleMenuPerfil(event)" style="cursor: pointer; width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
        <a href="#" class="user" onclick="toggleMenuPerfil(event)"><?php echo htmlspecialchars($usuario_activo); ?></a>

        <!-- 1. VENTANA DE PERFIL LIMPIA Y ACTIVA -->
        <div id="perfil-panel" class="carrito-panel">
        <div class="notificaciones-header">
        <span>Mi perfil</span>
        <button onclick="cerrarPerfil()" class="cerrar-panel-btn">✕</button>
        </div>
        <div class="notificaciones-body">
        <span class="perfil-status">Cuenta Activa</span>
        <hr class="perfil-divisor">
        <a href="perfil.php" class="sugerencia-item">Configurar perfil</a>
        <a href="logout.php" class="sugerencia-item cerrar-sesion-btn">Cerrar sesión</a>
        </div>
        </div>
        </div>
        <a href="carrito.php" class="shopping">Mis compras</a>
        <!-- 2. VENTANA DE NOTIFICACIONES REPARADA -->
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
        <!-- 3. VENTANA DE CARRITO FLOTANTE REPARADA -->
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
                <a href="#"><?php echo "Enviar a " . htmlspecialchars($usuario_activo); ?></a>
            </div>
        </div>
    </header>
    <main class="carrito-container">
        <div class="seleccion-todos">
            <input type="checkbox" id="check-todos" checked onclick="toggleTodos(this)">
            <label for="check-todos">Todos los productos</label>
        </div>
        <div class="productos-grid" id="lista-carrito"></div>
        <div class="resumen-flotante">
            <div class="resumen-detalle">
                <h3>Resumen de compra</h3>
                <div class="resumen-linea">
                    <span id="label-productos">Productos (0)</span>
                    <span id="subtotal-valor">$ 0</span>
                </div>
                <div class="resumen-linea">
                    <span>Envío</span>
                    <span id="envio-valor">Gratis</span>
                </div>
            </div>
                <div class="total-seccion">
                    <span class="total-label">Total</span>
                    <span class="total-monto" id="total-final">$ 0</span>
                </div>
            <button class="btn-continuar-compra" onclick="irAPagar()">CONTINUAR COMPRA</button>
        </div>
    </main>
    <!-- ESCUDO DE PROTECCIÓN CONTRA TEXTOS SUELTOS EN COMPUTADORAS -->
    <div class="wrapper-menu-movil-bloqueado">
        <nav class="bottom-nav">
            <a href="index.php" class="bottom-nav-item">
                <img src="assets/carrito.png" alt="Catálogo" class="bottom-nav-icon">
                <span>Catálogo</span>
            </a>
            <a href="#" class="bottom-nav-item bottom-nav-center" onclick="toggleMenuPerfil(event)">
                <img src="<?php echo htmlspecialchars($avatar_activo); ?>" alt="Perfil" class="bottom-nav-avatar">
                <span><?php echo htmlspecialchars($usuario_activo); ?></span>
            </a>
            <a href="#" class="bottom-nav-item">
                <span class="bottom-nav-badge">%</span>
                <span>Ofertas</span>
            </a>
        </nav>
    </div>
        <!-- MOTOR DE SINCRONIZACIÓN COMERCIAL Y ACCESIBILIDAD-->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                renderizarCarrito();
            });

            // LÓGICA NATIVA DE RENDERIZACIÓN DEL CARRITO GLOBAL
            function renderizarCarrito() {
                const contenedor = document.getElementById('lista-carrito');
                const carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
                if (carrito.length === 0) {
                    contenedor.innerHTML = "<p style='padding:20px; font-weight:bold; color:#777;'>No hay productos en tu carrito.</p>";
                    actualizarTotales(0, 0);
                    return;
                }
                contenedor.innerHTML = '';
                let subtotal = 0;
                let totalProductos = 0;
                carrito.forEach((prod, index) => {
                    if (!prod.cantidad) prod.cantidad = 1;
                    const precioNumerico = typeof prod.precio === 'string' ? parseFloat(prod.precio.replace(/[^\d]/g, "")) : prod.precio;
                    subtotal += (precioNumerico * prod.cantidad);
                    totalProductos += prod.cantidad;
                    
                    let urlImagen = '../src/productos/default.jpg';
                    if (prod.imagen) {
                        const nombreLimpio = prod.imagen.replace('assets/', '').replace('../src/productos/', '');
                        urlImagen = `../src/productos/${nombreLimpio}`;
                    }
                    const formatoPrecioRow = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(precioNumerico);
                    const card = document.createElement('div');
                    card.className = 'producto-card-carrito';
                    card.style = "display: flex; align-items: center; gap: 15px; background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);";
                    card.innerHTML = `
                        <input type="checkbox" class="check-item" checked data-index="${index}" onclick="recalcularSeleccionados()">
                        <img src="${urlImagen}" alt="${prod.nombre}" style="width: 80px; height: 80px; object-fit: contain; background:#fafafa; border-radius:6px;">
                        <div style="flex: 1;">
                            <p class="prod-nombre" style="font-weight: bold; margin: 0 0 5px 0; font-size:14px; text-align:left; color:#333;">${prod.nombre}</p>
                            <p class="prod-precio" style="color: #2d6f73; font-weight: bold; margin: 0; text-align:left;">${formatoPrecioRow}</p>
                        </div>
                        <div class="controles-cantidad" style="display: flex; align-items: center; gap: 5px;">
                            <button onclick="cambiarCant(${index}, -1)" style="padding: 5px 10px; cursor:pointer; font-weight:bold; border:1px solid #ccc; background:#f8fafb; border-radius:4px;">-</button>
                            <input type="text" value="${prod.cantidad}" readonly style="width: 35px; text-align: center; border: 1px solid #ccc; padding:4px 0; border-radius:4px; font-weight:bold;">
                            <button onclick="cambiarCant(${index}, 1)" style="padding: 5px 10px; cursor:pointer; font-weight:bold; border:1px solid #ccc; background:#f8fafb; border-radius:4px;">+</button>
                            <button class="btn-eliminar" onclick="eliminarItem(${index})" style="background: none; border: none; color: #c0392b; cursor: pointer; margin-left: 15px; font-size:16px;">🗑</button>
                        </div>
                    `;
                    contenedor.appendChild(card);
                });
                actualizarTotales(subtotal, totalProductos);
            }
        // MOTOR DE OPERACIONES COMERCIALES 
                // 🛠️ REQUERIMIENTO CUMPLIDO: INTERCEPTORES DE DISPLAY DIRECTOS DE TECHNEST
        function toggleMenuPerfil(e) {
            if(e) { e.preventDefault(); e.stopPropagation(); }
            const panel = document.getElementById('perfil-panel');
            if (panel) {
                panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
            }
            cerrarNotificaciones(); cerrarCarrito();
        }

        function cerrarPerfil() {
            const panel = document.getElementById('perfil-panel');
            if (panel) panel.style.display = 'none';
        }

        function toggleNotificaciones(e) {
            if(e) { e.preventDefault(); e.stopPropagation(); }
            const panel = document.getElementById('notificaciones-panel');
            if (panel) {
                panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
            }
            cerrarPerfil(); cerrarCarrito();
        }

        function cerrarNotificaciones() {
            const panel = document.getElementById('notificaciones-panel');
            if (panel) panel.style.display = 'none';
        }

        function toggleCarrito(e) {
            if(e) { e.preventDefault(); e.stopPropagation(); }
            const panel = document.getElementById('carrito-panel');
            if (panel) {
                panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
            }
            cerrarPerfil(); cerrarNotificaciones();
        }

        function cerrarCarrito() {
            const panel = document.getElementById('carrito-panel');
            if (panel) panel.style.display = 'none';
        }

        // Cierre general: Si hacen clic en cualquier zona vacía del DOM, se ocultan todas solas
        document.addEventListener('click', () => {
            cerrarPerfil(); cerrarNotificaciones(); cerrarCarrito();
        });

        // MOTOR DE OPERACIONES FINANCIERAS DE LA CESTA NATIVAS
        function cambiarCant(index, cambio) {
            let carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
            if(carrito[index]) {
                if(!carrito[index].cantidad) carrito[index].cantidad = 1;
                carrito[index].cantidad += cambio;
                if(carrito[index].cantidad < 1) carrito[index].cantidad = 1;
                localStorage.setItem('technest_carrito', JSON.stringify(carrito));
                renderizarCarrito();
                if (typeof actualizarPanelCarritoGlobal === 'function') { actualizarPanelCarritoGlobal(); }
            }
        }

        function recalcularSeleccionados() {
            const checkboxes = document.querySelectorAll('.check-item');
            let carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
            let subtotal = 0;
            let totalProductos = 0;
            checkboxes.forEach(cb => {
                if(cb.checked) {
                    const idx = cb.getAttribute('data-index');
                    const prod = carrito[idx];
                    if(prod) {
                        const precioNumerico = typeof prod.precio === 'string' ? parseFloat(prod.precio.replace(/[^\d]/g, "")) : prod.precio;
                        subtotal += (precioNumerico * (prod.cantidad || 1));
                        totalProductos += (prod.cantidad || 1);
                    }
                }
            });
            actualizarTotales(subtotal, totalProductos);
        }

        function toggleTodos(master) {
            const checkboxes = document.querySelectorAll('.check-item');
            checkboxes.forEach(cb => cb.checked = master.checked);
            recalcularSeleccionados();
        }

        function actualizarTotales(subtotal, cantidad) {
            const cantValida = isNaN(cantidad) ? 0 : cantidad;
            const subtotalValido = isNaN(subtotal) ? 0 : subtotal;
            const costoEnvio = cantValida > 1 ? 12000 : 0; 
            document.getElementById('label-productos').textContent = `Productos (${cantValida})`;
            document.getElementById('subtotal-valor').textContent = `$ ${subtotalValido.toLocaleString('es-CO')}`;
            document.getElementById('envio-valor').textContent = costoEnvio > 0 ? `$ ${costoEnvio.toLocaleString('es-CO')}` : "Gratis";
            document.getElementById('total-final').textContent = `$ ${(subtotalValido + costoEnvio).toLocaleString('es-CO')}`;
        }

        function eliminarItem(index) {
            let carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
            carrito.splice(index, 1);
            localStorage.setItem('technest_carrito', JSON.stringify(carrito));
            renderizarCarrito();
            if (typeof actualizarPanelCarritoGlobal === 'function') { actualizarPanelCarritoGlobal(); }
        }

        function irAPagar() {
            const total = document.getElementById('total-final').textContent;
            if(total === "$ 0" || total === "$ 0") {
                alert("Tu carrito está vacío.");
                return;
            }
            window.location.href = `pago.php?total=${encodeURIComponent(total)}`;
        }
     
        //  SUBSISTEMA DE ACCESIBILIDAD INTEGRADO AL DOM CENTRAL - 
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
        </script>
    </body>
</html>
