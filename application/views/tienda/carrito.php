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

    <script>
        /* Botón "Inicia Pago": envía el formulario a pago/preparar. El total NO viaja en el
           formulario (Pago::preparar lo recalcula con el carrito de la sesión): solo se
           guardan antes las cantidades editadas y sin guardar, para que lo que se muestra
           y lo que se cobrará coincidan. */
        function valido_preparar(){
            if (!hay_cambios_cantidad()) return true;

            guardar_cambios_y_continuar();
            return false;
        }

        /* Guarda el carrito (carrito/actualizar) con el token CSRF vigente y, al terminar,
           continúa con el envío del formulario hacia pago/preparar. */
        function guardar_cambios_y_continuar(){
            const formulario = document.querySelector('form[action$="carrito/actualizar"]');
            if (!formulario) return;

            const datos = new FormData(formulario);
            datos.set(csrf_token_name, token_csrf_actual());

            fetch(formulario.action, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: datos
            })
            .then(function(response){ return response.text(); })
            .then(function(html){
                // carrito/actualizar rota el token CSRF: se refresca antes de enviar
                actualizar_token_csrf(html);
                marcar_cantidades_originales();

                document.getElementById('form_preparar').submit();
            })
            .catch(function(error){
                console.error('No se pudieron guardar las cantidades:', error);
                alert('No se pudieron guardar las cantidades del carrito. Intente nuevamente.');
            });
        }
    </script>
    
    <?= form_open_multipart("pago/preparar", 'class="validation" id="form_preparar" onsubmit="return valido_preparar()"'); ?>
        <table style="width:100%; margin-top:7px;">
            <tr>
                <td style="width:35%">
                </td>
                <td style="width:30%" class="text-center">
                    
                </td>
                <td style="width:35%;text-align:right">
                    <button type="submit" id="btn_pagar0" class="btn btn-primary btn-md" style="padding-top:7px;">
                        <i class="bi bi-arrow-repeat me-1"></i>Inicia Pago
                    </button>
                </td>
            </tr>
        </table>
    <?= form_close(); ?>
    
    <?php endif; ?>

    
</div>
<script>


    // Names del CSRF de CodeIgniter (config.php: csrf_token_name / csrf_cookie_name)
    const csrf_token_name  = '<?php echo $this->security->get_csrf_token_name(); ?>';
    const csrf_cookie_name = '<?php echo $this->config->item('csrf_cookie_name'); ?>';

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

    /* Token CSRF vigente para un POST propio (XHR/fetch). La cookie es la fuente
       autoritativa porque CI compara $_POST[csrf_token] contra ella
       (system/core/Security.php:230): el campo oculto puede quedar viejo cuando
       csrf_regenerate = TRUE rota el token, por eso se pide en cada envío. */
    function token_csrf_actual(){
        const de_cookie = token_desde_cookie();
        if (de_cookie) return de_cookie;

        const campo = document.querySelector('input[name="' + csrf_token_name + '"]');
        return campo ? campo.value : '';
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


    /* Resumen del carrito en vivo: subtotal de cada línea (cantidad x precio) y totales.
       Es solo informativo: el monto que se cobra lo calcula el servidor con el carrito de
       la sesión (ver Carrito::_calcular_total y Pago::crear_orden). */
    function recalcular_resumen(){
        let suma = 0;

        document.querySelectorAll('tr[data-precio]').forEach(function(tr){
            const precio = parseFloat(tr.getAttribute('data-precio')) || 0;
            const input  = tr.querySelector('input[name^="cantidad"]');

            let cantidad = input ? parseInt(input.value, 10) : 1;
            if (isNaN(cantidad) || cantidad < 1) cantidad = 1;
            if (cantidad > 99) cantidad = 99;

            const subtotal = precio * cantidad;
            suma += subtotal;

            const celda = tr.querySelector('.subtotal-linea');
            if (celda) celda.textContent = subtotal.toFixed(2);
        });

        // Las celdas del resumen muestran solo el número (el símbolo va fuera)
        const resumen_subtotal = document.getElementById('resumen-subtotal');
        const resumen_total    = document.getElementById('resumen-total');

        if (resumen_subtotal) resumen_subtotal.textContent = suma.toFixed(2);
        if (resumen_total)    resumen_total.textContent    = suma.toFixed(2);
    }

    // ---- Resumen del carrito en vivo: subtotal = cantidad x precio mostrado ----------
    document.querySelectorAll('input[name^="cantidad"]').forEach(function(input){
        input.addEventListener('input', recalcular_resumen);
        input.addEventListener('change', recalcular_resumen);
    });

    // Al cargar, deja el subtotal de cada línea, el Subtotal y el Total iguales a la vista
    recalcular_resumen();
</script>