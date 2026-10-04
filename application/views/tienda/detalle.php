<?php
    $mi_precio = 0;

    $x_unidad = $x_docena = $x_media_docena = "";
    $hay_modalidad = false;

    foreach($precios as $r){ 
        if($r->id_unidad == '2'){ // docena
            $mi_precio = $r->precio;
            $x_docena = '1';
        }

        if($r->id_unidad == '3'){ // media docena
            //$mi_precio = $r->precio;
            $x_media_docena = '1';
        }

        $hay_modalidad = true;
    }

    // unidades
    $cSql = "select a.id, trim(a.descrip) descrip, a.conversion, b.precio from tec_unidades a inner join tec_precios b on a.id=b.id_unidad where id_producto = ?";
    $result   = $this->db->query($cSql, $producto->id)->result_array();
    $ar_u     = $this->fm->conver_dropdown($result, "descrip", "descrip", array(''=>'Seleccione'));


    echo "<script>\n";
    echo "let ar2 = [];\n"; 
    foreach($result as $r){
        //echo "let mi_descrip = '" . $r["descrip"] . "';\n";
        //echo "let mi_precio = " . $r["precio"] . ";\n";
        echo "ar2['" . $r["descrip"] . "'] = " . $r["precio"] . ";\n";
    }
    echo "</script>\n";
