<?php
/**
 * Paso 1 del pago: datos de envío + apertura de la pasarela de Culqi (Yape).
 *
 * Flujo completo (ver application/controllers/Pago.php):
 *   PAGAR -> POST pago/crear_orden    (crea la ORDEN en Culqi; sin order el checkout
 *                                      no muestra Yape)                  -> settings.order
 *         -> Culqi.open()             (celular + código de aprobación de Yape)
 *         -> POST carrito/recibe_token (crea el CARGO /v2/charges)
 *         -> redirect pago/procesar   (registra el pedido pagado)
 *
 * Variables que envía Pago::preparar(): $carrito y $total (recalculado en el servidor
 * con el carrito de la sesión, nunca con lo que envíe el navegador).
 */
$carrito = isset($carrito) ? $carrito : array();
$total   = isset($total) ? (float)$total : 0;

$correo_tienda = trim((string)$this->config->item('tienda_email'));
if (!filter_var($correo_tienda, FILTER_VALIDATE_EMAIL)) {
    $correo_tienda = 'flaviomorenoz@gmail.com';
}
?>
<div id="form-pagos" class="row">
    <div class="col-sm-12">
        <?= form_open_multipart("pago/crear_orden", 'name="form-checkout" id="form-checkout"'); ?>
            <div class="row g-4">
                <!-- Columna izquierda: datos de envío -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-semibold border-0 pt-3">
                            <i class="bi bi-person-circle me-2 text-dark"></i>Datos de envío
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label" for="dni">DNI <span class="text-danger">*</span></label>
                                    <input type="text" name="dni" id="dni" class="form-control"
                                        inputmode="numeric" maxlength="8" placeholder="12345678"
                                        value="<?php echo set_value('dni'); ?>" required>
                                    <div class="invalid-feedback">El DNI debe tener exactamente 8 dígitos numéricos.</div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="nombres">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" name="nombres" id="nombres" class="form-control"
                                        placeholder="Juan" maxlength="100"
                                        value="<?php echo set_value('nombres'); ?>" required>
                                    <div class="invalid-feedback">Los nombres son requeridos.</div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" name="apellidos" id="apellidos" class="form-control"
                                        placeholder="Pérez García" maxlength="100"
                                        value="<?php echo set_value('apellidos'); ?>" required>
                                    <div class="invalid-feedback">Los apellidos son requeridos.</div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="correo">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="correo" id="correo" class="form-control"
                                        placeholder="micorreo@dominio.com" maxlength="80"
                                        value="<?php echo set_value('correo'); ?>" required>
                                    <div class="invalid-feedback">Ingrese un correo electrónico válido.</div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="celular">Celular <span class="text-danger">*</span></label>
                                    <input type="tel" name="celular" id="celular" class="form-control"
                                        inputmode="numeric" maxlength="15" placeholder="987654321"
                                        value="<?php echo set_value('celular'); ?>" required>
                                    <div class="invalid-feedback">El celular es requerido (solo números).</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="direccion_envio">Dirección de envío <span class="text-danger">*</span></label>
                                    <input type="text" name="direccion_envio" id="direccion_envio" class="form-control"
                                        placeholder="Av. Ejemplo 123, Dpto. 201 - Referencia"
                                        value="<?php echo set_value('direccion_envio'); ?>" required>
                                    <div class="invalid-feedback">La dirección de envío es requerida.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="observaciones">Referencia / Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2"
                                            placeholder="Cerca al parque, piso 2, etc."><?php echo set_value('observaciones'); ?></textarea>
                                </div>
                            </div>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="bi bi-shield-lock me-1"></i>
                                Al pulsar <strong>PAGAR</strong> se abrirá la pasarela segura de Culqi
                                para pagar con <strong>Yape</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: resumen del pedido (total del servidor) -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-dark text-white fw-semibold">
                            <i class="bi bi-receipt me-2"></i>Resumen del pedido
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-3">
                                <?php foreach ($carrito as $item): ?>
                                <li class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-muted small">
                                        <?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        <br>Talla <?php echo htmlspecialchars($item['talla'], ENT_QUOTES, 'UTF-8'); ?>
                                        x <?php echo (int)$item['cantidad']; ?>
                                    </span>
                                    <span class="small text-nowrap">
                                        <?php echo $this->config->item('moneda_simbolo'); ?>
                                        <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Envío</span>
                                <span class="text-success">A coordinar</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span><?php echo $this->config->item('moneda_simbolo'); ?> <?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <button type="button" id="btn_pagar" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-repeat me-1"></i>PAGAR
                        </button>
                        <br>
                        <a href="<?php echo base_url('carrito'); ?>" class="btn btn-link btn-sm mt-2">
                            <i class="bi bi-arrow-left me-1"></i>Volver al carrito
                        </a>
                    </div>
                </div>
            </div>
        <?= form_close(); ?>
    </div>
