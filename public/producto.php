<?php
// 1. Arrancamos el motor de sesiones en el servidor
session_start();

// CORRECCIÓN CAPAS: Como estás en public/, salimos un nivel con /../ para ir a la carpeta config/
include(__DIR__ . '/../config/conexion.php'); 

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

// 2. Evaluamos si hay un usuario de la tienda logueado
$usuario_activo = isset($_SESSION['tienda_user']) ? $_SESSION['tienda_user'] : null;

// CORRECCIÓN AVATAR: Forzamos a que si hay una imagen en sesión, busque dentro de assets/ de forma fija
if (isset($_SESSION['tienda_avatar']) && !empty($_SESSION['tienda_avatar'])) {
    $avatar_crudo = $_SESSION['tienda_avatar'];
    
    // 1. Si la sesión ya trae el prefijo completo "assets/", lo dejamos intacto
    if (strpos($avatar_crudo, 'assets/') === 0) {
        $avatar_activo = $avatar_crudo;
    } 
    // 2. Si no lo trae, se lo inyectamos de forma obligatoria para buscar en la carpeta correcta
    else {
        $avatar_activo = "assets/" . $avatar_crudo;
    }
    
    // 3. Validación física: Si el archivo renombrado no existe en la carpeta, usamos el comodín
    if (!file_exists(__DIR__ . "/" . $avatar_activo)) {
        $avatar_activo = 'assets/user.ico';
    }
} else {
    $avatar_activo = 'assets/user.ico';
}

// 3. CAPTURA Y CONSULTA DINÁMICA DEL PRODUCTO SELECCIONADO
$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$producto_existe = false;

// Variables comodín de respaldo por si el producto no tiene datos cargados
$nombre_prod = "Producto Electrónico";
$precio_prod = 0;
$stock_prod  = 0;
$categoria_prod = "Tecnología";
$desc_prod = "No hay una descripción detallada disponible para este artículo actualmente.";

$img1 = "";
$img2 = "";
$img3 = "";

if ($id_producto > 0) {
    $query_prod = "SELECT * FROM productos WHERE id = $id_producto LIMIT 1";
    $res_prod = mysqli_query($conexion, $query_prod);
    
    if ($res_prod && mysqli_num_rows($res_prod) > 0) {
        $prod_info = mysqli_fetch_assoc($res_prod);
        $producto_existe = true;
        
        $nombre_prod = $prod_info['nombre'];
        $precio_prod = $prod_info['precio'];
        $stock_prod  = $prod_info['stock'];
        $categoria_prod = !empty($prod_info['categoria']) ? $prod_info['categoria'] : "Tecnología";
        
        // Sincronización exacta con tu carpeta física externa de recursos
        $carpeta_fuera = "../src/productos/";
        
        if (!empty($prod_info['imagen']) && file_exists($carpeta_fuera . $prod_info['imagen']) && $prod_info['imagen'] != 'default.jpg') {
            $img1 = $carpeta_fuera . $prod_info['imagen'];
        } else {
            $img1 = "../src/productos/default.jpg"; 
        }
        
        if (!empty($prod_info['imagen2']) && file_exists($carpeta_fuera . $prod_info['imagen2']) && $prod_info['imagen2'] != 'default.jpg') {
            $img2 = $carpeta_fuera . $prod_info['imagen2'];
        }
        
        if (!empty($prod_info['imagen3']) && file_exists($carpeta_fuera . $prod_info['imagen3']) && $prod_info['imagen3'] != 'default.jpg') {
            $img3 = $carpeta_fuera . $prod_info['imagen3'];
        }
        
        if (!empty($prod_info['descripcion'])) {
            $desc_prod = $prod_info['descripcion'];
        }
    }
}

