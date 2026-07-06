<?php
session_start();
// Como index.php está dentro de public, salimos un nivel para encontrar config/
include(__DIR__ . "/../config/conexion.php");

// Bloque de respaldo para garantizar conectividad en el puerto 3307 local
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

$usuario_activo = isset($_SESSION['tienda_user']) ? $_SESSION['tienda_user'] : null;

// REQUERIMIENTO COMPLETO: Escudo protector de avatar idéntico al de producto.php
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TechNest</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Cargamos tu script unificado con los escuchadores dinámicos calculados -->
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
                <input type="text" class="search" id="buscador" placeholder="Buscar productos..." autocomplete="off">
                <div class="search-sugerencias" id="search-sugerencias"></div>
            </div>
            <div class="right-section" style="display: flex; align-items: center; gap: 15px;">
            <div class="UserSection" id="btn-avatar-click" style="position: relative;">

            <?php if ($usuario_activo): ?>
            <!-- El avatar ahora lee de forma obligatoria la ruta blindada de assets -->
            <img src="<?php echo $avatar_activo; ?>" alt="Perfil" class="avatar" onclick="toggleMenuPerfil(event)" style="cursor: pointer; width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
            <a href="#" class="user" onclick="toggleMenuPerfil(event)"><?php echo htmlspecialchars($usuario_activo); ?></a>

            <!-- 1. VENTANA DE PERFIL LIMPIA (CONTROLADA POR TU ARCHIVO CSS) -->
            <div id="perfil-panel" class="carrito-panel">
            <div class="notificaciones-header">
                <span>Mi perfil</span>
                <button onclick="cerrarPerfil()" class="cerrar-panel-btn">✕</button>
            </div>
            <div class="notificaciones-body">
            <span class="perfil-status">Cuenta Activa</span>
            <hr class="perfil-divisor">
            <a href="perfil.php" class="sugerencia-item">Configurar perfil</a>
            <a href="notificaciones.php" class="sugerencia-item solo-movil">Notificaciones</a>
            <a href="ayuda.php" class="sugerencia-item solo-movil">Ayuda / Soporte</a>
            <a href="logout.php" class="sugerencia-item cerrar-sesion-btn">Cerrar sesión</a>
            </div>
            </div>
            <?php else: ?>
            <img src="assets/user.ico" alt="Perfil" class="avatar" style="opacity: 0.6; width: 36px; height: 36px;">
            <a href="seleccion_login.php" class="user">Iniciar Sesión</a>
            <?php endif; ?>

            </div>
            <a href="carrito.php" class="shopping">Mis compras</a>

            <!-- 2. VENTANA DE NOTIFICACIONES CON PANEL DE RASTREO INTEGRADO -->
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
                    <!-- Inyectamos el cuerpo dinámico del panel de notificaciones solicitado -->
                    <div class="notificaciones-body" style="padding: 15px; text-align: center;">
                        <p style="margin-bottom: 15px; font-size: 13px; color: #333; font-family: sans-serif; line-height: 1.4;">
                            Consulta el estado, número único y marcas de envío de tus transacciones en tiempo real.
                        </p>
                        <!-- Botón amarillo cóncavo unificado con las tipografías de tu marca -->
                        <a href="rastreo.php" style="display: block; background: #f2c300; color: #111; padding: 10px 15px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 12px; text-transform: uppercase; text-align: center; border: 1px solid #dbb100; font-family: sans-serif; transition: 0.2s;">
                            📥 Ir a Panel de Notificaciones
                        </a>
                    </div>
                </div>
            </div>


            <!-- 3. VENTANA DE CARRITO FLOTANTE LIMPIA -->
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
<!-- SECCIÓN CARRUSEL DINÁMICO REPARADO -->
<section class="carrusel-wrapper">
    <button class="carrusel-btn izquierda" onclick="moverCarrusel(-1)">←</button>
    <div class="carrusel-contenedor">
        <div class="carrusel" id="carrusel">
            <?php
            // CONTROL DE TOPE: Trae un máximo de 8 registros organizados para evitar la barra infinita
            $query = "SELECT id, nombre, precio, stock, imagen FROM productos ORDER BY id ASC LIMIT 8";
            $resultado = mysqli_query($conexion, $query);

            if ($resultado && mysqli_num_rows($resultado) > 0):
            while ($row = mysqli_fetch_assoc($resultado)):
            $tiene_stock = $row['stock'] > 0;
            // El enlace se deshabilita si no hay inventario disponible
            $enlace = $tiene_stock ? "producto.php?id=" . $row['id'] : "#";
            $estilo_agotado = $tiene_stock ? "" : "opacity: 0.6; cursor: not-allowed;";
            ?>
            <a href="<?php echo $enlace; ?>" class="producto-card" style="<?php echo $estilo_agotado; ?>" onclick="<?php echo $tiene_stock ? '' : 'event.preventDefault();'; ?>">
            <img src="../src/productos/<?php echo htmlspecialchars($row['imagen']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
            <p class="producto-nombre"><?php echo htmlspecialchars($row['nombre']); ?></p>
            <?php if ($tiene_stock): ?>
            <div class="estrellas">★★★★★</div>
                <p class="resenas">(<?php echo rand(15, 120); ?>)</p>
                <p class="precio">$ <?php echo number_format($row['precio'], 0, ',', '.'); ?></p>
                <?php else: ?>
                <div style="color: #ff3b30; font-weight: bold; font-size: 13px; margin-top: 10px; background: #fbebeb; padding: 6px; border-radius: 4px; text-align: center;">
                AGOTADO / SIN STOCK
            </div>
            <?php endif; ?>
            </a>
            <?php
            endwhile;
            else:
            ?>
            <p style="padding: 20px; text-align: center; color: #666; width: 100%;">No hay productos registrados en el inventario.</p>
            <?php endif; ?>
        </div>
    </div>
    <button class="carrusel-btn derecha" onclick="moverCarrusel(1)">→</button>
