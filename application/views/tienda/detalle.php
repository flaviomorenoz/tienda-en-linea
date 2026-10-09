<script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
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
        .bloque-elemento{
            border-style:none; border-color:red; display:inline-block; margin: 5px 7px;
        }

        /* La lupa (zoom) ya no se define aqui: sus estilos viven en
           assets/css/lupa.css, compartidos con las tarjetas de tienda/home.php.
           Lo que queda en esta vista es solo el marco y el aviso. */

        /* Previsualizacion de imagenes de la venta (tooltip con miniaturas) */
        #popup_fotos{
            display: none;
            position: fixed;
            z-index: 20000;
            max-width: 420px;
            padding: 6px;
            background: #fff;
            border: 1px solid #999;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        #example a.ver_fotos{
            text-decoration: none;
        }

        /* Comentarios del producto (popup mostrar_popup_comentario) */
        .comentario-item{
            background: #fff; border: 1px solid #eee; border-radius: 10px;
            padding: 12px 14px; margin-bottom: 10px;
        }
        .comentario-item .comentario-texto{ white-space: pre-wrap; margin-bottom: 6px; }
        .comentario-item .comentario-fecha{ font-size: 0.8rem; color: #8a8a8a; }

    </style>

    <!-- Contenedor flotante para las miniaturas de imagenes -->
    <div id="popup_fotos"></div>

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
                    <div class="bloque-elemento">
                        <label class="form-label fw-semibold">Unidad</label>
                        <div class="input-group" style="max-width:160px;">
                            <?php
                                echo form_dropdown('select_unidad', $ar_u, 'DOCENA','class="" id="select_unidad" required="required" onchange="coloca_precio(this)"');
                            ?>
                            <input type="hidden" name="hdn_precio" id="hdn_precio" value="">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="bloque-elemento" style="margin-right:0px;">
                        <label class="form-label fw-semibold">Talla</label>
                        <div class="input-group" style="">
                            <?php
                                $ar         = array();
                                $cSql       = "select a.id, a.id_producto, b.name, a.cod_talla
                                    from tec_product_tallas a
                                    inner join tec_products b on a.id_producto = b.id
                                    where a.id_producto = ?";
                                $result     = $this->db->query($cSql,$producto->id)->result_array();
                                $ar_talla   = $this->fm->conver_dropdown($result, "cod_talla", "cod_talla", array(''=>'Seleccione'));

                                echo form_dropdown('select_talla', $ar_talla, '','class="" id="select_talla" required="required"');
                            ?>
                        </div>
                    </div>

                    <div class="bloque-elemento" style="margin-left:0px;">
                        <button type="button" class="btn btn-danger btn-sm ver_fotos" data-fotos="<?=base_url()?>assets/img/truzas_mujer.jpeg">?</button>
                    </div>
                    <script>
                        
                    </script>

                    <div class="bloque-elemento">
                        <label class="form-label fw-semibold">Cantidad</label>
                        <div class="input-group" style="max-width:260px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="text-center" name="cantidad"
                                id="cantidad" value="1" min="1" max="99">
                            <button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                </div>

                <div class="d-grid gap-2">
                    <table style="width: 100%;">
                        <tr>
                            <th style="width:100%; text-align: center; padding: 10px 5px;">
                                <button type="submit" class="btn btn-dark btn-lg" style="width:250px">
                                    <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
                                </button>
                            </th>
                        </tr>
                        <tr>
                            <td style="width:100%; text-align: center;">
                                <a href="<?php echo base_url('carrito'); ?>" class="btn btn-outline-secondary" style="width:250px">
                                    <i class="bi bi-cart3 me-2"></i>Ver carrito
                                </a>
                            </td>
                        </tr>
                    </table>
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

    <?php
        // Comentarios del producto (tec_comentarios). Los envia Tienda::detalle();
        // se escriben desde el popup "Comenta" (boton del catalogo y de esta ficha).
        $com_lista = isset($comentarios) ? $comentarios : array();
        $com_total = isset($comentarios_total) ? (int) $comentarios_total : count($com_lista);
    ?>
    <!-- Comentarios del producto -->
    <hr class="my-5">
    <div class="row g-4" id="bloque-comentarios">
        <div class="col-lg-8">
            <h4 class="fw-bold mb-3">
                <i class="bi bi-chat-left-text me-2"></i>Comentarios
                <span class="badge bg-light text-dark border align-middle"><?php echo $com_total; ?></span>
                <a href="#" class="small ms-2 text-decoration-none"
                   onclick="mostrar_popup_comentario(<?php echo (int) $producto->id; ?>); return false;">Comenta</a>
            </h4>

            <?php if (empty($com_lista)): ?>
            <p class="text-muted small fst-italic mb-0">
                Todavía no hay comentarios de este producto. ¡Sé la primera en opinar!
            </p>
            <?php else: ?>
                <?php foreach ($com_lista as $com): ?>
                <div class="comentario-item">
                    <div class="comentario-texto"><?php echo nl2br(htmlspecialchars($com->comentario, ENT_QUOTES, 'UTF-8')); ?></div>
                    <div class="comentario-fecha">
                        <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($com->fecha)); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