// Redirección de control si intentan forzar la URL con un artículo inexistente
if (!$producto_existe) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>TechNest - Producto</title>
        <!-- Vinculación limpia con tus hojas de estilo y scripts unificados -->
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/producto.css.css">
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

                                <?php if ($usuario_activo): ?>
                                <img src="<?php echo $avatar_activo; ?>" alt="Perfil" class="avatar" onclick="toggleMenuPerfil(event)" style="cursor: pointer; width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                                <a href="#" class="user" onclick="toggleMenuPerfil(event)"><?php echo htmlspecialchars($usuario_activo); ?></a>

                                <!-- 1. VENTANA DE PERFIL LIMPIA Y ACTIVA -->
                                <div id="perfil-panel" class="carrito-panel">
                                    <div class="notificaciones-header">
                                        <span>Mi perfil</span>
                                        <button onclick="cerrarPerfil()" class="cerrar-panel-btn">✕</button>
                                    </div>
                                    <div class="notificaciones-body">
                                        <p style="font-weight: bold; margin-bottom: 5px; color: #333; font-size: 16px;">Cuenta Activa</p>
                                        <hr style="border: none; border-top: 1px solid #eee; margin: 10px 0 15px 0;">
                                        <a href="perfil.php" class="sugerencia-item">Configurar perfil</a>
                                        <a href="logout.php" class="sugerencia-item cerrar-sesion-btn">Cerrar sesión</a>
                                    </div>
                                </div>
                                    <?php else: ?>
                                    <img src="assets/user.ico" alt="Perfil" class="avatar" style="opacity: 0.6; width: 36px; height: 36px;">
                                    <a href="seleccion_login.php" class="user">Iniciar Sesión</a>
                                    <?php endif; ?>

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
                                        <?php if ($usuario_activo): ?>
                                    <div class="carrito-footer-movil">
                                <a href="carrito.php" class="sugerencia-item solo-movil btn-mis-compras-movil">Ver Mis Compras</a>
                            </div>
                            <?php endif; ?>
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
                <a href="#"><?php echo $usuario_activo ? "Enviar a " . htmlspecialchars($usuario_activo) : "Configurar envío"; ?></a>
            </div>
        </div>
        </header>
        <div class="volver">
            <a href="index.php">&#8630; Volver al listado</a>
        </div>
        <!-- SECCIÓN DE LA FICHA TÉCNICA DEL DISPOSITIVO -->
        <section class="producto-wrapper">
            <div class="producto-galeria">
                <div class="miniaturas">
                    <!-- Renderizado dinámico y condicional de tus 3 vistas organizadas en src/productos/ -->
                    <img src="<?php echo $img1; ?>" alt="Vista 1" class="miniatura activa" onclick="cambiarImagen(this)">
                    <?php if (!empty($img2)): ?>
                        <img src="<?php echo $img2; ?>" alt="Vista 2" class="miniatura" onclick="cambiarImagen(this)">
                    <?php endif; ?>
                    <?php if (!empty($img3)): ?>
                        <img src="<?php echo $img3; ?>" alt="Vista 3" class="miniatura" onclick="cambiarImagen(this)">
                    <?php endif; ?>
                </div>
                <div class="imagen-principal-wrapper">
                    <img src="<?php echo $img1; ?>" alt="Imagen principal" class="imagen-principal" id="imagen-principal">
                </div>
            </div>

            <div class="producto-info">
                <p class="producto-categoria"><?php echo htmlspecialchars($categoria_prod); ?></p>
                <h1 class="producto-titulo"><?php echo htmlspecialchars($nombre_prod); ?></h1>
                
                <div class="producto-rating">
                    <span class="estrellas-rating">★★★★★</span>
                    <span class="rating-num">5.0</span>
                    <span class="rating-resenas">(50 reseñas)</span>
                </div>

                <div class="producto-precios">
                    <span class="precio-actual">$ <?php echo number_format($precio_prod, 0, ',', '.'); ?></span>
                </div>

                <!-- CONTROL DE STOCK E INTERACTIVIDAD DE BOTONES SINCRONIZADA -->
                <?php if ($stock_prod > 0): ?>
                    <div class="producto-botones">
                        <!-- Se conectan los clics a las funciones del archivo script.js unificado -->
                        <button class="btn-comprar" onclick="procesarCompraAhora(<?php echo $id_producto; ?>)">COMPRAR AHORA</button>
                        <button class="btn-carrito-producto" onclick="agregarAlCarritoLocal(<?php echo $id_producto; ?>, '<?php echo htmlspecialchars($nombre_prod, ENT_QUOTES); ?>', <?php echo $precio_prod; ?>, '<?php echo $img1; ?>')">AÑADIR AL CARRITO</button>
                    </div>
                <?php else: ?>
                    <div class="producto-botones">
                        <button class="btn-comprar" style="background-color: #ff3b30; color: white; cursor: not-allowed; opacity: 0.8;" disabled>AGOTADO</button>
                        <button class="btn-carrito-producto" style="cursor: not-allowed; opacity: 0.5;" disabled>SIN STOCK</button>
                    </div>
                <?php endif; ?>

                <a href="#" class="ver-medios">Ver los medios de pago</a>
                
                <div class="producto-detalles">
                    <h3>Detalles</h3>
                    <div style="font-size: 14px; color: #333; line-height: 1.6; margin-bottom: 15px; white-space: pre-wrap;"><?php echo htmlspecialchars($desc_prod); ?></div>
                    <a href="#" class="ver-mas">ver más</a>
                </div>
            </div>
        </section>

        <!-- ESCUDO DE PROTECCIÓN CONTRA TEXTOS SUELTOS EN COMPUTADORAS -->
        <div class="wrapper-menu-movil-bloqueado">
            <nav class="bottom-nav">
                <a href="index.php" class="bottom-nav-item">
                    <img src="assets/carrito.png" alt="Catálogo" class="bottom-nav-icon">
                    <span>Catálogo</span>
                </a>
                <a href="<?php echo $usuario_activo ? '#' : 'seleccion_login.php'; ?>" class="bottom-nav-item bottom-nav-center" onclick="<?php echo $usuario_activo ? 'toggleMenuPerfil(event)' : ''; ?>">
                    <img src="<?php echo $usuario_activo ? $avatar_activo : 'assets/user.ico'; ?>" alt="Perfil" class="bottom-nav-avatar">
                    <span><?php echo $usuario_activo ? htmlspecialchars($usuario_activo) : "Ingresar"; ?></span>
                </a>
                <a href="#" class="bottom-nav-item">
                    <span class="bottom-nav-badge">%</span>
                    <span>Ofertas</span>
                </a>
            </nav>
        </div>

        <!--  SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON FICHA DE PRODUCTO -->

        <style>
            /*  CONTRASTE SEGURO PARA PRODUCTO: Invierte fondos sin colapsar las galerías */
            body.alto-contraste-activo {
                background-color: #141414 !important;
                color: #ffffff !important;
            }
            body.alto-contraste-activo main,
            body.alto-contraste-activo header,
            body.alto-contraste-activo .producto-wrapper,
            body.alto-contraste-activo .volver {
                background-color: #141414 !important;
            }
            
            /* CAPA DE FUERZA DE CONTENEDORES: Pasamos la galería y el bloque de detalles a grafito suave */
            body.alto-contraste-activo .producto-galeria,
            body.alto-contraste-activo .producto-info,
            body.alto-contraste-activo .miniaturas img,
            body.alto-contraste-activo .imagen-principal-wrapper,
            body.alto-contraste-activo .producto-detalles,
            body.alto-contraste-activo [class*="wrapper"] {
                background-color: #2d2d2d !important;
                background: #2d2d2d !important;
                border-color: #444444 !important;
            }
            
            /* Saneamiento Absoluto de Textos: Forzamos categoría, títulos, precios y rating a blanco nítido */
            body.alto-contraste-activo h1,
            body.alto-contraste-activo h2,
            body.alto-contraste-activo h3,
            body.alto-contraste-activo p,
            body.alto-contraste-activo span,
            body.alto-contraste-activo label,
            body.alto-contraste-activo a,
            body.alto-contraste-activo div {
                color: #ffffff !important;
            }
            
            /* Mantenemos el panel flotante del Widget siempre visible en blanco/negro puro */
            body.alto-contraste-activo #panel-accesibilidad-global,
            body.alto-contraste-activo #panel-accesibilidad-global * {
                background-color: #ffffff !important;
                color: #111111 !important;
                border-color: #2d6f73 !important;
            }

            /*  TIPOGRAFÍA VERDANA PARA BAJA VISIÓN: Forzado total sobre el comodín universal * */
            body.fuente-accesible-activa,
            body.fuente-accesible-activa * {
                font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
            }
        </style>

        <!-- SCRIPTS UNIFICADOS DE INTERFAZ COMERCIAL Y ACCESIBILIDAD UNIVERSAL -->
        <script>
        // 1. Inyección obligatoria de estado de login para librerías globales
        window.usuarioLogueado = <?php echo isset($_SESSION['tienda_user']) ? 'true' : 'false'; ?>;

        // Función nativa para conmutar las miniaturas de la galería técnica
        function cambiarImagen(elemento) {
            document.querySelectorAll('.miniatura').forEach(img => img.classList.remove('activa'));
            elemento.classList.add('activa');
            document.getElementById('imagen-principal').src = elemento.src;
        }

        // Función nativa para empaquetar y guardar artículos en la cesta del navegador
        function agregarAlCarritoLocal(id, nombre, precio, imagen) {
            let carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
            let itemExistente = carrito.find(prod => prod.id === id);
            
            if (itemExistente) {
                itemExistente.cantidad += 1;
            } else {
                carrito.push({ id: id, nombre: nombre, precio: precio, imagen: imagen, quantity: 1 });
            }
            
            localStorage.setItem('technest_carrito', JSON.stringify(carrito));
            alert('¡' + nombre + ' añadido al carrito con éxito!');
            if (typeof actualizarPanelCarritoGlobal === 'function') { actualizarPanelCarritoGlobal(); }
        }

        // Función nativa para saltar directamente al checkout de pago rápido
        function procesarCompraAhora(id) {
            window.location.href = 'pago.php?accion=comprar_directo&id=' + id;
        }

        // ==========================================================================
        //  SUBSISTEMA DE ACCESIBILIDAD INTEGRADO AL DOM CENTRAL
        // ==========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            // Inyectamos el panel flotante formalizado y serio sin emojis abajo a la derecha
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
            // 2. Lógica del Zoom por Escalamiento Proporcional Directo
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

            // 3. Lógica de Alto Contraste Seguro: Conmutación nativa de clase en el body
            document.getElementById('btn-toggle-contraste').addEventListener('click', () => {
                document.body.classList.toggle('alto-contraste-activo');
            });

            // 4. Lógica de Tipografía Verdana Blindada: Forzado con comillas invertidas sin excepciones
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
