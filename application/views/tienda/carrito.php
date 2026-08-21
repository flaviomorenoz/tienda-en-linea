<style>
    .defensa-01{
        border-style:solid;
        border-width:2px;
        border-color:red;
        border-radius:6px;
        padding:4px;
    }
    .estilo-yape{
        font-weight:bold;
    }
    .estilo-courier{
        font-weight:bold;
        font-size:16px;
        display: grid;
        place-items: center;
        height:90px;
    }
</style>
<div class="container">
    <h2 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Mi Carrito</h2>

    <?php if (empty($carrito)): ?>
    <!-- Carrito vacío -->
    <div class="text-center py-5">
        <i class="bi bi-cart-x text-muted" style="font-size:5rem;"></i>
        <h4 class="text-muted mt-3">Tu carrito está vacío</h4>
        <p class="text-muted">Agrega productos desde el catálogo para comenzar.</p>
        <a href="<?php echo base_url(); ?>" class="btn btn-dark btn-lg mt-2">
            <i class="bi bi-grid me-2"></i>Ver catálogo
        </a>
    </div>

    <?php else: ?>
    <form action="<?php echo base_url('carrito/actualizar'); ?>" method="POST">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
               value="<?php echo $this->security->get_csrf_hash(); ?>">

        <!-- Tabla de productos -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:80px;">Foto</th>
                                        <th>Producto</th>
                                        <th class="text-center">Talla</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Unidad</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($carrito as $key => $item): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo base_url("../erp-en-linea/assets/img/productos/".$item['imagen']); ?>"
                                                 alt="<?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                                 class="rounded" width="60" height="70"
                                                 style="object-fit:cover;"
                                            >
                                        </td>
                                        <td>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($item['categoria'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3 py-2">
                                                <?php echo htmlspecialchars($item['talla'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="text-center" style="width:130px;">
                                            <input type="number"
                                                   name="cantidad[<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>]"
                                                   value="<?php echo (int)$item['cantidad']; ?>"
                                                   min="1" max="99"
                                                   class="form-control form-control-sm text-center">
                                        </td>
                                        <td class="text-center" style="width:130px;">
                                            <input type="text" 
                                                name="unidad[<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>]"
                                                value="<?= $item['unidad'] ?>"
                                                class="form-control form-control-sm text-center">
                                        </td>
                                        <td class="text-end text-muted">
                                            <?php echo $this->config->item('moneda_simbolo'); ?>
                                            <?php echo number_format($item['precio'], 2); ?>
                                        </td>
                                        <td class="text-end fw-bold">
                                            <?php echo $this->config->item('moneda_simbolo'); ?>
                                            <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo base_url('carrito/quitar/' . urlencode($key)); ?>"
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('¿Eliminar este producto del carrito?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="<?php echo base_url(); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Seguir comprando
                    </a>
                    <div class="d-flex gap-2">
                        <!--<button type="button" onclick="rellenar()">rellenar</button>-->
                        <a href="#" onclick="ver_modal_paguito()" class="btn btn-primary btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i>Pago verificado
                        </a>
                        <button type="submit" class="btn btn-outline-dark">
                            <i class="bi bi-arrow-repeat me-1"></i>Actualizar
                        </button>
                        <a href="<?php echo base_url('carrito/vaciar'); ?>" class="btn btn-outline-danger"
                           onclick="return confirm('¿Vaciar todo el carrito?')">
                            <i class="bi bi-trash me-1"></i>Vaciar
                        </a>
                    </div>
                </div>
            </div>

            



            <!-- Resumen del pedido -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-semibold">
                        <i class="bi bi-receipt me-2"></i>Resumen del pedido
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span><?php echo $this->config->item('moneda_simbolo'); ?> <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Envío</span>
                            <span class="text-success">A coordinar</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total</span>
                            <span class="precio">
                                <?php echo $this->config->item('moneda_simbolo'); ?>
                                <?php echo number_format($total, 2); ?>
                            </span>
                        </div>
                        <div class="d-grid mt-4">
                            <a href="<?php echo base_url('checkout'); ?>" class="btn btn-dark btn-lg">
                                <i class="bi bi-lock-fill me-2"></i>Total del Pedido
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CUADRO DEL YAPE ------------------>
        <div class="row" style="margin-left:1px!important;">
            <!-- Mensaje de yape -->
            <div class="col-sm-6 col-lg-3" style="padding:4px;margin-top:4px!important;">
                <div class="row defensa-01">
                    <div class="col-sm-6 col-lg-6">
                        <img src="<?= base_url("assets/img/yape.jpeg") ?>" style="height:74px;">
                    </div>
                    <div class="col-sm-6 col-lg-6 estilo-yape" style="padding:16px">
                        991-629-237<br>Rosita Cha*
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4 estilo-courier" style="padding:16px;margin-top:4px!important;">
                Envíos por Shalom, Olva Courier, Marvisur
            </div>
        </div>
    </form>

    <div id="form-pagos" class="row defensa-01" style="display:none">
        <div class="col-sm-12">    
            <form action="<?php echo base_url('pago/procesar'); ?>" method="POST" name="form-checkout" id="form-checkout" enctype="multipart/form-data" novalidate>
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
                                        <input type="text" name="dni" id="dni" class="form-control <?php echo form_error('dni') ? 'is-invalid' : ''; ?>"
                                            placeholder="" maxlength="15"
                                            value="<?php echo set_value('dni'); ?>" required>
                                        <div class="invalid-feedback"><?php echo form_error('dni'); ?></div>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label">Nombres completos <span class="text-danger">*</span></label>
                                        <input type="text" name="nombres" id="nombres" class="form-control <?php echo form_error('nombres') ? 'is-invalid' : ''; ?>"
                                            placeholder="Juan Pérez García"
                                            value="<?php echo set_value('nombres'); ?>" required>
                                        <div class="invalid-feedback"><?php echo form_error('nombres'); ?></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Dirección de envío <span class="text-danger">*</span></label>
                                        <input type="text" name="direccion_envio" id="direccion_envio"
                                            class="form-control <?php echo form_error('direccion_envio') ? 'is-invalid' : ''; ?>"
                                            placeholder="sitio..."
                                            value="<?php echo set_value('direccion_envio'); ?>" required>
                                        <div class="invalid-feedback"><?php echo form_error('direccion_envio'); ?></div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Celular <span class="text-danger">*</span></label>
                                        <input type="tel" name="celular" id="celular"
                                            class="form-control <?php echo form_error('celular') ? 'is-invalid' : ''; ?>"
                                            placeholder="987654321" maxlength="20"
                                            value="<?php echo set_value('celular'); ?>" required>
                                        <div class="invalid-feedback"><?php echo form_error('celular'); ?></div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Comprobante</label>
                                        <input type="file" name="archivo" id="archivo">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Referencia / Observaciones</label>
                                        <textarea name="observaciones" id="observaciones" class="form-control" rows="2"
                                                placeholder="Cerca al parque, piso 2, etc."><?php echo set_value('observaciones'); ?></textarea>
                                    </div>
                                </div>
                                <div class="row g-3" style="margin-top: 10px;">
                                    <div class="col-10 text-center">
                                        <button type="button" onclick="previo(0)" class="btn btn-dark btn-lg">
                                            <i class="bi bi-lock-fill me-2"></i>Pagar ahora
                                        </a>
                                        <input type="hidden" name="tipo_pago" id="tipo_pago" value="1">
                                    </div>
                                    <div class="col-2">
                                        <input type="text" name="id_c" id="id_c" placeholder="Id Conversacion" class="form-control" onblur="traer_datos()">
                                    </div>
                                </div>
                                <div class="row g-3" style="margin-top: 10px;">
                                    <img src="" id="img_pago" class="img-fluid" style="display:none; max-width: 200px; margin: 0 auto;">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Columna derecha: resumen del pedido -->
                </div>
                <div class="row g-4">
                    <div class="col-lg-7 text-center">
                        
                    </div>
                </div>
            </form>
        </div> <!-- del col -->
    </div> <!-- del row --> 

    <?php endif; ?>
</div>
<script>
    function ver_modal_paguito(){
        elemento = document.getElementById("form-pagos");
        elemento.style.display = "block";
        document.getElementById("tipo_pago").value = "0";
    }

    function validarDatosEnvio() {
        let valido = true;

        const campos = [
            {
                el: document.getElementById('dni'),
                test: function(v) { return /^\d{8}$/.test(v); },
                msg: 'El DNI debe tener exactamente 8 dígitos numéricos.'
            },
            {
                el: document.getElementById('nombres'),
                test: function(v) { return v.length > 0; },
                msg: 'Los nombres son requeridos.'
            },
            {
                el: document.getElementById('direccion_envio'),
                test: function(v) { return v.length > 0; },
                msg: 'La dirección de envío es requerida.'
            },
            {
                el: document.getElementById('celular'),
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
            document.querySelector('#form-checkout .is-invalid').focus();
        }

        return valido;
    }

    ['dni','nombres','direccion_envio','celular'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function() {
                el.classList.remove('is-invalid', 'is-valid');
            });
        }
    });

    function previo(){
        if (!validarDatosEnvio()) return;
        document.getElementById("form-checkout").submit();
    }

    function rellenar(){
        document.getElementById("dni").value                = "12345666"
        document.getElementById("nombres").value            = "ALAN GARCIA"
        document.getElementById("direccion_envio").value    = "LOS ALISOS 615"
        document.getElementById("celular").value            = "951564780"
        document.getElementById("observaciones").value      = "CARMELO LA PLAZA"
    }

    function traer_datos(){
        let id_c = document.getElementById("id_c").value;
        if(id_c.trim() === "") return;

        fetch('<?php echo base_url('carrito/actualizar_datos_cliente/'); ?>' + encodeURIComponent(id_c), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            body: new URLSearchParams({
                nombre_cliente: '',
                celular_cliente: ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                document.getElementById("nombres").value = data.nombre_cliente || '';
                document.getElementById("celular").value = data.celular_cliente || '';
                document.getElementById("img_pago").style.display = data.imagenes ? 'block' : 'none';
                document.getElementById("img_pago").src = data.imagenes ? '<?php echo base_url('uploads/'); ?>' + data.imagenes : '';
                console.log(data.nombre_cliente, data.celular_cliente);
            } else {
                alert('Error al obtener datos del cliente: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al obtener los datos del cliente.');
        });
    }
</script>