document.addEventListener('DOMContentLoaded', function () { // Lupa (zoom) de la imagen principal. La implementacion esta en assets/js/lupa.js (compartida con las tarjetas de tienda/home.php): aqui
     
    iniciarLupa({
        img: 'detalle-img-principal',
        wrapper: '.product-detail-img-wrapper'
    });
});

// Previsualizacion de las imagenes de la venta al pasar el mouse por el icono ("?").
// El src real depende del sexo del producto, que se consulta por ajax
// (products/getSexo); por eso el popup recien se muestra dentro del success.
var tFoto      = null;
var sexoProd   = null;   // cache del sexo devuelto por products/getSexo
var hoverFotos = false;

// Guia de tallas (truza) segun el codigo de sexo que devuelve products/getSexo.
// Si el codigo no esta en el mapa se respeta la url de data-fotos.
var TRUZAS = {
    'H': 'assets/img/truzas_hombre.jpeg',
    'M': 'assets/img/truzas_mujer.jpeg',
    'O': 'assets/img/truzas_ninos.jpeg',
    'A': 'assets/img/truzas_ninas.jpeg'
};

// Coloca #popup_fotos al lado del boton señalado. El rectangulo se pide aqui,
// sobre el elemento del DOM: dentro del callback de $.ajax "this" NO es el
// boton (jQuery lo reemplaza por el objeto de opciones de la peticion), y por
// eso reventaba this.getBoundingClientRect().
function colocaPopupFotos($pop, el) {
    var rect = el.getBoundingClientRect();
    var w    = $pop.outerWidth();
    var h    = $pop.outerHeight();

    var left = rect.right + 8;
    if((left + w) > $(window).width()){ left = rect.left - w - 8; }
    if(left < 4){ left = 4; }

    var top = rect.top;
    if((top + h) > $(window).height()){ top = $(window).height() - h - 8; }
    if(top < 4){ top = 4; }

    $pop.css({top: top + 'px', left: left + 'px'});
}

function pintaFotosVenta(el, urls) {
    var cod = (sexoProd || '').toUpperCase().charAt(0);
    var cad = '';

    for(var i=0; i<urls.length; i++){
        if(urls[i].length == 0){ continue; }
        // Rutas absolutas: las relativas ("assets/img/...") se resolvian contra
        // /tienda/producto/<id>/ y la imagen no cargaba.
        var src = TRUZAS[cod] ? '<?php echo base_url(); ?>' + TRUZAS[cod] : urls[i];
        cad += '<img src="' + src + '" style="height:460px;width:auto;margin:3px;border:1px solid #ccc;border-radius:4px;background:#fff;">';
    }

    var $pop = $('#popup_fotos');
    $pop.html(cad).css({top: '-9999px', left: '-9999px'}).show();
    colocaPopupFotos($pop, el);

    // El ancho real solo se conoce cuando cargan las imagenes: se reposiciona.
    $pop.find('img').on('load', function(){ colocaPopupFotos($pop, el); });
}

$(document).on('mouseenter', '.ver_fotos', function(){
    clearTimeout(tFoto);
    hoverFotos = true;

    // El boton señalado se captura ANTES del ajax: dentro del success ya no se
    // puede usar "this".
    var el   = this;
    var urls = ($(this).attr('data-fotos') || '').split('|');

    if(sexoProd !== null){          // ya consultado: sin llamada al servidor
        pintaFotosVenta(el, urls);
        return;
    }

    $.ajax({
        data    : {id_product: '<?php echo (int) $producto->id; ?>'},
        url     : '<?php echo base_url('products/getSexo'); ?>',
        type    : 'get',
        success : function(res){
            sexoProd = String(res === null || res === undefined ? '' : res).trim();
            // Si el mouse ya salio del boton no se muestra el popup.
            if(!hoverFotos){ return; }
            pintaFotosVenta(el, urls);
        }
    });
});

$(document).on('mouseleave', '.ver_fotos', function(){
    hoverFotos = false;
    tFoto = setTimeout(function(){ $('#popup_fotos').hide(); }, 250);
});

$('#popup_fotos').on('mouseenter', function(){ clearTimeout(tFoto); });
$('#popup_fotos').on('mouseleave', function(){ $('#popup_fotos').hide(); });

</script>