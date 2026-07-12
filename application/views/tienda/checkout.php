<div class="container">
    <h2 class="fw-bold mb-1"><i class="bi bi-lock-fill me-2"></i>Finalizar compra</h2>
    <p class="text-muted mb-4">Completa tus datos para procesar el pedido</p>

    <!-- Pasos -->
    <div class="d-flex align-items-center mb-4 checkout-steps">
        <span class="step-done"><i class="bi bi-cart-check-fill"></i> Carrito</span>
        <span class="step-line"></span>
        <span class="step-active"><i class="bi bi-person-fill"></i> Mis datos</span>
        <span class="step-line"></span>
        <span class="step-pending"><i class="bi bi-credit-card"></i> Pago</span>
    </div>

    <form action="<?php echo base_url('pago/procesar'); ?>" method="POST" id="form-checkout" novalidate>
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
               value="<?php echo $this->security->get_csrf_hash(); ?>">

        <div class="row g-4">
            <!-- Columna izquierda: datos personales y pago -->
            <div class="col-lg-7">
                <!-- Datos personales -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold border-0 pt-3">
                        <i class="bi bi-person-circle me-2 text-dark"></i>Datos de envío
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">DNI <span class="text-danger">*</span></label>
                                <input type="text" name="dni" class="form-control <?php echo form_error('dni') ? 'is-invalid' : ''; ?>"
                                       placeholder="12345678" maxlength="15"
                                       value="<?php echo set_value('dni'); ?>" required>
                                <div class="invalid-feedback"><?php echo form_error('dni'); ?></div>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Nombres completos <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" class="form-control <?php echo form_error('nombres') ? 'is-invalid' : ''; ?>"
                                       placeholder="Juan Pérez García"
                                       value="<?php echo set_value('nombres'); ?>" required>
                                <div class="invalid-feedback"><?php echo form_error('nombres'); ?></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección de envío <span class="text-danger">*</span></label>
                                <input type="text" name="direccion_envio"
                                       class="form-control <?php echo form_error('direccion_envio') ? 'is-invalid' : ''; ?>"
                                       placeholder="Av. Los Olivos 123, Lima"
                                       value="<?php echo set_value('direccion_envio'); ?>" required>
                                <div class="invalid-feedback"><?php echo form_error('direccion_envio'); ?></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Celular <span class="text-danger">*</span></label>
                                <input type="tel" name="celular"
                                       class="form-control <?php echo form_error('celular') ? 'is-invalid' : ''; ?>"
                                       placeholder="987654321" maxlength="20"
                                       value="<?php echo set_value('celular'); ?>" required>
                                <div class="invalid-feedback"><?php echo form_error('celular'); ?></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Referencia / Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"
                                          placeholder="Cerca al parque, piso 2, etc."><?php echo set_value('observaciones'); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Método de pago (simulado) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold border-0 pt-3">
                        <i class="bi bi-credit-card me-2 text-dark"></i>Método de pago
                        <span class="badge bg-warning text-dark ms-2 small">Modo sandbox</span>
                    </div>
                    <div class="card-body">
                        <div class="payment-card-preview p-3 rounded-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-white fw-bold">Tarjeta de crédito / débito</span>
                                <div class="d-flex gap-2">
                                    <img src="https://img.icons8.com/color/32/visa.png" alt="Visa">
                                    <img src="https://img.icons8.com/color/32/mastercard.png" alt="MC">
                                </div>
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-card"
                                       placeholder="1234 5678 9012 3456" maxlength="19"
                                       id="num-tarjeta" autocomplete="cc-number">
                            </div>
                            <div class="row g-2">
                                <div class="col-7">
                                    <input type="text" class="form-control form-control-card"
                                           placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
                                </div>
                                <div class="col-5">
                                    <input type="text" class="form-control form-control-card"
                                           placeholder="CVV" maxlength="4" autocomplete="cc-csc">
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info small mb-0 py-2">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Modo demo:</strong> El pago es simulado. No se cargará a ninguna tarjeta real.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: resumen del pedido -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
                    <div class="card-header bg-dark text-white fw-semibold">
                        <i class="bi bi-receipt me-2"></i>Tu pedido
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($carrito as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo base_url($item['imagen']); ?>"
                                         width="45" height="50" style="object-fit:cover;" class="rounded"
                                         onerror="this.onerror=null;this.src='<?php echo base_url('assets/img/productos/default1.jpg'); ?>'">
                                    <div>
                                        <div class="fw-semibold small"><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-muted small">
                                            Talla: <?php echo htmlspecialchars($item['talla'], ENT_QUOTES, 'UTF-8'); ?> &nbsp;|&nbsp;
                                            <?php echo $item['cantidad']; ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="fw-bold">
                                    <?php echo $this->config->item('moneda_simbolo'); ?>
                                    <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>Total</span>
                            <span class="precio">
                                <?php echo $this->config->item('moneda_simbolo'); ?>
                                <?php echo number_format($total, 2); ?>
                            </span>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg" id="btn-pagar">
                                <i class="bi bi-shield-lock-fill me-2"></i>Pagar ahora
                            </button>
                        </div>
                        <p class="text-muted small text-center mt-2 mb-0">
                            <i class="bi bi-lock-fill me-1"></i>Pago 100% seguro
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Formatear número de tarjeta con espacios
document.getElementById('num-tarjeta').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let matches = v.match(/\d{4,16}/g);
    let match = matches && matches[0] || '';
    let parts = [];
    for (let i = 0, len = match.length; i < len; i += 4) {
        parts.push(match.substring(i, i + 4));
    }
    e.target.value = parts.length ? parts.join(' ') : v;
});

function validarDatosEnvio() {
    let valido = true;

    const campos = [
        {
            el: document.querySelector('[name="dni"]'),
            test: function(v) { return /^\d{8}$/.test(v); },
            msg: 'El DNI debe tener exactamente 8 dígitos numéricos.'
        },
        {
            el: document.querySelector('[name="nombres"]'),
            test: function(v) { return v.length > 0; },
            msg: 'Los nombres son requeridos.'
        },
        {
            el: document.querySelector('[name="direccion_envio"]'),
            test: function(v) { return v.length > 0; },
            msg: 'La dirección de envío es requerida.'
        },
        {
            el: document.querySelector('[name="celular"]'),
            test: function(v) { return v.length > 0; },
            msg: 'El celular es requerido.'
        }
    ];

    campos.forEach(function(campo) {
        const val = campo.el.value.trim();
        const feedback = campo.el.nextElementSibling;
        if (!campo.test(val)) {
            campo.el.classList.add('is-invalid');
            campo.el.classList.remove('is-valid');
            if (feedback) feedback.textContent = campo.msg;
            valido = false;
        } else {
            campo.el.classList.remove('is-invalid');
            campo.el.classList.add('is-valid');
        }
    });

    if (!valido) {
        document.querySelector('.is-invalid').focus();
    }

    return valido;
}

// Limpiar estado de error al escribir
['dni','nombres','direccion_envio','celular'].forEach(function(nombre) {
    const el = document.querySelector('[name="' + nombre + '"]');
    if (el) {
        el.addEventListener('input', function() {
            el.classList.remove('is-invalid', 'is-valid');
        });
    }
});

document.getElementById('form-checkout').addEventListener('submit', function(e) {
    if (!validarDatosEnvio()) {
        e.preventDefault();
        return;
    }
    const btn = document.getElementById('btn-pagar');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
    btn.disabled = true;
});
</script>
