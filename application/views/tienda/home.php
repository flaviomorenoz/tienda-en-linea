<style>
    .muesca{
        color:rgb(100,150,255);
        text-align:right!important;
    }
</style>
<div class="container" style="padding-top:0px!important;">

    <!-- Filtros de categoría -->
    <div class="d-flex flex-wrap gap-2 mb-4" id="productos">
        <a href="<?php echo base_url(); ?>"
           class="btn <?php echo (!isset($categoria_activa) || !$categoria_activa) ? 'btn-dark' : 'btn-outline-secondary'; ?> btn-sm rounded-pill">
            <i class="bi bi-grid me-1"></i>Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <a href="<?php echo base_url('tienda/categoria/' . urlencode($cat)); ?>"
           class="btn <?php echo (isset($categoria_activa) && $categoria_activa == $cat) ? 'btn-dark' : 'btn-outline-secondary'; ?> btn-sm rounded-pill">
            <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($productos)): ?>
    <!-- Mensaje de vacío -->
    <div class="text-center py-5">
        <i class="bi bi-box-seam text-muted" style="font-size:4rem;"></i>
        <p class="text-muted mt-3">No hay productos disponibles en esta categoría.</p>
        <a href="<?php echo base_url(); ?>" class="btn btn-dark">Ver todos los productos</a>
    </div>
    <?php else: ?>

        <?php
        /**
         * Agrupa los productos por sección (web_secciones).
         * - En el home (index) se reciben $secciones ordenadas por "orden".
         * - En la vista de categoría no se reciben $secciones, se muestran
         *   todos los productos en un solo bloque sin título.
         */
        $hay_secciones = isset($secciones) && !empty($secciones);
        $busqueda = trim(isset($termino_busqueda) ? $termino_busqueda : '');

            // Sin secciones (vista de categoría): todos los productos en un solo bloque sin título.
            if (!$hay_secciones) {
                $secciones_render = array((object)array(
                    'id'              => null,
                    'descrip_seccion' => '',
                    'orden'           => 0,
                ));
            } else {
                $secciones_render = $secciones;
            }
            // Cuando hay búsqueda activa se muestran TODOS los resultados en un
            // solo bloque con encabezado propio, sin agrupar por sección.
            if ($busqueda !== '') {
                $secciones_render = array((object)array(
                    'id'              => -1,
                    'descrip_seccion' => 'Resultados para "' . $busqueda . '"',
                    'orden'           => 0,
                ));
            }

            foreach ($secciones_render as $sec):
                if (!$hay_secciones || $busqueda !== '') {
                    $prod_sec = $productos;
                } else {
                    $prod_sec = array_values(array_filter($productos, function($p) use ($sec) {
                        return isset($p->id_seccion) && (int)$p->id_seccion === (int)$sec->id;
                    }));
                }

                if (empty($prod_sec)) continue;
        ?>
        <!-- Encabezado de sección (estilo Aruma) -->
        <?php if (trim($sec->descrip_seccion) !== ''): ?>
        <div class="aruma-section-header mt-2">
            <h2 class="aruma-section-title"><?php echo htmlspecialchars($sec->descrip_seccion, ENT_QUOTES, 'UTF-8'); ?></h2>
            <a href="<?php echo base_url(); ?>" class="aruma-section-link">Ver todos</a>
        </div>
        <?php endif; ?>

        <!-- Grid de productos de la sección -->
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3 mb-5">
            <?php foreach ($prod_sec as $p): ?>
            <div class="col">
                <div class="card product-card h-100 border-0 shadow-sm">
                    <!-- Imagen principal -->
                    <?php
                        $trz_img2 = isset($p->imagen2) ? $p->imagen2 : '';
                        $trz_img3 = isset($p->imagen3) ? $p->imagen3 : '';
                        //traza("home.php producto id=" . $p->id . " nombre='" . $p->nombre . "' imagen_url='" . $p->imagen_url . "' imagen2='" . $trz_img2 . "' imagen3='" . $trz_img3 . "' img(0)='" . $p->imagenes[0] . "'");
                        $img_principal = ruta_imagen_producto($p->imagenes[0]);
                        $img_default   = base_url('assets/img/default.png');
                        $img_id        = 'prod-img-' . $p->id;
                        //traza("home.php producto id={$p->id} img_principal='$img_principal'");
                    ?>
                    <a href="<?php echo base_url('tienda/producto/' . $p->id); ?>" class="text-decoration-none">
                        <div class="product-img-wrapper">
                            <img src="<?php echo $img_principal; ?>"
                                 id="<?php echo $img_id; ?>"
                                 alt="<?php echo htmlspecialchars($p->nombre, ENT_QUOTES, 'UTF-8'); ?>"
                                 class="product-img"
                                 onerror="this.onerror=null;this.src='<?php echo $img_default; ?>'"
                                 data-combre="<?php echo htmlspecialchars($img_principal, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php if (!$p->tiene_precio): ?>
                            <span class="badge bg-secondary product-badge">Consultar</span>
                            <?php endif; ?>
                        </div>
                    </a>

                    <!-- Miniaturas (solo si hay más de 1 imagen) -->
                    <?php if (count($p->imagenes) > 1): ?>
                    <div class="product-thumbs">
                        <?php foreach ($p->imagenes as $i => $img_nombre): ?>
                        <?php $img_src = ruta_imagen_producto($img_nombre); ?>
                        <img src="<?php echo $img_src; ?>"
                             class="<?php echo $i === 0 ? 'active' : ''; ?>"
                             alt="Foto <?php echo $i + 1; ?>"
                             onerror="this.style.display='none'"
                             onmouseenter="swapProdImg('<?php echo $img_id; ?>', this, '<?php echo $img_src; ?>')">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column p-3">
                        <!-- ******* Categoría ********* -->
                        <span class="text-muted small mb-1"><?php echo htmlspecialchars($p->categoria, ENT_QUOTES, 'UTF-8'); ?></span>

                        <!-- ******* Nombre ******* -->
                        <h6 class="card-title fw-semibold mb-2">
                            <a href="<?php echo base_url('tienda/producto/' . $p->id); ?>" class="text-dark text-decoration-none product-name-link">
                                <?php echo htmlspecialchars($p->nombre, ENT_QUOTES, 'UTF-8'); ?>
                            </a><br>
                            <div id="div-muesca" class="muesca"><a href="#" onclick="quitar_ocultacion(this); return false;">Ver m&aacute;s...</a></div>
                            <span id="div-descrip_<?php echo $p->id; ?>" class="texto-descrip div-ocultar"><pre><?php echo $p->descripcion; ?></pre></span>
                        </h6>

                        <div class="mt-auto">
                            <?php if ($p->tiene_precio && $p->precio): ?>
                            <!-- ******* Precio y botón compra ******* -->
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="precio fw-bold fs-5">
                                    <?php echo $this->config->item('moneda_simbolo'); ?>
                                    <?php echo number_format($p->precio, 2); ?>
                                </span>
                            </div>
                            <a href="<?php echo base_url('tienda/producto/' . $p->id); ?>"
                               class="btn btn-dark btn-sm w-100 mt-2">
                                <i class="bi bi-cart-plus me-1"></i>Ver y agregar
                            </a>
                            <?php else: ?>
                            <!-- Botón consultar WhatsApp -->
                            <a href="https://wa.me/<?php echo $this->config->item('whatsapp_numero'); ?>?text=<?php echo urlencode($this->config->item('whatsapp_mensaje') . $p->nombre); ?>"
                               class="btn btn-consultar btn-sm w-100 mt-2" target="_blank">
                                <i class="bi bi-whatsapp me-1"></i>Consultar precio
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
            <?php endforeach; ?>

    <?php endif; ?>
</div>
<script>
function swapProdImg(imgId, thumb, src) {
    document.getElementById(imgId).src = src;
    thumb.closest('.product-thumbs').querySelectorAll('img').forEach(function(t) {
        t.classList.remove('active');
    });
    thumb.classList.add('active');
}
function quitar_ocultacion(enlace){
    var span = enlace.closest('.card-body').querySelector('.texto-descrip');
    if (span) {
        var oculto = span.classList.toggle('div-ocultar');
        enlace.textContent = oculto ? 'Ver más...' : 'Ver menos...';
    }
    return false;
}
</script>