?>
<div class="container">

    <style>
        .product-detail-thumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .product-detail-thumbs img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: border-color 0.2s, opacity 0.2s;
            opacity: 0.7;
        }
        .product-detail-thumbs img.active,
        .product-detail-thumbs img:hover {
            border-color: #343a40;
            opacity: 1;
        }
        .texto-descripb{
            color: rgb(80,80,80);
            font-family: var(--font);
            font-size: 1.4rem!important;
            line-height: 1.8;
            white-space: pre-wrap;
            border: none;
            background: transparent;
            padding: 0;
            margin: 0.25rem 0 0;
        }

        /* --- Efecto LUPA (zoom) sobre la imagen principal --- */
        .product-detail-img-wrapper {
            position: relative;
        }
        .product-detail-img-wrapper img.product-detail-img {
            cursor: zoom-in;
        }
        /* Lente que sigue al cursor. El tamano se define aqui y el JS lo lee. */
        .product-detail-lupa {
            position: absolute;
            top: 0;
            left: 0;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background-color: #111;
            background-repeat: no-repeat;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.12s ease;
            z-index: 6;
            /* El borde va con box-shadow para no desplazar el origen del fondo. */
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.95),
                        0 0 0 3px rgba(0, 0, 0, 0.30),
                        0 10px 26px rgba(0, 0, 0, 0.45);
            will-change: transform, background-position;
        }
        .product-detail-lupa.activa {
            opacity: 1;
        }
        /* Aviso para que el usuario descubra el zoom. */
        .product-detail-lupa-hint {
            position: absolute;
            right: 10px;
            bottom: 10px;
            z-index: 5;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 0.75rem;
            line-height: 1;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .product-detail-lupa-hint i {
            font-size: 0.95rem;
        }
        .product-detail-img-wrapper.lupa-activa .product-detail-lupa-hint {
            opacity: 0;
        }
        /* En pantallas tactiles no hay hover: se oculta el aviso. */
        @media (hover: none), (pointer: coarse) {
            .product-detail-lupa-hint {
                display: none;
            }
            .product-detail-img-wrapper img.product-detail-img {
                cursor: default;
            }
        }
    </style>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Tienda</a></li>
            <li class="breadcrumb-item">
                <a href="<?php echo base_url('tienda/categoria/' . urlencode($producto->categoria)); ?>">
                    <?php echo htmlspecialchars($producto->categoria, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8'); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Imagen del producto -->
        <div class="col-md-6">
            <?php
                // $imagenes trae hasta 6 nombres (tec_products.imagen .. imagen6), ya
                // ordenados y sin las vacias, desde Tienda::_imagenes_de().
                $det_img_principal = ruta_imagen_producto($imagenes[0]); //base_url('../erp-en-linea/assets/img/productos/' . $imagenes[0]);
                $det_img_default   = base_url('../erp-en-linea/assets/img/productos/default1.jpg');
                $det_img_id        = 'detalle-img-principal';
            ?>
            <div class="product-detail-img-wrapper rounded-4 overflow-hidden shadow-sm">
                <img src="<?php echo $det_img_principal; ?>" style="height:356px!important;"
                     id="<?php echo $det_img_id; ?>"
                     alt="<?php echo htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8'); ?>"
                     class="product-detail-img"
                     data-lupa-zoom="2.5"
                     onerror="this.onerror=null;this.src='<?php echo $det_img_default; ?>'">
                <span class="product-detail-lupa-hint"><i class="bi bi-zoom-in"></i> Pasa el cursor para ampliar</span>
            </div>
            <?php if (count($imagenes) > 1): ?>
            <div class="product-detail-thumbs">
                <?php foreach ($imagenes as $i => $img_nombre): ?>
                <?php $det_img_src = ruta_imagen_producto($img_nombre); ?>
                <img src="<?php echo $det_img_src; ?>"
                     class="<?php echo $i === 0 ? 'active' : ''; ?>"
                     alt="Foto <?php echo $i + 1; ?> de <?php echo htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8'); ?>"
                     onerror="this.style.display='none'"
                     onclick="swapDetalleImg(this, '<?php echo $det_img_src; ?>')"
                     data-combre="<?php echo htmlspecialchars($det_img_src, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="product-detail-thumbs">
                <span class="texto-descripb"><?= isset($descripcion) ? $descripcion : "" ?></span>
            </div>
            
        </div>

        <!-- Información del producto -->
        <div class="col-md-6">
            <span class="badge bg-light text-dark border mb-2"><?php echo htmlspecialchars($producto->categoria, ENT_QUOTES, 'UTF-8'); ?></span>
            <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8'); ?></h1>

            <?php if ($producto->tiene_precio && $producto->precio): ?>
            <div class="precio-grande mb-3">
                <span class="display-6 fw-bold text-dark">
                    <?php echo $this->config->item('moneda_simbolo'); ?>
                    <span id="precio"><?php echo number_format($mi_precio, 2); ?></span>
                </span>
            </div>
            <?php endif; ?>

            <!--<p class="text-muted mb-4"><?php echo nl2br(htmlspecialchars($producto->descripcion, ENT_QUOTES, 'UTF-8')); ?></p>-->

            <?php if ($producto->tiene_precio): ?>
            <!-- Formulario agregar al carrito -->
            <form action="<?php echo base_url('carrito/agregar'); ?>" method="POST" id="form-agregar">
                
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                       value="<?php echo $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="id_producto" value="<?php echo $producto->id; ?>">

                <!-- Selector de talla -->
                <?php if (!empty($tallas)): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Talla</label>
                    <div class="talla-selector d-flex flex-wrap gap-2" id="tallas-container">
                        <?php foreach ($tallas as $i => $t): ?>
                        <input type="radio" class="btn-check" name="talla"
                               id="talla-<?php echo $t->talla; ?>"
                               value="<?php echo htmlspecialchars($t->talla, ENT_QUOTES, 'UTF-8'); ?>"
                               <?php echo ($i === 0) ? 'checked' : ''; ?> required>
                        <label class="btn btn-outline-dark btn-talla" for="talla-<?php echo $t->talla; ?>">
                            <?php echo htmlspecialchars($t->talla, ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <input type="hidden" name="talla" value="Única">
                <?php endif; ?>

                <!-- Cantidad -->
                <div class="mb-4">
                    <?php if ($hay_modalidad): ?>
                    <div style="border-style:none; border-color:red;display:inline-block;">
                        <label class="form-label fw-semibold">Unidad</label>
                        <div class="input-group" style="max-width:160px;">
                            <?php
                                echo form_dropdown('select_unidad', $ar_u, 'DOCENA','class="" id="select_unidad" required="required" onchange="coloca_precio(this)"');
                            ?>
                            <input type="hidden" name="hdn_precio" id="hdn_precio" value="">
                        </div>
                    </div>
                    <?php endif; ?>
                    <div style="border-style:none; border-color:red;display:inline-block;">
                        <label class="form-label fw-semibold">Cantidad</label>
                        <div class="input-group" style="max-width:160px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control text-center" name="cantidad"
                                id="cantidad" value="1" min="1" max="99">
                            <button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
                    </button>
                    <a href="<?php echo base_url('carrito'); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-cart3 me-2"></i>Ver carrito
                    </a>
                </div>
            </form>

            <?php else: ?>
            <!-- Producto sin precio: botón WhatsApp -->
            <div class="d-grid gap-2">
                <a href="https://wa.me/<?php echo $this->config->item('whatsapp_numero'); ?>?text=<?php echo urlencode($this->config->item('whatsapp_mensaje') . $producto->nombre); ?>"
                   class="btn btn-consultar btn-lg" target="_blank">
                    <i class="bi bi-whatsapp me-2"></i>Consultar precio por WhatsApp
                </a>
                <p class="text-muted small text-center mt-1">
                    Enviaremos tus precios y disponibilidad a la brevedad.
                </p>
            </div>
            <?php endif; ?>

            <!-- Tallas disponibles (solo información) -->
            <?php if (!$producto->tiene_precio && !empty($tallas)): ?>
            <div class="mt-3">
                <small class="text-muted fw-semibold">Tallas disponibles: </small>
                <?php foreach ($tallas as $t): ?>
                <span class="badge bg-light text-dark border me-1"><?php echo htmlspecialchars($t->talla, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </div>

    <div class="row g-4">
        <div class="col_md-6">
            
        </div>
    </div>

    <!-- Productos relacionados -->
    <?php if (!empty($relacionados)): ?>
    <hr class="my-5">
    <h4 class="fw-bold mb-4">Productos relacionados</h4>
    <div class="row row-cols-2 row-cols-md-4 g-3">
        <?php foreach ($relacionados as $r): ?>
        <div class="col">
            <div class="card product-card h-100 border-0 shadow-sm">
                <a href="<?php echo base_url('tienda/producto/' . $r->id); ?>">
                    <div class="product-img-wrapper">
                        <?php
                            $rutax = ruta_imagen_producto($r->imagen_url);
                        ?>
                        <img src="<?= $rutax ?>"
                             alt="<?php echo htmlspecialchars($r->nombre, ENT_QUOTES, 'UTF-8'); ?>"
                             class="product-img"
                             onerror="this.onerror=null;this.src='<?php echo base_url('assets/img/productos/default1.jpg'); ?>'"
                             data-combre="<?php echo htmlspecialchars($rutax, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </a>
                <div class="card-body p-3">
                    <h6 class="card-title fw-semibold small mb-1">
                        <?php echo htmlspecialchars($r->nombre, ENT_QUOTES, 'UTF-8'); ?>
                    </h6>
                    <?php if ($r->tiene_precio && $r->precio): ?>
                    <p class="precio fw-bold mb-0">
                        <?php echo $this->config->item('moneda_simbolo'); ?> <?php echo number_format($r->precio, 2); ?>
                    </p>
                    <?php else: ?>
                    <span class="badge bg-secondary small">Consultar</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function swapDetalleImg(thumb, src) {
    document.getElementById('detalle-img-principal').src = src;
    thumb.closest('.product-detail-thumbs').querySelectorAll('img').forEach(function(t) {
        t.classList.remove('active');
    });
    thumb.classList.add('active');
}

function cambiarCantidad(delta) {
    const input = document.getElementById('cantidad');
    const val = parseInt(input.value) + delta;
    if (val >= 1 && val <= 99) input.value = val;

    // colocando el valor real
    /*
    const la_unidad = document.getElementById("select_unidad").value
    let valor_real = 1
    let paquete = 1
    if(la_unidad == "DOCENA"){ paquete = 12; }
    if(la_unidad == "MEDIA DOCENA"){ paquete = 6; }
    valor_real = val * paquete
    document.getElementById("cantidad").value = valor_real
    */
}

function coloca_precio(obj){
    document.getElementById("precio").innerHTML = ar2[obj.value]
    document.getElementById("hdn_precio").value = ar2[obj.value]
    //alert("Sois bendecido")
}

function dispara_change(){
    let event = new Event('change');  // Create a new 'change' event
    let obj = document.getElementById("select_unidad")
    obj.dispatchEvent(event); // Dispatch it.
}

setTimeout(dispara_change,600);

</script>

<!-- ============================================================
     LUPA (zoom) de la imagen principal del detalle de producto.
     - Sigue al cursor y amplia la zona apuntada.
     - El aumento se configura con data-lupa-zoom en la etiqueta <img>
       y el tamano de la lente en el CSS (.product-detail-lupa).
     - Solo se activa con mouse / lapiz: en pantallas tactiles no interfiere.
     ============================================================ -->
<script>
(function () {
    var img = document.getElementById('detalle-img-principal');
    if (!img || !img.closest) { return; }

    var wrapper = img.closest('.product-detail-img-wrapper');
    if (!wrapper) { return; }

    var ZOOM = parseFloat(img.getAttribute('data-lupa-zoom')) || 2.5;

    var lupa = document.createElement('div');
    lupa.className = 'product-detail-lupa';
    lupa.setAttribute('aria-hidden', 'true');
    wrapper.appendChild(lupa);

    // Tamano definido en el CSS (se lee antes de cualquier ajuste en linea).
    var diametroCSS = lupa.offsetWidth || 190;

    // El fondo de la lente debe fundirse con el del marco, que cambia segun el tema.
    var fondoMarco = window.getComputedStyle(wrapper).backgroundColor;
    if (fondoMarco && fondoMarco !== 'transparent' && fondoMarco !== 'rgba(0, 0, 0, 0)') {
        lupa.style.backgroundColor = fondoMarco;
    }

    var srcActual = '';
    var dAplicado = -1;
    var ultimoEvento = null;
    var raf = null;
    var activa = false;

    /* Geometria real de la imagen dibujada dentro del marco: contempla
       object-fit (cover / contain / fill / none) y el recorte que produce. */
    function geometria() {
        var w = wrapper.getBoundingClientRect();
        var r = img.getBoundingClientRect();
        var nw = img.naturalWidth;
        var nh = img.naturalHeight;
        if (!nw || !nh || r.width <= 0 || r.height <= 0 || w.width <= 0) { return null; }

        var fit = (window.getComputedStyle(img).objectFit || 'fill').toLowerCase();
        var escX, escY;
        if (fit === 'none') {
            escX = 1;
            escY = 1;
        } else if (fit === 'fill') {
            escX = r.width / nw;
            escY = r.height / nh;
        } else if (fit === 'contain') {
            escX = escY = Math.min(r.width / nw, r.height / nh);
        } else if (fit === 'scale-down') {
            escX = escY = Math.min(1, Math.min(r.width / nw, r.height / nh));
        } else { // cover (por defecto en los temas)
            escX = escY = Math.max(r.width / nw, r.height / nh);
        }

        var anchoDib = nw * escX;
        var altoDib  = nh * escY;

        return {
            caja: w,
            anchoCaja: r.width,
            altoCaja: r.height,
            anchoDib: anchoDib,
            altoDib: altoDib,
            xImg: r.left - w.left,                                 // esquina de la caja <img>
            yImg: r.top - w.top,
            x0: (r.left - w.left) + (r.width - anchoDib) / 2,      // esquina de la imagen dibujada
            y0: (r.top - w.top) + (r.height - altoDib) / 2
        };
    }

    function sincronizarFondo() {
        var s = img.currentSrc || img.src;
        if (s && s !== srcActual) {
            srcActual = s;
            lupa.style.backgroundImage = 'url("' + s.replace(/"/g, '%22') + '")';
        }
    }

    function pintar() {
        raf = null;
        if (!activa || !ultimoEvento) { return; }

        var g = geometria();
        if (!g) { ocultar(); return; }

        // La lente nunca es mas grande que la imagen.
        var d = Math.min(diametroCSS, g.anchoCaja, g.altoCaja);
        if (d !== dAplicado) {
            lupa.style.width  = d + 'px';
            lupa.style.height = d + 'px';
            dAplicado = d;
        }

        var mx = ultimoEvento.clientX - g.caja.left;
        var my = ultimoEvento.clientY - g.caja.top;

        var ix = mx - g.xImg;   // cursor dentro de la caja <img>
        var iy = my - g.yImg;
        if (ix < 0 || iy < 0 || ix > g.anchoCaja || iy > g.altoCaja) { ocultar(); return; }

        // La lente se mantiene dentro de la imagen (centrada en el cursor cuando cabe).
        var lx = g.xImg + Math.min(Math.max(ix - d / 2, 0), Math.max(0, g.anchoCaja - d));
        var ly = g.yImg + Math.min(Math.max(iy - d / 2, 0), Math.max(0, g.altoCaja - d));

        sincronizarFondo();

        // El punto apuntado por el cursor se ve ampliado en esa misma posicion
        // dentro de la lente, asi que la lupa se siente "pegada" a la imagen.
        var bx = (mx - lx) - (mx - g.x0) * ZOOM;
        var by = (my - ly) - (my - g.y0) * ZOOM;

        lupa.style.transform          = 'translate(' + lx + 'px,' + ly + 'px)';
        lupa.style.backgroundSize     = (g.anchoDib * ZOOM) + 'px ' + (g.altoDib * ZOOM) + 'px';
        lupa.style.backgroundPosition = bx + 'px ' + by + 'px';
    }

    function mostrar() {
        if (activa) { return; }
        activa = true;
        sincronizarFondo();
        lupa.classList.add('activa');
        wrapper.classList.add('lupa-activa');
    }

    function ocultar() {
        if (raf) { cancelAnimationFrame(raf); raf = null; }
        ultimoEvento = null;
        if (!activa) { return; }
        activa = false;
        lupa.classList.remove('activa');
        wrapper.classList.remove('lupa-activa');
    }

    // Pointer events permiten ignorar el dedo y responder solo al mouse / lapiz
    // (util en laptops con pantalla tactil, donde el hover igual existe).
    var usaPointer = 'onpointerdown' in window;
    var evEntra = usaPointer ? 'pointerenter' : 'mouseenter';
    var evMueve = usaPointer ? 'pointermove'  : 'mousemove';
    var evSale  = usaPointer ? 'pointerleave' : 'mouseleave';

    function esMouse(e) {
        if (!usaPointer) { return true; }
        return e.pointerType === 'mouse' || e.pointerType === 'pen' || e.pointerType === '';
    }

    wrapper.addEventListener(evEntra, function (e) {
        if (!esMouse(e)) { return; }
        mostrar();
        ultimoEvento = e;
        if (!raf) { raf = requestAnimationFrame(pintar); }
    });

    wrapper.addEventListener(evMueve, function (e) {
        if (!esMouse(e)) { return; }
        ultimoEvento = e;
        mostrar();
        if (!raf) { raf = requestAnimationFrame(pintar); }
    });

    wrapper.addEventListener(evSale, function (e) {
        if (!esMouse(e)) { return; }
        ocultar();
    });

    // Al cambiar de imagen (miniaturas, o respaldo por onerror) se recalcula todo.
    img.addEventListener('load', function () {
        srcActual = '';
        dAplicado = -1;
        sincronizarFondo();
        if (activa && ultimoEvento && !raf) { raf = requestAnimationFrame(pintar); }
    });

    window.addEventListener('resize', function () {
        dAplicado = -1;
        ocultar();
    });

    // Si se hace scroll con el cursor sobre la imagen, la lente sigue alineada.
    window.addEventListener('scroll', function () {
        if (activa && ultimoEvento && !raf) { raf = requestAnimationFrame(pintar); }
    }, true);
})();
</script>
