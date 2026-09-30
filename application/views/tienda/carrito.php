<?php
$fecha = new DateTime();
$fecha->modify('+1 day');
$fechin = $fecha->format('d-m-Y H:i:s');
$timestamp = DateTime::createFromFormat('d-m-Y H:i:s', $fechin)->getTimestamp();
?>
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
                                    <?php $i = 0; foreach ($carrito as $key => $item): ?>
                                    <!-- data-precio: precio unitario que se muestra en la columna Precio.
                                         recalcular_resumen() lo usa para el subtotal en vivo (cantidad x precio). -->
                                    <tr data-precio="<?php echo (float)$item['precio']; ?>">
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
                                            <input type="hidden"
                                                   name="clave[<?php echo $i; ?>]"
                                                   value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">

                                            <input type="number"
                                                   name="cantidad[<?php echo $i; ?>]"
                                                   value="<?php echo (int)$item['cantidad']; ?>"
                                                   data-original="<?php echo (int)$item['cantidad']; ?>"
                                                   min="1" max="99"
                                                   class="form-control form-control-sm text-center">
                                        </td>
                                        <td class="text-center" style="width:130px;">
                                            <input type="text" 
                                                name="unidad[<?php echo $i; ?>]"
                                                value="<?= $item['unidad'] ?>"
                                                class="form-control form-control-sm text-center">
                                        </td>
                                        <td class="text-end text-muted">
                                            <?php echo $this->config->item('moneda_simbolo'); ?>
                                            <?php echo number_format($item['precio'], 2); ?>
                                        </td>
                                        <td class="text-end fw-bold">
                                            <?php echo $this->config->item('moneda_simbolo'); ?>
                                            <span class="subtotal-linea"><?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?php echo base_url('carrito/quitar/' . urlencode($key)); ?>"
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('¿Eliminar este producto del carrito?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php $i++; endforeach; ?>
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
                        <!--<button type="button" id="btn_pagar0" class="btn btn-primary btn-sm" onclick="crear_orden()" style="padding-top:7px;">
                            <i class="bi bi-arrow-repeat me-1"></i>PREPARAR
                        </button>-->
                        <a href="#" id="btn_pagar" class="btn btn-primary btn-sm" style="padding-top:7px;">
                            <i class="bi bi-arrow-repeat me-1"></i>PAGAR
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
                            <span><?php echo $this->config->item('moneda_simbolo'); ?> <span id="resumen-subtotal"><?php echo number_format($total, 2); ?></span></span>
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
                                <span id="resumen-total"><?php echo number_format($total, 2); ?></span>
                            </span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- AQUI VA EL ANTERIOR PROCESO -->
            
    </form>

    <div id="form-pagos" class="row" style="display:none">
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
                                        <label class="form-label">Email<span class="text-danger">*</span></label>
                                        <input type="email" name="correo" id="correo"
                                            class="form-control <?php echo form_error('correo') ? 'is-invalid' : ''; ?>"
                                            placeholder="micorreo@" maxlength="80"
                                            value="<?php echo set_value('correo'); ?>" required>
                                        <div class="invalid-feedback"><?php echo form_error('correo'); ?></div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Celular <span class="text-danger">*</span></label>
                                        <input type="tel" name="celular" id="celular"
                                            class="form-control <?php echo form_error('celular') ? 'is-invalid' : ''; ?>"
                                            placeholder="987654321" maxlength="20"
                                            value="<?php echo set_value('celular'); ?>" required>
                                        <div class="invalid-feedback"><?php echo form_error('celular'); ?></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Referencia / Observaciones</label>
                                        <textarea name="observaciones" id="observaciones" class="form-control" rows="2"
                                                placeholder="Cerca al parque, piso 2, etc."><?php echo set_value('observaciones'); ?></textarea>
                                    </div>
                                </div>
                                <div class="row g-3" style="margin-top: 10px;">
                                    <div class="col-10 text-center">
                                        <?php /* El cobro real se hace con el botón PAGAR (pasarela Culqi).
                                                 Se desactiva este botón para NO usar el flujo de pago
                                                 simulado de Pago::procesar (Pasarela_model::simular_pago),
                                                 que marcaba el pedido como "Pagado" sin cobrar.
                                        <button type="button" onclick="previo(0)" class="btn btn-dark btn-lg">
                                            <i class="bi bi-lock-fill me-2"></i>Pagar ahora
                                        </button>
                                        */ ?>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-shield-lock me-1"></i>
                                            Al pulsar <strong>PAGAR</strong> se abrirá la pasarela segura de Culqi.
                                        </p>
                                        <input type="hidden" name="tipo_pago" id="tipo_pago" value="1">
                                    </div>
                                    <div class="col-2">
                                        
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

    <script src="https://js.culqi.com/checkout-js"></script>
</div>
<script>
    // Total del carrito calculado por el servidor (Carrito::_calcular_total).
    // De aquí en adelante se mantiene en vivo: cantidad x precio mostrado en cada línea.
    let total_carrito = <?php echo (float) $total; ?>;

    // Nombres del campo y de la cookie CSRF (config.php: csrf_token_name / csrf_cookie_name)
    const csrf_token_name  = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const csrf_cookie_name = '<?php echo $this->config->item('csrf_cookie_name'); ?>';

    // Formatea un monto igual que number_format($monto, 2): 1,234.56
    function formato_monto(monto){
        monto = Math.round((parseFloat(monto) || 0) * 100) / 100;
        const partes = monto.toFixed(2).split('.');
        partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return partes.join('.');
    }

    // Subtotal en vivo de cada línea (cantidad x precio mostrado) y totales del resumen
    /* Culqi Checkout v4 (js.culqi.com/checkout-js) NO expone Culqi.settings(): el monto se lee
       del objeto real de configuración cada vez que se abre el modal (open() -> watch de
       _isOpen -> createAndMountApp() -> culqiConfig.getUrlParamaters().amount). Por eso el monto
       se escribe en esa configuración y se verifica en el objeto que usa el checkout. */
    function actualizar_monto_culqi(monto_centimos){
        if (typeof Culqi === 'undefined' || !Culqi) return false;

        monto_centimos = Math.max(0, Math.round(parseFloat(monto_centimos) || 0));

        try {
            const configuracion = Culqi.culqiConfig;
            let aplicado = false;

            if (configuracion) {
                if (typeof configuracion.setConfig === 'function') {
                    configuracion.setConfig({ settings: { amount: monto_centimos } });
                    aplicado = true;
                } else {
                    configuracion.settings = { amount: monto_centimos };
                    aplicado = true;
                }
            }

            // Verificación: si la configuración interna no tomó el valor, se escribe el objeto real
            if (typeof Culqi.getSettingsReal === 'function') {
                const real = Culqi.getSettingsReal();

                if (real) {
                    if (Math.round(parseFloat(real.amount) || 0) !== monto_centimos) {
                        real.amount = monto_centimos;
                        console.warn('Monto de Culqi ajustado por vía directa.');
                    }

                    aplicado = Math.round(parseFloat(real.amount) || 0) === monto_centimos;
                }
            }

            return aplicado;
        } catch (e) {
            console.warn('No se pudo actualizar el monto en Culqi:', e);
            return false;
        }
    }

    function recalcular_resumen(){
        let suma = 0;

        document.querySelectorAll('tr[data-precio]').forEach(function(tr){
            const precio = parseFloat(tr.getAttribute('data-precio')) || 0;
            const input  = tr.querySelector('input[type="number"]');

            let cantidad = input ? parseInt(input.value, 10) : 1;
            if (isNaN(cantidad) || cantidad < 1) cantidad = 1;
            if (cantidad > 99) cantidad = 99;

            const subtotal = precio * cantidad;
            suma += subtotal;

            const celda = tr.querySelector('.subtotal-linea');
            if (celda) celda.textContent = formato_monto(subtotal);
        });

        total_carrito = Math.round(suma * 100) / 100;

        const resumen_subtotal = document.getElementById('resumen-subtotal');
        const resumen_total    = document.getElementById('resumen-total');
        if (resumen_subtotal) resumen_subtotal.textContent = formato_monto(total_carrito);
        if (resumen_total)    resumen_total.textContent    = formato_monto(total_carrito);

        // Monto de la pasarela (en céntimos), igual al total que se muestra
        settings.amount = Math.round(total_carrito * 100);
        actualizar_monto_culqi(settings.amount);
    }

    // ¿El cliente cambió alguna cantidad respecto a la que se pintó al cargar la página?
    function hay_cambios_cantidad(){
        let hay = false;
        document.querySelectorAll('input[name^="cantidad"]').forEach(function(input){
            const original = parseInt(input.getAttribute('data-original'), 10) || 1;
            if ((parseInt(input.value, 10) || 1) !== original) hay = true;
        });
        return hay;
    }

    // Tras guardar, las cantidades enviadas pasan a ser las de referencia
    function marcar_cantidades_originales(){
        document.querySelectorAll('input[name^="cantidad"]').forEach(function(input){
            input.setAttribute('data-original', String(parseInt(input.value, 10) || 1));
        });
    }

    // Token CSRF vigente, leído de la cookie (config.php: cookie_httponly = FALSE)
    function token_desde_cookie(){
        const galletas = document.cookie ? document.cookie.split('; ') : [];
        for (let i = 0; i < galletas.length; i++) {
            const partes = galletas[i].split('=');
            if (partes[0] === csrf_cookie_name) {
                return decodeURIComponent(partes.slice(1).join('='));
            }
        }
        return '';
    }

    /* csrf_regenerate = TRUE y carrito/actualizar NO está en csrf_exclude_uris: cada POST
       validado rota el token. Si no se refresca el campo oculto, el botón "Actualizar"
       del carrito fallaría con "The action you have requested is not allowed." */
    function actualizar_token_csrf(html){
        let nuevo = '';

        if (html) {
            try {
                const doc   = new DOMParser().parseFromString(html, 'text/html');
                const campo = doc.querySelector('input[name="' + csrf_token_name + '"]');
                if (campo) nuevo = campo.value;
            } catch (e) {
                nuevo = '';
            }
        }

        if (!nuevo) nuevo = token_desde_cookie();
        if (!nuevo) return;

        document.querySelectorAll('input[name="' + csrf_token_name + '"]').forEach(function(input){
            input.value = nuevo;
        });
    }

    /* Guarda en la sesión (carrito/actualizar) las cantidades editadas en la vista para que
       lo mostrado, lo guardado y lo cobrado coincidan: Carrito::recibe_token() cobra el
       total de la sesión (Carrito::_calcular_total), nunca un monto enviado por el cliente. */
    /* Total del carrito (en céntimos) tal como lo calculó el servidor en el HTML devuelto por
       carrito/actualizar (ver Carrito::_calcular_total). Devuelve null si no se pudo leer. */
    function total_servidor_centimos(html){
        if (!html) return null;

        try {
            const doc   = new DOMParser().parseFromString(html, 'text/html');
            const nodo  = doc.getElementById('resumen-total');
            const monto = parseFloat(String(nodo ? nodo.textContent : '').replace(/[^0-9.]/g, ''));

            return isNaN(monto) ? null : Math.round(monto * 100);
        } catch (e) {
            return null;
        }
    }

    function sincronizar_carrito(){
        return new Promise(function(resolve, reject){
            const formulario = document.querySelector('form[action$="carrito/actualizar"]');

            // Sin formulario o sin cambios pendientes: no hay nada que guardar
            if (!formulario || !hay_cambios_cantidad()) {
                resolve(false);
                return;
            }

            fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
            .then(function(response){
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(html){
                actualizar_token_csrf(html);
                /* Devuelve el HTML del carrito ya guardado: con él se verifica, antes de abrir la
                   pasarela, que el total del servidor coincide con el total que se muestra. */
                resolve(html || '');
            })
            .catch(function(error){
                console.error('No se pudo guardar el carrito:', error);
                reject(error);
            });
        });
    }
    function ver_modal_paguito(){
        elemento = document.getElementById("form-pagos");
        elemento.style.display = "block";
        document.getElementById("tipo_pago").value = "0";
    }

    // Muestra el bloque de datos de envío (viene oculto) y lleva al usuario hasta él
    function mostrar_datos_envio(){
        const elemento = document.getElementById("form-pagos");
        if (!elemento) return;
        elemento.style.display = "block";
        elemento.scrollIntoView({behavior: "smooth", block: "center"});
    }

    // Habilita/deshabilita el botón PAGAR mientras se procesa el cobro
    function btn_procesando(activo){
        const boton = document.getElementById("btn_pagar");
        if (!boton) return;
        if (activo) {
            boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';
            boton.style.pointerEvents = "none";
            boton.style.opacity = "0.7";
        } else {
            boton.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>PAGAR';
            boton.style.pointerEvents = "";
            boton.style.opacity = "";
        }
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

    const settings = {
        title: "Culqi Store (BELLAROSSE)",
        currency: "PEN",
        amount: Math.round(total_carrito * 100), // monto en céntimos, según el total del carrito
        order: "",
        xculqirsaid: "<?= getenv('CULQI_LLAVE_RSA_ID') ?>",
        rsapublickey: "<?= getenv('CULQI_LLAVE_RSA') ?>" 
    };

    const publicKey = '<?= getenv('CULQI_LLAVE_PUBLICA') ?>';

    // las opciones se ordenan según se configuren
    const paymentMethods = {
        tarjeta: false,
        yape: true,
        billetera: false,
        bancaMovil: false,
        agente: false,
        cuotealo: false
    };

    const options = {
        lang: "auto",
        installments: true,
        modal: true,
        container: "#culqi-container", // Opcional
        paymentMethods: paymentMethods,
        paymentMethodsSort: Object.keys(paymentMethods) // las opciones se ordenan según se configuren en paymentMethods
    };

    const client = {
        email: "test2@demo.com"
    };

    const appearance = {
        theme: "default",
        hiddenCulqiLogo: false,
        hiddenBannerContent: false,
        hiddenBanner: false,
        hiddenToolBarAmount: false,
        menuType: "sidebar", // default/sidebar / sliderTop / select
        buttonCardPayText: "Pagar tal monto", // hexadecimal
        logo: "http://www.childrensociety.ms/wp-content/uploads/2019/11/MCS-Logo-2019-no-text.jpg",
        defaultStyle: {
            bannerColor: "blue", // hexadecimal
            buttonBackground: "yellow", // hexadecimal
            menuColor: "pink", // hexadecimal
            linksColor: "green", // hexadecimal
            buttonTextColor: "blue", // hexadecimal
            priceColor: "red"
        },
        variables: {
            fontFamily: "Verdana",
            fontWeightNormal: "500",
            borderRadius: "8px",
            colorBackground: "#0A2540",
            colorPrimary: "#EFC078",
            colorPrimaryText: "#1A1B25",
            colorText: "white",
            colorTextSecondary: "white",
            colorTextPlaceholder: "#727F96",
            colorIconTab: "white",
            colorLogo: "dark",
            soyUnaVariable: "rgb(100,100,100)"
        },
        rules: {
            ".Culqi-Main-Container": {
                background: "rgb(226,132,199)",
                fontFamily: "var(--fontFamily)"
            },
            ".Culqi-ToolBanner": {
                background: "rgb(223,44,172)",
                fontFamily: "var(--fontFamily)",
                color: "white"
            },
            // cambia el color del texto y del ícono
            ".Culqi-Toolbar-Price": {
                color: "red",
                fontFamily: "var(--fontFamily)"
            },
            // cambia el color solo del ícono
            ".Culqi-Toolbar-Price .Culqi-Icon": {
                color: "blue"
            },
            ".Culqi-Main-Method": {
                background: "rgb(250,210,239)",
                padding: "10px 20px",
                color: "rgb(100,100,100)"
            },

            // aplica color al texto del link y al Icon del link
            ".Culqi-Text-Link": {
                color: "red"
            },
            // Solo aplica color al Icon del link
            ".Culqi-Text-Link .Culqi-Icon": {
                color: "rgb(100,100,100)"
            },
            // Message, color aplica para text e ícono
            ".Culqi-message": {
                color: "rgb(100,100,100)"
            },
            // cambia el color solo del ícono
            ".Culqi-message .Culqi-Icon": {
                color: "red"
            },
            ".Culqi-message-warning": {
                background: "white",
                color: "orange"
            },
            ".Culqi-message-info": {
                background: "white",
                color: "black"
            },
            ".Culqi-message-error": {
                background: "black",
                color: "yellow"
            },
            ".Culqi-message-error .Culqi-Icon": {
                color: "yellow"
            },

            // aplica a los labels
            ".Culqi-Label": {
                color: "var(--soyUnaVariable)",
                marginBottom: "20px"
            },
            ".Culqi-Input": {
                border: "1px solid red",
                color: "var(--soyUnaVariable)"
            },
            ".Culqi-Input:focus": {
                border: "2px solid black"
            },
            ".Culqi-Input.input-valid": {
                border: "1px solid pink",
                background: "black",
                color: "var(--colorText)"
            },
            ".Culqi-Input-Icon-Spinner": {
                color: "red"
            },
            ".Culqi-Input-Select": {
                border: "1px solid red",
                color: "blue"
            },
            // aplica para al hacer hover en los options del select
            ".Culqi-Input-Select-Options-Hover": {
                color: "red",
                background: "black"
            },
            // aplica para el seleccionado al ser activado
            ".Culqi-Input-Select-Selected": {
                color: "green"
            },
            ".Culqi-Input-Select.active": {
                // aplica cuando le das click al control
                border: "1px solid red",
                background: "pink"
            },
            // aplica al listado de cuotas
            ".Culqi-Input-Select-Options": {
                background: "gray"
            },
            // aplica a los botones
            ".Culqi-Button": {
                background: "red"
            },

            //--------Menu GENERALES----------------
            // el color se aplica para el texto y el ícono del menú
            ".Culqi-Menu": {
                color: "blue"
                //background: "white",
            },

            // el color se aplica para el ícono del menú
            ".Culqi-Menu .Culqi-Icon": {
                color: "green"
            },
            //-------FIN Menu GENERALES----------------

            //--------- MENU SELECT-------------
            // aplica cuando el select esta seleccionado
            ".Culqi-Menu-Selected": {
                //background: "orange",
                color: "#D621A5"
                //border: "1px solid white",
            },
            ".Culqi-Menu-Selected .Culqi-Icon": {
                //background: "orange",
                color: "red"
                //border: "1px solid white",
            },
            // aplica cuando para las opciones del select menú
            ".Culqi-Menu-Options": {
                background: "orange"
            },
            // aplica para las opciones del select menú cuando se hace hover
            ".Culqi-Menu-Options-Hover": {
                background: "green",
                color: "red"
            },
            // aplica para los ICONOS de las opciones del select menú cuando se hace hover
            ".Culqi-Menu-Options-Hover .Culqi-Icon": {
                color: "blue"
            }

            //--------- FIN SELECT-------------

            //----------------- MENU SLIDERTOP Y SIDEBAR----------------------
            /*
            ".Culqi-Menu-Item": {
                background: "black",
                color: "red",
            },

            // cambia el color para el item menu, tanto texto e ícono seleccionado (no aplica en el select menu)
            ".Culqi-Menu-Item.active": {
                color: "white",
                //border: "1px solid white",
            },
            // cambia el color para el ICONO del item menu seleccionado (no aplica en el select menu)
            ".Culqi-Menu-Item.active .Culqi-Icon": {
                color: "blue",
            },

            // MODIFICA EL TEXTO DEL MENÚ(no aplica al menú select)
            ".Culqi-Menu-Item-Text": { // reemplaza a la clase .Culqi-Menu-Item
                "font-size": "12px",
                color: "green",
            },


            // cambia el color de los ICONOS ARROW DE sliderTop
            ".Culqi-Menu .Culqi-Icon-Arrow": {
                color: "blue",
            },
            // CAMBIA EL COLOR DE LA BARRA LATERAL DE SIDEBAR
            ".Culqi-Menu-Item.active .Culqi-Bar": {
                background: "blue"
            },
            */
        }
    };

    function crear_orden(){ // nombre, apellido, correo, fono, dni

        let nombre      = document.getElementById("nombres").value
        let apellido    = document.getElementById("nombres").value
        let correo      = document.getElementById("correo").value
        let fono        = document.getElementById("celular").value
        let dni        = document.getElementById("dni").value

        let total       = document.getElementById("resumen-total").innerHTML * 100

        console.log(`. . . ${nombre} ${apellido} ${correo} ${fono} ${dni}. . . `)
        
        const data = JSON.stringify({
          "amount": total,
          "currency_code": "PEN",
          "description": "Varios productos",
          "order_number": "#id-<?= date("YmdHis") ?>",
          "expiration_date": "<?= $timestamp ?>",
          "client_details": {
            "first_name": nombre,
            "last_name": apellido,
            "email": correo,
            "phone_number": fono
          },
          "confirm": true,
          "metadata": {
            "dni": dni
          }
        });

        const xhr = new XMLHttpRequest();
        xhr.withCredentials = true;

        xhr.addEventListener("readystatechange", function () {
            if (this.readyState === this.DONE) {
                console.log(this.responseText);

                // 5.5 Metemos el order en settings
                let obj = this.responseText
                settings.order = obj.id
                console.log(`settings.order: `+settings.order)

                // 6. Solo ahora las cantidades enviadas son las de referencia
                //if (html) 
                marcar_cantidades_originales();

                // 7. Abrir la pasarela de Culqi
                Culqi.open();
            }else{
                console.log("No ha podido crear la orden")
            }
        });

        xhr.open("POST", "<?= base_url() ?>carrito/crear_orden");


        xhr.send(data);
        
    }

    const handleCulqiAction = () => {
        if (Culqi.token) {
            const token = Culqi.token.id;
            console.log("Se ha creado un Token: ", token);

            // Ruta generada por PHP (base_url)
            const url = '<?= base_url("carrito/recibe_token") ?>';

            // Enviar el token + los datos de envío del formulario
            const form = document.getElementById('form-checkout');
            if (!form) {
                alert('No se encontró el formulario de datos de envío.');
                btn_procesando(false);
                return;
            }

            const datosPago = new FormData(form);
            datosPago.append('token', token);

            fetch(url, {
                method: 'POST',
                body: datosPago
            })
            .then(response => response.json())
            .then(res => {
                console.log(res);
                if (res.ok) {
                    
                    // EL PAGO ESTA PROCESADO CORRECTAMENTE
                    

                    window.location.href = res.redirect;
                } else {
                    alert('No se pudo procesar el pago: ' + res.error);
                    btn_procesando(false);
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                alert('Ocurrió un error al procesar el pago. Intente nuevamente.');
                btn_procesando(false);
            });

        } else if (Culqi.order) {
            const order = Culqi.order;
            console.log("Se ha creado el objeto Order: ", order);
            alert('El pago con Yape/billetera todavía no está habilitado. Use una tarjeta.');
            btn_procesando(false);
        } else {
            console.log("Errorrr : ", Culqi.error);
            const mensaje = (Culqi.error && Culqi.error.user_message) ? Culqi.error.user_message : 'No se pudo iniciar el pago.';
            alert(mensaje);
            btn_procesando(false);
        }
    };

    const config = {
        settings,
        client,
        options,
        appearance
    };

    const Culqi = new CulqiCheckout(publicKey, config);

    Culqi.culqi = handleCulqiAction; // ejecuta la funcion

    let btn = document.getElementById("btn_pagar");

    if (btn) {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            console.log("Iniciando la carga de la Pasarela");

            // 1. Mostrar los datos de envío y validarlos antes de cobrar
            mostrar_datos_envio();
            if (!validarDatosEnvio()) {
                return;
            }

            // 2. Abrir la pasarela de Culqi
            btn_procesando(true);

            /* 3. Si el cliente cambió las cantidades en la vista, se guardan primero en la
                  sesión: así el total mostrado, el de la sesión y el que cobra Culqi coinciden. */
            sincronizar_carrito()
                .then(function(html){
                    // 4. El resumen y el monto de la pasarela ya reflejan el total real
                    recalcular_resumen();

                    /* 5. Lo mostrado y lo que cobrará Culqi (el total de la sesión, calculado por
                          Carrito::_calcular_total) deben ser iguales: si no coinciden no se abre la
                          pasarela, porque se cobraría un monto distinto al de la pantalla. */
                    const total_servidor = total_servidor_centimos(html);

                    if (total_servidor !== null && total_servidor !== Math.round(total_carrito * 100)) {
                        alert('No se pudieron guardar todas las cantidades. El servidor calculó ' +
                              formato_monto(total_servidor / 100) +
                              '. Se recargará el carrito para mostrar los valores reales.');
                        window.location.reload();
                        return;
                    }

                    // ***********************************************
                    crear_orden(); // nombre, apellido, correo, fono, dni
                    // ***********************************************

                    
                })
                /*.catch(function(){
                    alert('No se pudieron guardar los cambios del carrito. Intente nuevamente.');
                    btn_procesando(false);
                });*/
        });
    }

    // ---- Resumen del carrito en vivo: subtotal = cantidad x precio mostrado ----------
    document.querySelectorAll('input[name^="cantidad"]').forEach(function(input){
        input.addEventListener('input', recalcular_resumen);
        input.addEventListener('change', recalcular_resumen);
    });

    // Al cargar, deja el subtotal de cada línea, el Subtotal y el Total iguales a la vista
    recalcular_resumen();
</script>