</section>

<!-- SECCIÓN CATEGORÍAS ORIGINALES CON RUTAS REPARADAS -->
<section class="categorias-wrapper">
    <div class="categorias-header">
        <h2>Categorías</h2>
        <a href="#">Mostrar todas las categorías</a>
    </div>
    <div class="categorias-grid">
        <a href="#" class="categoria-card">
        <img src="assets/mini_router.png" alt="Conectividad y Redes">
        <span>Conectividad y Redes</span>
        </a>
        <a href="#" class="categoria-card">
        <img src="assets/teclado.png" alt="Periféricos de Entrada">
        <span>Periféricos de Entrada</span>
        </a>
        <a href="#" class="categoria-card">
        <img src="assets/gudga.png" alt="Energía y Almacenamiento">
        <span>Energía y Almacenamiento</span>
        </a>
        <a href="#" class="categoria-card">
        <img src="assets/camara px.png" alt="Imagen y Video">
        <span>Imagen y Video</span>
        </a>
        <a href="#" class="categoria-card">
        <img src="assets/audifonos rosas.webp" alt="Audio y Sonido">
        <span>Audio y Sonido</span>
        </a>
        <a href="#" class="categoria-card">
        <img src="assets/let.png" alt="Ergonomía e Iluminación">
        <span>Ergonomía e Iluminación</span>
        </a>
    </div>
</section>

<!-- NAVEGACIÓN INFERIOR MÓVIL ORIGINAL CON ESCUDO PROTECTOR -->
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

<!-- SUBSISTEMA DE ACCESIBILIDAD UNIVERSAL - COMPATIBLE CON EL CATÁLOGO GLOBAL -->
<style>
    /*  CONTRASTE SEGURO PARA EL CATÁLOGO: Invierte fondos sin colapsar las grillas */
    body.alto-contraste-activo {
        background-color: #141414 !important;
        color: #ffffff !important;
    }
    body.alto-contraste-activo main,
    body.alto-contraste-activo header,
    body.alto-contraste-activo section,
    body.alto-contraste-activo footer {
        background-color: #141414 !important;
    }
    
    /* CAPA DE FUERZA DE TARJETAS: Forzamos tanto el carrusel de productos como las categorías a volverse grafito legible */
    body.alto-contraste-activo .producto-card,
    body.alto-contraste-activo .categoria-card,
    body.alto-contraste-activo [class*="card"],
    body.alto-contraste-activo [class*="wrapper"] {
        background-color: #2d2d2d !important;
        background: #2d2d2d !important;
        border-color: #444444 !important;
        box-shadow: 0 4px 10px rgba(255,255,255,0.05) !important;
    }
    
    /* Saneamiento Absoluto de Textos: Obligamos a absolutamente TODOS los elementos del catálogo a contrastar en blanco brillante */
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
    
    /* Evita que los botones del widget se vuelvan invisibles en modo oscuro */
    body.alto-contraste-activo #panel-accesibilidad-global,
    body.alto-contraste-activo #panel-accesibilidad-global * {
        background-color: #ffffff !important;
        color: #111111 !important;
        border-color: #2d6f73 !important;
    }

    /*  TIPOGRAFÍA UNIVERSAL BLINDADA: Tumba cualquier fuente base de Google Fonts forzando la legibilidad de Verdana */
    body.fuente-accesible-activa,
    body.fuente-accesible-activa * {
        font-family: 'Verdana', 'Trebuchet MS', sans-serif !important;
    }
</style>



<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Inyectamos la interfaz del panel formalizado sin emojis abajo a la derecha
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

    // 2. Lógica del Zoom por Escalamiento Proporcional
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

    // 4. Lógica de Tipografía Verdana Blindada: Inyección dinámica para romper cualquier Google Fonts
    let fuenteAccesibleActiva = false;
    let estiloFuentesDinamico = null;
    
    document.getElementById('btn-toggle-fuente').addEventListener('click', () => {
        fuenteAccesibleActiva = !fuenteAccesibleActiva;
        if (fuenteAccesibleActiva) {
            estiloFuentesDinamico = document.createElement('style');
            estiloFuentesDinamico.id = 'fuente-accesible-forzado';
            estiloFuentesDinamico.innerHTML = `* { font-family: 'Verdana', 'Trebuchet MS', sans-serif !important; letter-spacing: 0.5px !important; }`;
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
