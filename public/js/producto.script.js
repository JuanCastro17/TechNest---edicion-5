// base de datos de productos
const baseDatos = {
    1: {
        nombre: 'Tv Hyundai Roku 40"',
        categoria: 'Imagen y Video > Televisores',
        precio: '$ 759.900',
        rating: '4.2',
        resenas: '20',
        imagenes: ['television.jpg', 'television2.png', 'television3.png'],
        detalles: [
            'Pantalla Full HD 40 pulgadas',
            'Sistema operativo Roku integrado',
            'Resolución 1920x1080',
            'Conexión WiFi y Ethernet',
            '3 puertos HDMI'
        ]
    },
    2: {
        nombre: 'Nubia Smartphone Neo 3gt 256 Gb',
        categoria: 'Conectividad > Smartphones',
        precio: '$ 949.900',
        rating: '5.0',
        resenas: '240',
        imagenes: ['Celular.jpg', 'Celular2.png', 'Celular3.png'],
        detalles: [
            'Almacenamiento 256 GB',
            'Pantalla AMOLED 6.5"',
            'Cámara principal 64 MP',
            'Batería 5000 mAh',
            'Procesador Snapdragon'
        ]
    },
    3: {
        nombre: 'Lavadora Secadora Haceb 12 Kg',
        categoria: 'Electrodomésticos > Lavadoras',
        precio: '$ 2.319.900',
        rating: '5.0',
        resenas: '50',
        imagenes: ['lavadora.jpg', 'lavadora2.png', 'lavadora3.png'],
        detalles: [
            'Capacidad 12 kg',
            'Función lavadora y secadora',
            'Múltiples programas de lavado',
            'Bajo consumo energético',
            'Panel digital'
        ]
    },
    4: {
        nombre: 'Auriculares Inalámbricos Xiaomi Redmi',
        categoria: 'Audio y Sonido > Auriculares',
        precio: '$ 600.000',
        rating: '4.8',
        resenas: '5674',
        imagenes: ['audifonos.jpg', 'audifonos2.png', 'audifonos3.png'],
        detalles: [
            'Conexión Bluetooth 5.3',
            'Cancelación de ruido activa',
            'Batería 30 horas con estuche',
            'Resistente al agua IPX4',
            'Micrófono incorporado'
        ]
    },
    5: {
        nombre: 'Cámara De Vídeo Digital',
        categoria: 'Imagen y Video > Cámaras',
        precio: '$ 363.281',
        rating: '4.0',
        resenas: '100',
        imagenes: ['camara.jpg', 'camara2.png', 'camara3.png'],
        detalles: [
            'Resolución 4K',
            'Pantalla táctil giratoria',
            'Zoom óptico 16x',
            'Grabación en tarjeta SD',
            'Batería recargable incluida'
        ]
    }
};

