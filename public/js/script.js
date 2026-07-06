// public/js/script.js - SUBPARTE A: CARRUSEL, BUSCADOR Y TOGGLES CORREGIDOS

// 1. CONTROLADORES DEL CARRUSEL DE PRODUCTOS PRINCIPAL
document.addEventListener('DOMContentLoaded', function() {
    const carrusel = document.getElementById('carrusel');
    const cards = document.querySelectorAll('.producto-card');
    let indice = 0;
    if (!carrusel || cards.length === 0) return;

    function tarjetasVisibles() {
        if (window.innerWidth <= 480) return 1;
        if (window.innerWidth <= 768) return 2;
        return 4;
    }

    function moverCarrusel(direccion) {
        const total = cards.length;
        const visibles = tarjetasVisibles();
        indice += direccion;
        if (indice > total - visibles) indice = 0;
        if (indice < 0) indice = total - visibles;
        const anchoCard = cards[0].getBoundingClientRect().width + 20; 
        carrusel.style.transform = 'translateX(-' + (indice * anchoCard) + 'px)';
    }

    window.addEventListener('resize', function() {
        indice = 0;
        carrusel.style.transform = 'translateX(0)';
    });
    window.moverCarrusel = moverCarrusel;
});

// 2. REPARACIÓN DE LA BARRA BUSCADORA COMPLETA (CONEXIÓN DIRECTA A TU API REAL)
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const sugerenciasPanel = document.getElementById('search-sugerencias');
    if (!buscador || !sugerenciasPanel) return;

    buscador.addEventListener('input', function() {
        const texto = this.value.trim();

        if (texto.length < 1) {
            sugerenciasPanel.innerHTML = '';
            sugerenciasPanel.classList.remove('activo');
            sugerenciasPanel.style.display = 'none';
            return;
        }

        fetch(`api/consultar.php?q=${encodeURIComponent(texto)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    sugerenciasPanel.innerHTML = '<div class="sugerencia-item" style="color:#888; padding:10px;">Sin resultados</div>';
                    sugerenciasPanel.classList.add('activo');
                    sugerenciasPanel.style.display = 'block';
                    return;
                }

                let html = '';
                data.forEach(p => {
                    const rutaImagen = p.imagen ? `../src/productos/${p.imagen}` : '../src/productos/default.jpg';
                    const formatoPrecio = new Intl.NumberFormat('es-CO', { 
                        style: 'currency', currency: 'COP', minimumFractionDigits: 0 
                    }).format(p.precio);
                    
                    html += `
                        <a href="producto.php?id=${p.id}" class="sugerencia-item" style="display:flex; align-items:center; gap:10px; padding:10px; text-decoration:none; color:#333; border-bottom:1px solid #eee;">
                            <img src="${rutaImagen}" alt="${p.nombre}" style="width:35px; height:30px; object-fit:contain; background:#fafafa; border-radius:4px;">
                            <div style="display:flex; flex-direction:column; text-align:left;">
                                <span style="font-weight:bold; font-size:13px;">${escapeHTML(p.nombre)}</span>
                                <span style="font-size:12px; color:#2d6f73; font-weight:bold;">${formatoPrecio}</span>
                            </div>
                        </a>
                    `;
                });

                sugerenciasPanel.innerHTML = html;
                sugerenciasPanel.classList.add('activo');
                sugerenciasPanel.style.display = 'block';
            })
            .catch(err => console.error("Error en motor de búsqueda AJAX:", err));
    });
});

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag));
}

// --- PANEL DE NOTIFICACIONES (CORRECCIÓN CRÍTICA DE VISIBILIDAD DE HOJAS DE ESTILO) ---
function toggleNotificaciones(event) {
    if (event) event.preventDefault();
    const panel = document.getElementById('notificaciones-panel');
    const puntoAlerta = document.getElementById('punto-alerta');
    if (!panel) return;

    // Evaluamos el estilo real renderizado por el navegador en lugar del inline vacío
    const estiloReal = window.getComputedStyle(panel).display;
    
    if (estiloReal === 'none') {
        panel.style.setProperty('display', 'block', 'important');
        actualizarPanelNotificacionesGlobal();
        cerrarCarrito();
        cerrarPerfil();
    } else {
        panel.style.setProperty('display', 'none', 'important');
    }
    
    if (puntoAlerta) {
        puntoAlerta.style.setProperty('display', 'none', 'important');
    }
    localStorage.setItem('technest_nuevas_notificaciones', 'false');
}

function cerrarNotificaciones() {
    const panel = document.getElementById('notificaciones-panel');
    if (panel) panel.style.setProperty('display', 'none', 'important');
}
// public/js/script.js - SUBPARTE B: CONTINUACIÓN DE TOGGLES, PERSISTENCIA Y CIERRE COMPLETO

// REEMPLAZA ESTA FUNCIÓN EN TU ARCHIVO SCRIPT.JS:
function actualizarPanelNotificacionesGlobal() {
    const contenedorBody = document.querySelector('#notificaciones-panel .notificaciones-body');
    if (!contenedorBody) return;
    
    const listaNotis = JSON.parse(localStorage.getItem('technest_notificaciones')) || [];
    
    // DEFINIMOS EL BOTÓN FIJO DE REDIRECCIÓN AL MÓDULO DE RASTREO SOLICITADO
    const botonRastreoFijo = `
        <div style="padding-top: 10px; margin-top: 10px; border-top: 1px solid #eee; text-align: center;">
            <p style="margin-bottom: 12px; font-size: 13px; color: #555; line-height: 1.4;">
                Consulta el estado, número único y marcas de envío de tus transacciones en tiempo real.
            </p>
            <a href="rastreo.php" style="display: block; background: #f2c300; color: #111; padding: 10px 15px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 12px; text-transform: uppercase; text-align: center; border: 1px solid #dbb100; transition: 0.2s;">
                 Ir a Panel de Notificaciones
            </a>
        </div>
    `;

    // CASO 1: Si no hay alertas nuevas en el historial del cliente
    if (listaNotis.length === 0) {
        contenedorBody.innerHTML = `
            <p style="margin: 0; font-size: 13px; color: #666; font-weight: 500;">No tienes alertas del sistema pendientes</p>
            ${botonRastreoFijo}
        `;
        return;
    }
    
    // CASO 2: Si hay transacciones activas, las listamos en grilla antes del botón
    let htmlAlertas = listaNotis.map(n => `
        <div style="padding: 10px 0; border-bottom: 1px solid #eee; font-size: 13px; text-align: left;">
            <p style="margin: 0 0 4px 0; color: #333; line-height: 1.4;">${escapeHTML(n.texto)}</p>
            <span style="font-size: 11px; color: #999; font-weight: bold;">⏱️ ${n.fecha} - Pendiente</span>
        </div>
    `).join('');

    // Inyectamos las alertas y abajo el botón de control institucional
    contenedorBody.innerHTML = htmlAlertas + botonRastreoFijo;
}


// --- PANEL DE CARRITO (CON INITIALIZADOR BLINDADO) ---
function toggleCarrito(event) {
    if (event) event.preventDefault();
    const panel = document.getElementById('carrito-panel');
    if (!panel) return;

    // Leemos el estilo real calculado para romper la incompatibilidad del CSS plano
    const estiloReal = window.getComputedStyle(panel).display;
    
    if (estiloReal === 'none') {
        panel.style.setProperty('display', 'block', 'important');
        actualizarPanelCarritoGlobal();
        cerrarNotificaciones();
        cerrarPerfil();
    } else {
        panel.style.setProperty('display', 'none', 'important');
    }
}

function cerrarCarrito() {
    const panel = document.getElementById('carrito-panel');
    if (panel) panel.style.setProperty('display', 'none', 'important');
}

// --- PANEL DE PERFIL (CON INITIALIZADOR BLINDADO) ---
function toggleMenuPerfil(event) {
    if (event) event.preventDefault();
    const panel = document.getElementById('perfil-panel');
    if (!panel) return;

    const estiloReal = window.getComputedStyle(panel).display;
    
    if (estiloReal === 'none') {
        panel.style.setProperty('display', 'block', 'important');
        cerrarCarrito();
        cerrarNotificaciones();
    } else {
        panel.style.setProperty('display', 'none', 'important');
    }
}

function cerrarPerfil() {
    const panel = document.getElementById('perfil-panel');
    if (panel) panel.style.setProperty('display', 'none', 'important');
}

// --- VERIFICAR PUNTO ROJO AL CARGAR ---
function verificarNotificacionesPendientes() {
    const puntoAlerta = document.getElementById('punto-alerta');
    if (!puntoAlerta) return; 
    const tieneAlertasNuevas = localStorage.getItem('technest_nuevas_notificaciones') === 'true';
    if (tieneAlertasNuevas) {
        puntoAlerta.style.setProperty('display', 'block', 'important');
    } else {
        puntoAlerta.style.setProperty('display', 'none', 'important');
    }
}

// --- ESCUCHADORES GENERALES DE ARRANQUE Y CIERRE ---
document.addEventListener('DOMContentLoaded', function() {
    actualizarPanelCarritoGlobal();
    actualizarPanelNotificacionesGlobal();
    verificarNotificacionesPendientes();
    
    // Forzamos el estado de cerrado limpio al cargar el documento
    cerrarCarrito();
    cerrarNotificaciones();
    cerrarPerfil();

    const buscadorInput = document.getElementById('buscador');
    const sugerenciasPanelBox = document.getElementById('search-sugerencias');
    
    document.addEventListener('click', function(e) {
        if (buscadorInput && sugerenciasPanelBox && !buscadorInput.contains(e.target) && !sugerenciasPanelBox.contains(e.target)) {
            sugerenciasPanelBox.style.setProperty('display', 'none', 'important');
        }
    });
});

// DETECTOR DE CLIC EXTERIOR (Cierra cualquier flujo abierto al pulsar fuera)
document.addEventListener('click', function(e) {
    const panelNoti = document.getElementById('notificaciones-panel');
    const btnNoti = document.getElementById('btn-campana');
    const panelCarrito = document.getElementById('carrito-panel');
    const btnCarrito = document.getElementById('btn-carrito');
    const panelPerfil = document.getElementById('perfil-panel');
    const btnPerfil = document.getElementById('btn-avatar-click'); 

    if (panelNoti && btnNoti && !panelNoti.contains(e.target) && !btnNoti.contains(e.target)) {
        panelNoti.style.setProperty('display', 'none', 'important');
    }
    if (panelCarrito && btnCarrito && !panelCarrito.contains(e.target) && !btnCarrito.contains(e.target)) {
        panelCarrito.style.setProperty('display', 'none', 'important');
    }
    if (panelPerfil && !panelPerfil.contains(e.target)) {
        const clickAvatarEscritorio = btnPerfil && btnPerfil.contains(e.target);
        const clickAvatarMovil = e.target.classList.contains('bottom-nav-avatar') || e.target.closest('.bottom-nav-center');
        const clickClaseAvatar = e.target.classList.contains('avatar');
        if (!clickAvatarEscritorio && !clickAvatarMovil && !clickClaseAvatar) {
            panelPerfil.style.setProperty('display', 'none', 'important');
        }
    }
});

// --- RENDERIZADOR DEL CARRITO GLOBAL ---
function actualizarPanelCarritoGlobal() {
    const listaCarrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
    const contenedorBody = document.querySelector('#carrito-panel .notificaciones-body');
    if (!contenedorBody) return;
    
    if (listaCarrito.length === 0) {
        contenedorBody.innerHTML = `<p style="margin:0; padding:10px; color:#888; font-size:13px; text-align:center;">Tu carrito está vacío</p>`;
        return;
    }
    
    let htmlProductos = '';
    listaCarrito.forEach((prod, index) => {
        const valorNumerico = typeof prod.precio === 'string' ? parseFloat(prod.precio.replace(/[^\d]/g, "")) : prod.precio;
        const formatoPrecio = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(valorNumerico);
        
        let urlImagen = '../src/productos/default.jpg';
        if (prod.imagen) {
            const nombreLimpio = prod.imagen.replace('assets/', '').replace('../src/productos/', '');
            urlImagen = `../src/productos/${nombreLimpio}`;
        }
        
        htmlProductos += `
            <div class="carrito-item" style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                <img src="${urlImagen}" alt="${escapeHTML(prod.nombre)}" style="width: 50px; height: 50px; object-fit: contain; border-radius:5px; background:#fafafa;">
                <div style="flex-grow: 1; text-align: left;">
                    <p style="margin:0; font-weight:bold; font-size:13px; color:#333;">${escapeHTML(prod.nombre)}</p>
                    <p style="margin:0; font-size:12px; color:#2d6f73; font-weight:bold;">${formatoPrecio} (x${prod.cantidad || 1})</p>
                </div>
                <button onclick="eliminarDelCarritoGlobal(${index})" style="background:none; border:none; color:#ff4d4d; cursor:pointer; font-weight:bold; font-size:14px;">✕</button>
            </div>
        `;
    });
    
    contenedorBody.innerHTML = htmlProductos;
}

function eliminarDelCarritoGlobal(index) {
    let listaCarrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
    listaCarrito.splice(index, 1);
    localStorage.setItem('technest_carrito', JSON.stringify(listaCarrito));
    
    actualizarPanelCarritoGlobal();
    if (typeof actualizarVistaCarrito === 'function') {
        actualizarVistaCarrito();
    }
}
