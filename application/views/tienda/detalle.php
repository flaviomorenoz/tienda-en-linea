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
                $det_img_principal = base_url('../erp-en-linea/assets/img/productos/' . $imagenes[0]);
                $det_img_default   = base_url('../erp-en-linea/assets/img/productos/default1.jpg');
                $det_img_id        = 'detalle-img-principal';
            ?>
            <div class="product-detail-img-wrapper rounded-4 overflow-hidden shadow-sm">
                <img src="<?php echo $det_img_principal; ?>" style="height:356px!important;"
                     id="<?php echo $det_img_id; ?>"
                     alt="<?php echo htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8'); ?>"
                     class="product-detail-img"
                     onerror="this.onerror=null;this.src='<?php echo $det_img_default; ?>'">
            </div>
            <div class="product-detail-thumbs">
                <span class="texto-descripb"><?= isset($descripcion) ? $descripcion : "" ?></span>
            </div>
            <?php if (count($imagenes) > 1): ?>
            <div class="product-detail-thumbs">
                <?php foreach ($imagenes as $i => $img_nombre): ?>
                <?php $det_img_src="<?= ruta_imagen_producto($img_nombre) ?>" ?>
                <img src="<?php echo $det_img_src; ?>"
                     class="<?php echo $i === 0 ? 'active' : ''; ?>"
                     alt="Foto <?php echo $i + 1; ?> de <?php echo htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8'); ?>"
                     onerror="this.style.display='none'"
                     onclick="swapDetalleImg(this, '<?php echo $det_img_src; ?>')">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
        </div>

        <!-- Información del producto -->
        <div class="col-md-6">
            <span class="badge bg-light text-dark border mb-2"><?php echo htmlspecialchars($producto->categoria, ENT_QUOTES, 'UTF-8'); ?></span>
            <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($producto->nombre, ENT_QUOTES, 'UTF-8'); ?></h1>

            <?php if ($producto->tiene_precio && $producto->precio): ?>
            <div class="precio-grande mb-3">
                <span class="display-6 fw-bold text-dark">
                    <?php echo $this->config->item('moneda_simbolo'); ?>
                    <?php echo number_format($producto->precio, 2); ?>
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
                    <div style="border-style:none; border-color:red;display:inline-block;">
                        <label class="form-label fw-semibold">Unidad</label>
                        <div class="input-group" style="max-width:160px;">
                            <select name="select_unidad" id="select_unidad">
                                <option value="UNIDAD">Unidad</option>
                                <option value="DOCENA">Docena</option>
                                <option value="MEDIA DOCENA">1/2 Docena</option>
                            </select>
                            
                        </div>
                    </div>
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
                        <img src="<?php echo base_url('../erp-en-linea/assets/img/productos/' . $r->imagen_url); ?>"
                             alt="<?php echo htmlspecialchars($r->nombre, ENT_QUOTES, 'UTF-8'); ?>"
                             class="product-img"
                             onerror="this.onerror=null;this.src='<?php echo base_url('assets/img/productos/default1.jpg'); ?>'">
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
</script>
