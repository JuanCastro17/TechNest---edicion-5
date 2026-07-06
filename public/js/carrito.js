document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const totalCarrito = params.get('total'); // Viene desde carrito.php

    if (totalCarrito) {
        // Asignación de los valores calculados en la pasarela de pagos
        const descProducto = document.getElementById('precio-producto');
        const descTotal = document.getElementById('precio-total');

        if(descProducto) descProducto.textContent = "Productos seleccionados de tu carrito";
        if(descTotal) descTotal.textContent = totalCarrito;
    }
});