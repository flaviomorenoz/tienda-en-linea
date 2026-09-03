<style>
    /* =====================================================
       LIBRO DE RECLAMACIONES · Estilo acorde al tema activo
       Base legal: Ley N° 29571, D.S. N° 011-2011-PCM,
       D.S. N° 004-2024-PCM (Libro de Reclamaciones Digital)
       ===================================================== */
    .lr-page {
        font-family: var(--font, 'Poppins', sans-serif);
        color: var(--texto, #3A3735);
        letter-spacing: 0.02em;
    }
    .lr-kicker {
        display: inline-block;
        background: var(--primary, #FA0082);
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        padding: 0.4rem 0.9rem;
        margin-bottom: 1rem;
    }
    .lr-title {
        font-family: 'Fraunces', serif;
        font-weight: 800;
        color: var(--negro, #1D1D1B);
        text-transform: uppercase;
        letter-spacing: 0.01em;
    }
    .lr-subtitle {
        color: var(--texto-muted, #8a8580);
        font-weight: 600;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        font-size: 0.72rem;
    }
    .lr-card {
        border: 1px solid var(--borde, #e8e6e3);
        border-top: 3px solid var(--primary, #FA0082);
        border-radius: 0;
        background: #ffffff;
        padding: 1.6rem 1.5rem;
    }
    .lr-card h5 {
        font-family: 'Fraunces', serif;
        font-weight: 700;
        color: var(--negro, #1D1D1B);
    }
    .lr-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--negro, #1D1D1B);
    }
    .lr-input,
    .lr-input:focus {
        border-radius: 0;
        border-color: var(--borde, #d9d5cf);
        box-shadow: none;
    }
    .lr-input:focus {
        border-color: var(--primary, #FA0082);
    }
    .lr-nota {
        background: var(--light-bg, #f7f6f4);
        border-left: 4px solid var(--primary, #FA0082);
        padding: 1rem 1.2rem;
        font-size: 0.88rem;
        line-height: 1.7;
    }
    .lr-badge-ley {
        font-size: 0.7rem;
        letter-spacing: 0.08em;
    }
    .lr-exito {
        border: 1px solid var(--borde, #e8e6e3);
        border-top: 4px solid #198754;
        border-radius: 0;
        background: #ffffff;
        padding: 2.2rem 1.8rem;
    }
    .lr-exito .lr-codigo {
        font-family: 'Fraunces', serif;
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: var(--negro, #1D1D1B);
        background: var(--light-bg, #f7f6f4);
        border: 1px dashed var(--borde, #d9d5cf);
        display: inline-block;
        padding: 0.5rem 1.4rem;
        margin: 0.4rem 0 1rem;
    }
    .lr-tabla td, .lr-tabla th {
        font-size: 0.9rem;
        border-color: var(--borde, #e8e6e3);
    }
    .lr-tabla th {
        color: var(--texto-muted, #8a8580);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.1em;
    }
    .lr-estado-badge {
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .lr-boton {
        border-radius: 0;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .lr-radio-card {
        cursor: pointer;
        border: 1px solid var(--borde, #d9d5cf);
        border-radius: 0;
        padding: 0.9rem 1.1rem;
        transition: border-color 0.15s, box-shadow 0.15s;
        height: 100%;
    }
    .lr-radio-card:hover {
        border-color: var(--primary, #FA0082);
    }
    .lr-radio-card.active {
        border-color: var(--primary, #FA0082);
        box-shadow: 0 0 0 1px var(--primary, #FA0082) inset;
    }
    .lr-radio-card .tipo-titulo {
        font-weight: 700;
        color: var(--negro, #1D1D1B);
    }
    .lr-radio-card small {
        color: var(--texto-muted, #8a8580);
    }
</style>

<div class="container lr-page py-4">

    <!-- ============ ENCABEZADO ============ -->
    <div class="text-center mb-4">
        <span class="lr-kicker">Atención al consumidor</span>
        <h1 class="lr-title">Libro de Reclamaciones</h1>
        <p class="mx-auto mt-2" style="max-width:760px;">
            De acuerdo con el <strong>artículo 150° de la Ley N° 29571</strong> (Código de Protección y
            Defensa del Consumidor) y su reglamento (<strong>D.S. N° 011-2011-PCM</strong>), todo consumidor
            tiene derecho a presentar una hoja de reclamación o queja. Este es nuestro
            <strong>Libro de Reclamaciones Digital</strong> (D.S. N° 004-2024-PCM).
        </p>
    </div>

    <?php if (isset($registrado) && $registrado && !empty($reclamo)): ?>
    <!-- ============ CONFIRMACIÓN DE REGISTRO ============ -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="lr-exito">
                <div class="text-center mb-3">
                    <i class="bi bi-check-circle-fill" style="font-size:3rem; color:#198754;"></i>
                    <h2 class="lr-title h3 mt-2 mb-1">¡Su solicitud fue registrada con éxito!</h2>
                    <p class="text-muted mb-1">Guarde el siguiente código para consultar el estado de su solicitud.</p>
                    <div class="lr-codigo"><?php echo htmlspecialchars($reclamo->codigo, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="lr-nota mb-4">
                    <i class="bi bi-clock-history me-2"></i>
                    De acuerdo con el <strong>artículo 152° de la Ley N° 29571</strong>, el proveedor debe dar
                    respuesta a su solicitud en un plazo máximo de
                    <strong><?php echo (int)($plazo_dias ?? 15); ?> días hábiles</strong> contados desde la fecha de
                    recepción, pudiendo ampliarse por única vez por un período adicional de
                    <strong><?php echo (int)($plazo_dias ?? 15); ?> días hábiles</strong> (con aviso previo y justificado).
                </div>

                <table class="table lr-tabla mb-4">
                    <tbody>
                        <tr>
                            <th scope="row" style="width:40%;">Código</th>
                            <td><?php echo htmlspecialchars($reclamo->codigo, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Tipo</th>
                            <td><?php echo $reclamo->tipo === 'QUEJA' ? 'Queja' : 'Reclamo'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Fecha de registro</th>
                            <td><?php echo date('d/m/Y H:i', strtotime($reclamo->created_at)); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Producto o servicio</th>
                            <td><?php echo htmlspecialchars($reclamo->producto_servicio, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <?php if ($reclamo->monto_reclamado !== NULL && $reclamo->monto_reclamado !== ''): ?>
                        <tr>
                            <th scope="row">Monto reclamado</th>
                            <td><?php echo $this->config->item('moneda_simbolo'); ?> <?php echo number_format((float)$reclamo->monto_reclamado, 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th scope="row">Estado</th>
                            <td>
                                <span class="badge bg-secondary lr-estado-badge"><?php echo $reclamo->estado; ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="<?php echo base_url('libro-reclamaciones/verificar'); ?>" class="btn btn-dark lr-boton">
                        <i class="bi bi-search me-2"></i>Consultar estado
                    </a>
                    <a href="<?php echo base_url(); ?>" class="btn btn-outline-secondary lr-boton">
                        <i class="bi bi-shop me-2"></i>Volver a la tienda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ============ FORMULARIO ============ -->
    <div class="row g-4">
        <!-- Columna del formulario -->
        <div class="col-lg-8">
            <div class="lr-card">
                <h5 class="mb-1"><i class="bi bi-journal-text me-2"></i>Hoja de Reclamación</h5>
                <p class="text-muted small mb-4">Complete los siguientes datos (<span class="text-danger">*</span> obligatorios).</p>

                <?php echo form_open('libro-reclamaciones/enviar', array('id' => 'form-libro-reclamaciones', 'novalidate' => '')); ?>

                <!-- Tipo de solicitud -->
                <div class="mb-3">
                    <label class="form-label lr-label">Tipo de solicitud <span class="text-danger">*</span></label>
                    <div class="row g-3" id="grupo-tipo">
                        <div class="col-md-6">
                            <label class="lr-radio-card d-block mb-0" data-tipo="RECLAMO">
                                <input type="radio" name="tipo" value="RECLAMO"
                                       class="form-check-input me-2" <?php echo set_radio('tipo', 'RECLAMO', TRUE); ?>>
                                <span class="tipo-titulo">Reclamo</span>
                                <br><small>Disconformidad con el producto o servicio adquirido (incluye monto).</small>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="lr-radio-card d-block mb-0" data-tipo="QUEJA">
                                <input type="radio" name="tipo" value="QUEJA"
                                       class="form-check-input me-2" <?php echo set_radio('tipo', 'QUEJA'); ?>>
                                <span class="tipo-titulo">Queja</span>
                                <br><small>Disconformidad con la atención al cliente o el servicio brindado.</small>
                            </label>
                        </div>
                    </div>
                    <div class="text-danger small mt-1"><?php echo form_error('tipo'); ?></div>
                </div>

                <hr class="my-4">

                <!-- Datos del consumidor -->
                <h6 class="lr-subtitle mb-3">1. Datos del consumidor</h6>

                <div class="mb-3">
                    <label class="form-label lr-label" for="nombres">Nombres y apellidos <span class="text-danger">*</span></label>
                    <input type="text" name="nombres" id="nombres" maxlength="250"
                           class="form-control lr-input <?php echo form_error('nombres') ? 'is-invalid' : ''; ?>"
                           placeholder="Ej: Juan Carlos Pérez García"
                           value="<?php echo set_value('nombres'); ?>" required>
                    <div class="invalid-feedback"><?php echo form_error('nombres'); ?></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label lr-label" for="tipo_documento">Tipo de documento <span class="text-danger">*</span></label>
                        <select name="tipo_documento" id="tipo_documento"
                                class="form-select lr-input <?php echo form_error('tipo_documento') ? 'is-invalid' : ''; ?>" required>
                            <option value="DNI" <?php echo set_select('tipo_documento', 'DNI', TRUE); ?>>DNI</option>
                            <option value="CE"  <?php echo set_select('tipo_documento', 'CE'); ?>>Carné de Extranjería</option>
                            <option value="PASAPORTE" <?php echo set_select('tipo_documento', 'PASAPORTE'); ?>>Pasaporte</option>
                            <option value="OTRO" <?php echo set_select('tipo_documento', 'OTRO'); ?>>Otro</option>
                        </select>
                        <div class="invalid-feedback"><?php echo form_error('tipo_documento'); ?></div>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label lr-label" for="numero_documento">Número de documento <span class="text-danger">*</span></label>
                        <input type="text" name="numero_documento" id="numero_documento" maxlength="20"
                               class="form-control lr-input <?php echo form_error('numero_documento') ? 'is-invalid' : ''; ?>"
                               placeholder="Ej: 12345678"
                               value="<?php echo set_value('numero_documento'); ?>" required>
                        <div class="invalid-feedback"><?php echo form_error('numero_documento'); ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label lr-label" for="domicilio">Domicilio</label>
                    <input type="text" name="domicilio" id="domicilio" maxlength="255"
                           class="form-control lr-input <?php echo form_error('domicilio') ? 'is-invalid' : ''; ?>"
                           placeholder="Ej: Av. Los Olivos 123, San Miguel"
                           value="<?php echo set_value('domicilio'); ?>">
                    <div class="invalid-feedback"><?php echo form_error('domicilio'); ?></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label lr-label" for="telefono">Teléfono / Celular</label>
                        <input type="tel" name="telefono" id="telefono" maxlength="30"
                               class="form-control lr-input <?php echo form_error('telefono') ? 'is-invalid' : ''; ?>"
                               placeholder="Ej: 987654321"
                               value="<?php echo set_value('telefono'); ?>">
                        <div class="invalid-feedback"><?php echo form_error('telefono'); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label lr-label" for="email">Correo electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" maxlength="150"
                               class="form-control lr-input <?php echo form_error('email') ? 'is-invalid' : ''; ?>"
                               placeholder="Ej: juan@correo.com"
                               value="<?php echo set_value('email'); ?>" required>
                        <div class="invalid-feedback"><?php echo form_error('email'); ?></div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label lr-label" for="departamento">Departamento</label>
                        <input type="text" name="departamento" id="departamento" maxlength="100"
                               class="form-control lr-input" placeholder="Ej: Lima"
                               value="<?php echo set_value('departamento'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label lr-label" for="provincia">Provincia</label>
                        <input type="text" name="provincia" id="provincia" maxlength="100"
                               class="form-control lr-input" placeholder="Ej: Lima"
                               value="<?php echo set_value('provincia'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label lr-label" for="distrito">Distrito</label>
                        <input type="text" name="distrito" id="distrito" maxlength="100"
                               class="form-control lr-input" placeholder="Ej: San Miguel"
                               value="<?php echo set_value('distrito'); ?>">
                    </div>
                </div>

                <hr class="my-4">

                <!-- Identificación del bien o servicio -->
                <h6 class="lr-subtitle mb-3">2. Identificación del bien o servicio</h6>

                <div class="mb-3">
                    <label class="form-label lr-label" for="producto_servicio">Producto o servicio adquirido <span class="text-danger">*</span></label>
                    <input type="text" name="producto_servicio" id="producto_servicio" maxlength="255"
                           class="form-control lr-input <?php echo form_error('producto_servicio') ? 'is-invalid' : ''; ?>"
                           placeholder="Ej: Polo básico blanco - talla M"
                           value="<?php echo set_value('producto_servicio'); ?>" required>
                    <div class="invalid-feedback"><?php echo form_error('producto_servicio'); ?></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label lr-label" for="numero_pedido">N° de pedido / boleta / factura</label>
                        <input type="text" name="numero_pedido" id="numero_pedido" maxlength="50"
                               class="form-control lr-input" placeholder="Si lo tiene a la mano (opcional)"
                               value="<?php echo set_value('numero_pedido'); ?>">
                    </div>
                    <div class="col-md-6" id="campo-monto">
                        <label class="form-label lr-label" for="monto_reclamado">
                            Monto reclamado (S/) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text lr-input"><?php echo $this->config->item('moneda_simbolo'); ?></span>
                            <input type="text" name="monto_reclamado" id="monto_reclamado" maxlength="12"
                                   class="form-control lr-input <?php echo form_error('monto_reclamado') ? 'is-invalid' : ''; ?>"
                                   placeholder="0.00" inputmode="decimal"
                                   value="<?php echo set_value('monto_reclamado'); ?>">
                        </div>
                        <div class="invalid-feedback"><?php echo form_error('monto_reclamado'); ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label lr-label" for="detalle">
                        Detalle de su reclamo o queja <span class="text-danger">*</span>
                    </label>
                    <textarea name="detalle" id="detalle" rows="6" maxlength="2000"
                              class="form-control lr-input <?php echo form_error('detalle') ? 'is-invalid' : ''; ?>"
                              placeholder="Explique de manera clara y ordenada lo sucedido..." required><?php echo set_value('detalle'); ?></textarea>
                    <div class="d-flex justify-content-between">
                        <div class="invalid-feedback"><?php echo form_error('detalle'); ?></div>
                        <small class="text-muted ms-auto" id="contador-detalle">0/2000</small>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Declaración -->
                <div class="form-check mb-4">
                    <input type="checkbox" name="acepto" value="1" id="acepto"
                           class="form-check-input <?php echo form_error('acepto') ? 'is-invalid' : ''; ?>"
                           <?php echo set_checkbox('acepto', '1'); ?> required>
                    <label class="form-check-label small" for="acepto">
                        Declaro que los datos consignados son verídicos y autorizo el tratamiento de mis datos
                        personales conforme a la <strong>Ley N° 29733</strong>, exclusivamente para la atención de mi
                        solicitud. <span class="text-danger">*</span>
                    </label>
                    <div class="invalid-feedback"><?php echo form_error('acepto'); ?></div>
                </div>

                <div class="d-grid d-sm-flex gap-2 justify-content-sm-end">
                    <button type="submit" class="btn btn-dark lr-boton btn-lg" id="btn-enviar-libro">
                        <i class="bi bi-send-fill me-2"></i>Presentar hoja de reclamación
                    </button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>

        <!-- Columna lateral: información normativa -->
        <div class="col-lg-4">
            <div class="lr-card mb-4">
                <h5 class="mb-3"><i class="bi bi-shop me-2"></i>Datos del proveedor</h5>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <i class="bi bi-building me-2 text-muted"></i>
                        <strong>Razón social:</strong>
                        <?php echo htmlspecialchars($this->config->item('tienda_razon_social') ?: $this->config->item('tienda_nombre'), ENT_QUOTES, 'UTF-8'); ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-card-text me-2 text-muted"></i>
                        <strong>RUC:</strong>
                        <?php $ruc = $this->config->item('tienda_ruc'); echo $ruc ? htmlspecialchars($ruc, ENT_QUOTES, 'UTF-8') : '<em class="text-muted">Completar en Ajustes</em>'; ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-geo-alt me-2 text-muted"></i>
                        <strong>Domicilio:</strong>
                        <?php $dom = $this->config->item('tienda_domicilio_fiscal'); echo $dom ? htmlspecialchars($dom, ENT_QUOTES, 'UTF-8') : '<em class="text-muted">Completar en Ajustes</em>'; ?>
                    </li>
                    <li>
                        <i class="bi bi-envelope me-2 text-muted"></i>
                        <strong>Correo:</strong> <?php echo htmlspecialchars($this->config->item('tienda_email'), ENT_QUOTES, 'UTF-8'); ?>
                    </li>
                </ul>
            </div>

            <div class="lr-nota mb-4">
                <h6 class="fw-bold">¿Reclamo o queja?</h6>
                <p class="mb-1"><strong>Reclamo:</strong> disconformidad con el producto o servicio; se indica el monto.</p>
                <p class="mb-0"><strong>Queja:</strong> disconformidad con la atención al cliente; no requiere monto.</p>
            </div>

            <div class="lr-card mb-4">
                <h5 class="mb-3"><i class="bi bi-clock me-2"></i>Plazo de atención</h5>
                <p class="small mb-0">
                    Responderemos su solicitud en un máximo de <strong>15 días hábiles</strong> (art. 152° de la
                    Ley N° 29571), ampliable por única vez por 15 días hábiles adicionales con aviso previo.
                </p>
            </div>

            <div class="lr-card mb-4">
                <h5 class="mb-3"><i class="bi bi-shield-check me-2"></i>Si no está conforme</h5>
                <p class="small mb-0">
                    Puede presentar su reclamo ante el <strong>INDECOPI</strong> (www.consumidor.gob.pe) si no se
                    encuentra conforme con la respuesta del proveedor o si no hay respuesta en el plazo legal.
                </p>
            </div>

            <div class="text-center">
                <a href="<?php echo base_url('libro-reclamaciones/verificar'); ?>" class="btn btn-outline-dark lr-boton w-100">
                    <i class="bi bi-search me-2"></i>Consultar estado de mi solicitud
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
(function() {
    var form      = document.getElementById('form-libro-reclamaciones');
    var grupoTipo = document.getElementById('grupo-tipo');
    var campoMonto = document.getElementById('campo-monto');
    var inputMonto = document.getElementById('monto_reclamado');
    var detalle    = document.getElementById('detalle');
    var contador   = document.getElementById('contador-detalle');

    // Activar visualmente la tarjeta de tipo seleccionada
    function marcarTipo() {
        var radios = form ? form.querySelectorAll('input[name="tipo"]') : [];
        radios.forEach(function(r) {
            var card = r.closest('.lr-radio-card');
            if (card) {
                card.classList.toggle('active', r.checked);
            }
        });
        // El monto solo aplica a reclamos
        var tipoSeleccionado = form ? (form.querySelector('input[name="tipo"]:checked') || {}).value : 'RECLAMO';
        if (campoMonto) {
            campoMonto.style.display = tipoSeleccionado === 'RECLAMO' ? '' : 'none';
        }
        if (inputMonto) {
            inputMonto.required = tipoSeleccionado === 'RECLAMO';
        }
    }
    if (grupoTipo) {
        grupoTipo.addEventListener('change', marcarTipo);
    }
    marcarTipo();

    // Contador de caracteres del detalle
    if (detalle && contador) {
        detalle.addEventListener('input', function() {
            contador.textContent = detalle.value.length + '/2000';
        });
    }

    // Evitar doble envío
    if (form) {
        form.addEventListener('submit', function() {
            var btn = document.getElementById('btn-enviar-libro');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
            }
        });
    }
})();
</script>