// Carga el producto según el ?id= de la URL y maneja los eventos de la interfaz
document.addEventListener('DOMContentLoaded', function() {

    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    const producto = baseDatos[id];

    // si el id no existe muestra error
    if (!producto) {
        document.querySelector('.producto-wrapper').innerHTML =
            '<p style="padding:40px;text-align:center;color:#888">Producto no encontrado.</p>';
        return;
    }

    // llena los datos
    document.title = 'TechNest - ' + producto.nombre;
    document.querySelector('.producto-categoria').textContent = producto.categoria;
    document.querySelector('.producto-titulo').textContent = producto.nombre;
    document.querySelector('.precio-actual').textContent = producto.precio;
    document.querySelector('.rating-num').textContent = producto.rating;
    document.querySelector('.rating-resenas').textContent = '(' + producto.resenas + ' reseñas)';

    // llena las imágenes
    const miniaturas = document.querySelectorAll('.miniatura');
    miniaturas.forEach(function(min, i) {
        if (producto.imagenes[i]) {
            min.src = producto.imagenes[i];
        }
    });
    document.getElementById('imagen-principal').src = producto.imagenes[0];

    // llena los detalles
    const lista = document.querySelector('.producto-detalles ul');
    lista.innerHTML = '';
    producto.detalles.forEach(function(detalle) {
        const li = document.createElement('li');
        li.textContent = detalle;
        lista.appendChild(li);
    });

    // Dibujar lo que ya esté guardado en el carrito
    actualizarVistaCarrito();

    
    // CONTROL SEGURO DEL BOTÓN COMPRAR AHORA
    
    const botonComprar = document.querySelector('.btn-comprar');
    if (botonComprar) {
        botonComprar.addEventListener('click', function(event) {
            event.preventDefault(); 
            event.stopPropagation();

            if (window.usuarioLogueado === true) {
                window.location.href = "pago.php?id=" + id;
            } else {
                alert("⚠️ Para realizar un pedido o proceder al pago, necesitas iniciar sesión con tu cuenta de TechNest.");
                window.location.href = "seleccion_login.php";
            }
        });
    }

    
    // CONTROL SEGURO DEL BOTÓN AÑADIR AL CARRITO
    
    const botonCarrito = document.querySelector('.btn-carrito-producto');
    if (botonCarrito) {
        botonCarrito.addEventListener('click', function(event) {
            // Evaluamos si el usuario NO tiene sesión activa antes de modificar el localStorage
            if (window.usuarioLogueado !== true) {
                event.preventDefault();
                event.stopPropagation();
                alert("⚠️ ¡Hola! Para añadir productos al carrito de compras, primero debes iniciar sesión con tu cuenta.");
                window.location.href = "seleccion_login.php";
                return; // Bloquea y corta por completo el resto de la función
            }

            // Si está logueado, pasa normal y guarda el producto en la clave unificada 'technest_carrito'
            if (producto) {
                let carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
                carrito.push(producto); // Agregamos el nuevo objeto completo de la base de datos
                localStorage.setItem('technest_carrito', JSON.stringify(carrito)); // Guardamos
                
                actualizarVistaCarrito();
                document.getElementById('carrito-panel').classList.add('activo'); // Feedback
            }
        });
    }

});

// cambia la imagen principal al clickear las miniaturas
function cambiarImagen(miniatura) {
    document.querySelectorAll('.miniatura').forEach(function(m) {
        m.classList.remove('activa');
    });
    miniatura.classList.add('activa');
    document.getElementById('imagen-principal').src = miniatura.src;
}

// Función para dibujar el carrito leyendo la clave global del localStorage
function actualizarVistaCarrito() {
    const listaCarrito = document.querySelector('#carrito-panel .notificaciones-body');
    if (!listaCarrito) return;

    // Cambiado de 'carrito' a 'technest_carrito' para sincronización global
    let carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];

    if (carrito.length === 0) {
        listaCarrito.innerHTML = '<p style="margin:0; padding:10px; color:#888;">Tu carrito está vacío</p>';
        return;
    }

    listaCarrito.innerHTML = ''; // Limpiar para redibujar
    carrito.forEach((prod, index) => {
        let urlImagen = 'default.jpg';
        if (prod.imagenes && prod.imagenes[0]) {
            urlImagen = prod.imagenes[0];
        } else if (prod.imagen) {
            urlImagen = prod.imagen;
        }

        const item = document.createElement('div');
        item.style.cssText = "display:flex; align-items:center; gap:10px; padding:10px; border-bottom:1px solid #eee;";
        item.innerHTML = `
            <img src="${urlImagen}" style="width:50px; height:50px; border-radius:5px; object-fit:contain;">
            <div style="flex:1">
                <p style="font-size:12px; margin:0; font-weight:bold; color:#333;">${prod.nombre}</p>
                <p style="font-size:12px; margin:0; color:#2d6f73; font-weight:bold;">${prod.precio}</p>
            </div>
            <button onclick="eliminarDelCarrito(${index})" style="background:none; border:none; color:red; cursor:pointer; font-weight:bold; font-size:14px;">✕</button>
        `;
        listaCarrito.appendChild(item);
    });
}

// Función vinculada al entorno window para eliminar productos de forma segura
window.eliminarDelCarrito = function(index) {
    let carrito = JSON.parse(localStorage.getItem('technest_carrito')) || [];
    carrito.splice(index, 1); // Quitar el producto del array
    localStorage.setItem('technest_carrito', JSON.stringify(carrito)); // Guardar cambios en la clave correcta
    
    // Refrescar ambas vistas posibles por si interactúan al tiempo
    actualizarVistaCarrito(); 
    if (typeof actualizarPanelCarritoGlobal === 'function') {
        actualizarPanelCarritoGlobal();
    }
};