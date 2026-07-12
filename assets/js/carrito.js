/**
 * carrito.js - Tienda Virtual
 * Lógica de interacción del carrito en el frontend.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Actualizar badge del carrito en la navbar
    actualizarBadge();

    // Listener para botones de agregar al carrito (formularios vía fetch AJAX)
    document.querySelectorAll('form[id="form-agregar"]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            agregarAlCarrito(form);
        });
    });
});

/**
 * Agrega un producto al carrito via fetch (AJAX).
 * Si falla (no soportado), hace submit normal.
 */
function agregarAlCarrito(form) {
    const btn = form.querySelector('button[type="submit"]');
    const textoOriginal = btn.innerHTML;

    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Agregando...';
    btn.disabled = true;

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function (res) {
        if (!res.ok) throw new Error('Error de red');
        return res.json();
    })
    .then(function (data) {
        if (data.success) {
            actualizarBadgeConValor(data.total_items);
            mostrarToast(data.mensaje || 'Producto agregado al carrito', 'success');
        } else {
            mostrarToast('No se pudo agregar el producto.', 'danger');
        }
    })
    .catch(function () {
        // Fallback: submit normal si AJAX falla
        form.submit();
    })
    .finally(function () {
        btn.innerHTML = textoOriginal;
        btn.disabled = false;
    });
}

/**
 * Lee el badge actual y lo muestra.
 */
function actualizarBadge() {
    const badge = document.getElementById('carrito-badge');
    if (badge) {
        const val = parseInt(badge.textContent) || 0;
        badge.textContent = val;
        badge.classList.toggle('d-none', val === 0);
    }
}

/**
 * Actualiza el badge con un valor nuevo.
 */
function actualizarBadgeConValor(cantidad) {
    const badge = document.getElementById('carrito-badge');
    if (!badge) return;
    cantidad = parseInt(cantidad) || 0;
    badge.textContent = cantidad;
    badge.classList.toggle('d-none', cantidad === 0);

    // Animación de pulso
    badge.classList.remove('badge-pulse');
    void badge.offsetWidth; // reflow
    badge.classList.add('badge-pulse');
}

/**
 * Muestra un toast de Bootstrap en la esquina inferior derecha.
 */
function mostrarToast(mensaje, tipo) {
    const container = obtenerToastContainer();
    const id = 'toast-' + Date.now();
    const color = tipo === 'success' ? 'bg-success' : 'bg-danger';

    const html = `
    <div id="${id}" class="toast align-items-center text-white ${color} border-0" role="alert" aria-live="assertive">
        <div class="d-flex">
            <div class="toast-body fw-semibold">${escapeHtml(mensaje)}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);

    const toastEl = document.getElementById(id);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); });
}

function obtenerToastContainer() {
    let c = document.getElementById('toast-container');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toast-container';
        c.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        c.style.zIndex = '9999';
        document.body.appendChild(c);
    }
    return c;
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