</div>
<script src="https://js.culqi.com/checkout-js"></script>
<script>
(function(){
    // --------------------------------------------------------------------
    // CSRF: CodeIgniter valida $_POST[csrf_token] contra la cookie
    // (system/core/Security.php) y, con csrf_regenerate = TRUE, cada POST
    // validado rota el token: por eso el token se lee de la cookie antes
    // de cada envío con fetch.
    // --------------------------------------------------------------------
    const csrf_token_name  = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const csrf_cookie_name = '<?php echo $this->config->item('csrf_cookie_name'); ?>';

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

    function token_csrf_actual(){
        const de_cookie = token_desde_cookie();
        if (de_cookie) return de_cookie;

        const campo = document.querySelector('#form-checkout input[name="' + csrf_token_name + '"]');
        return campo ? campo.value : '';
    }

    // Refresca el campo oculto del formulario con el token vigente de la cookie
    function refrescar_token_csrf(){
        const nuevo = token_desde_cookie();
        if (!nuevo) return;

        document.querySelectorAll('#form-checkout input[name="' + csrf_token_name + '"]').forEach(function(input){
            input.value = nuevo;
        });
    }

    // Datos del formulario con el token CSRF vigente (nunca el campo viejo)
    function datos_formulario(){
        const datos = new FormData(document.getElementById('form-checkout'));
        datos.set(csrf_token_name, token_csrf_actual());
        return datos;
    }
    // --------------------------------------------------------------------
    // Validación de los datos de envío (las mismas reglas del servidor)
    // --------------------------------------------------------------------
    function validarDatosEnvio(){
        let valido = true;

        const campos = [
            { el: document.getElementById('dni'),
              test: function(v){ return /^\d{8}$/.test(v); },
              msg: 'El DNI debe tener exactamente 8 dígitos numéricos.' },
            { el: document.getElementById('nombres'),
              test: function(v){ return v.length >= 2; },
              msg: 'Los nombres son requeridos.' },
            { el: document.getElementById('apellidos'),
              test: function(v){ return v.length >= 2; },
              msg: 'Los apellidos son requeridos.' },
            { el: document.getElementById('correo'),
              test: function(v){ return /^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/.test(v); },
              msg: 'Ingrese un correo electrónico válido.' },
            { el: document.getElementById('celular'),
              test: function(v){ return /^\d{6,15}$/.test(v); },
              msg: 'El celular es requerido (solo números).' },
            { el: document.getElementById('direccion_envio'),
              test: function(v){ return v.length >= 5; },
              msg: 'La dirección de envío es requerida.' }
        ];

        campos.forEach(function(campo){
            const val      = campo.el.value.trim();
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
            const primero = document.querySelector('#form-checkout .is-invalid');
            if (primero) primero.focus();
        }

        return valido;
    }

    ['dni', 'nombres', 'apellidos', 'correo', 'celular', 'direccion_envio'].forEach(function(id){
        const el = document.getElementById(id);
        if (!el) return;

        el.addEventListener('input', function(){
            el.classList.remove('is-invalid', 'is-valid');
        });
    });

    // Bloquea el botón PAGAR mientras se procesa el cobro
    function btn_procesando(activo){
        const boton = document.getElementById('btn_pagar');
        if (!boton) return;

        if (activo) {
            boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';
            boton.style.pointerEvents = 'none';
            boton.style.opacity = '0.7';
        } else {
            boton.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>PAGAR';
            boton.style.pointerEvents = '';
            boton.style.opacity = '';
        }
    }
    // --------------------------------------------------------------------
    // Culqi Checkout v4 (js.culqi.com/checkout-js), solo Yape
    // --------------------------------------------------------------------
    const settings = {
        title: '<?php echo $this->config->item('tienda_nombre'); ?>',
        currency: "PEN",
        amount: <?php echo (int)round($total * 100); ?>,   // céntimos; lo confirma pago/crear_orden
        order: "",                                         // lo asigna pago/crear_orden
        xculqirsaid: "<?= getenv('CULQI_LLAVE_RSA_ID') ?>",
        rsapublickey: "<?= getenv('CULQI_LLAVE_RSA') ?>"
    };

    const publicKey = '<?= getenv('CULQI_LLAVE_PUBLICA') ?>';

    // Solo Yape: el resto de medios queda deshabilitado en el checkout
    const paymentMethods = {
        tarjeta: false,
        yape: true,
        billetera: false,
        bancaMovil: false,
        agente: false,
        cuotealo: false
    };

    const options = {
        lang: "es",
        installments: false,          // Yape es un pago único
        modal: true,
        paymentMethods: paymentMethods,
        paymentMethodsSort: ['yape']
    };

    const client = {
        email: '<?php echo $correo_tienda; ?>'
    };

    const appearance = {
        theme: "default",
        hiddenCulqiLogo: false,
        hiddenBanner: false,
        menuType: "sidebar"
    };

    const config = { settings, client, options, appearance };

    const Culqi = new CulqiCheckout(publicKey, config);
    /* Respuesta JSON del controlador; si algo devuelve HTML (p. ej. un error de
       CSRF de CodeIgniter) se avisa con un mensaje legible. */
    function respuesta_json(response){
        return response.text().then(function(texto){
            const limpio = (texto || '').trim();

            if (limpio.charAt(0) !== '{') {
                if (response.status === 403) {
                    // CodeIgniter deniega el POST cuando el token CSRF caducó
                    throw new Error('Su sesión expiró. Recargue la página e intente nuevamente.');
                }

                throw new Error('El servidor no devolvió JSON (HTTP ' + response.status + ').');
            }

            return JSON.parse(limpio);
        });
    }

    /* El checkout lee su configuración cada vez que se abre (open() -> getUrlParamaters()
       -> amount/order), por eso los valores que devuelve pago/crear_orden se escriben en
       la configuración viva antes de Culqi.open(). */
    function fijar_config_culqi(valores){
        if (typeof valores.amount === 'number') settings.amount = valores.amount;
        if (valores.order)                      settings.order  = valores.order;
        if (valores.email)                      client.email    = valores.email;

        const configuracion = Culqi.culqiConfig;
        if (!configuracion) return;

        try {
            if (typeof configuracion.setConfig === 'function') {
                configuracion.setConfig({
                    settings: { amount: settings.amount, order: settings.order },
                    client:   { email: client.email }
                });
            } else {
                configuracion.settings = Object.assign({}, configuracion.settings, {
                    amount: settings.amount,
                    order:  settings.order
                });
                configuracion.client = Object.assign({}, configuracion.client, {
                    email: client.email
                });
            }
        } catch (e) {
            console.warn('No se pudo fijar la configuración de Culqi:', e);
        }
    }

    // Callback que Culqi ejecuta al confirmar el pago en el checkout
    const handleCulqiAction = function(){
        /* Yape confirma con el token del pago (tkn_/ype_); otros medios resuelven con el
           objeto Order. Se acepta cualquiera de los dos: el servidor los vuelve a validar. */
        const objeto = (Culqi.token && Culqi.token.id) ? Culqi.token : (Culqi.order || null);

        if (!objeto || !objeto.id) {
            const mensaje = (Culqi.error && (Culqi.error.user_message || Culqi.error.merchant_message))
                          ? (Culqi.error.user_message || Culqi.error.merchant_message)
                          : '';

            if (mensaje) {
                alert(mensaje);
            } else {
                console.log('Checkout cerrado sin token:', Culqi.error || Culqi.token);
            }

            btn_procesando(false);
            return;
        }

        console.log('Culqi devolvió ' + objeto.object + ': ' + objeto.id);

        // El CARGO se crea en el servidor con los datos de envío + el token
        const datos = datos_formulario();
        datos.append('token', objeto.id);

        fetch('<?= base_url('carrito/recibe_token') ?>', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: datos
        })
        .then(respuesta_json)
        .then(function(res){
            if (!res.ok) throw new Error(res.error || 'No se pudo procesar el pago.');

            // recibe_token rota el token CSRF: se refresca el campo antes de continuar
            refrescar_token_csrf();
            Culqi.close();

            // Cargo aprobado: pago/procesar registra el pedido y muestra el agradecimiento
            window.location.href = res.redirect;
        })
        .catch(function(error){
            console.error('No se pudo cobrar:', error);
            alert(error.message || 'Ocurrió un error al procesar el pago. Intente nuevamente.');
            btn_procesando(false);
        });
    };

    Culqi.culqi = handleCulqiAction;
    // --------------------------------------------------------------------
    // Botón PAGAR: 1) crea la orden en Culqi  2) abre el checkout
    // --------------------------------------------------------------------
    const btn_pagar = document.getElementById('btn_pagar');

    if (btn_pagar) {
        btn_pagar.addEventListener('click', function(e){
            e.preventDefault();

            if (!validarDatosEnvio()) return;

            btn_procesando(true);

            // El checkout muestra el correo del cliente
            const campo_correo = document.getElementById('correo');
            if (campo_correo && campo_correo.value.trim() !== '') {
                client.email = campo_correo.value.trim();
            }

            /* La ORDEN es obligatoria para que el checkout muestre Yape. El monto lo calcula
               el servidor con el carrito de la sesión: nunca se confía en el navegador. */
            fetch('<?= base_url('pago/crear_orden') ?>', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: datos_formulario()
            })
            .then(respuesta_json)
            .then(function(res){
                if (!res.ok) throw new Error(res.error || 'No se pudo iniciar el pago.');

                // crear_orden rota el token CSRF: se refresca el campo oculto
                refrescar_token_csrf();

                // El checkout cobrará el monto y la orden confirmados por el servidor
                fijar_config_culqi({
                    amount: res.amount,
                    order:  res.order,
                    email:  client.email
                });

                console.log('Orden Culqi creada: ' + res.order + ' (' + res.order_number + ')');

                // Abrir la pasarela: celular + código de aprobación de Yape
                Culqi.open();
            })
            .catch(function(error){
                console.error('No se pudo crear la orden:', error);
                alert(error.message || 'No se pudo iniciar el pago. Intente nuevamente.');
                btn_procesando(false);
            });
        });
    }
})();
</script>
