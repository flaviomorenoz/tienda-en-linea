<style>
</style>
<div class="container">

    <!-- Hero banner -->
    <div class="tol-hero rounded-4 mb-4 text-white d-flex align-items-center" style="border-width:1px;border-right: 1px solid var(--color-tol-tit);
    border-bottom: 1px solid var(--color-tol-tit);">
        <div>
            <h1 class="tol-tit"><?php echo $this->config->item('tienda_nombre'); ?></h1>
            <!--<p class="lead mb-3"><?php echo $this->config->item('tienda_slogan'); ?></p>-->
            <a href="#productos" class="btn btn-light btn-lg fw-semibold tol-tit2">
                <i class="bi bi-grid-3x3-gap me-2"></i>Ropa y accesorios para todos
            </a>
        </div>
        <div class="flex-grow-1 imagen-movil-oculta hero-logos" style="padding-left: 55px;">
            <?php for ($i = 0; $i < 5; $i++): 
                //$color = ['#ff6b6b', '#6c5ce7', '#00cec9', '#55efc4', '#fdcb6e', '#ff6b6b'];
                $color = ['#ff6b6b', '#da4f4f', '#a83535', '#801c1c','#5c1818' ];
            ?>
            <!--<img src="<?php echo base_url('assets/img/logox.png'); ?>" alt="Hero Image">-->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1260 1260">
                <rect x="0" y="0" width="1060" height="1060" rx="165" ry="165" fill="<?php echo $color[$i]; ?>"/>
                <line x1="835" y1="437" x2="1060" y2="437" stroke="#ffffff" stroke-width="6"/>
                <line x1="0" y1="691" x2="162" y2="691" stroke="#ffffff" stroke-width="6"/>
                <text x="192" y="495" font-family="Georgia, 'Times New Roman', serif" font-size="250" fill="#f09696">Bella</text>
                <text x="408" y="745" font-family="Georgia, 'Times New Roman', serif" font-size="250" fill="#f09696">Rosse</text>
            </svg>
            <?php endfor; ?>
        </div>
    </div>

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

    <!-- Grid de productos -->
    <?php if (empty($productos)): ?>
    <div class="text-center py-5">
        <i class="bi bi-box-seam text-muted" style="font-size:4rem;"></i>
        <p class="text-muted mt-3">No hay productos disponibles en esta categoría.</p>
        <a href="<?php echo base_url(); ?>" class="btn btn-dark">Ver todos los productos</a>
    </div>
    <?php else: ?>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
        <?php foreach ($productos as $p): ?>
        <div class="col">
            <div class="card product-card h-100 border-0 shadow-sm">
                <!-- Imagen principal -->
                <?php
                    $trz_img2 = isset($p->imagen2) ? $p->imagen2 : '';
                    $trz_img3 = isset($p->imagen3) ? $p->imagen3 : '';
                    traza("home.php producto id=" . $p->id . " nombre='" . $p->nombre . "' imagen_url='" . $p->imagen_url . "' imagen2='" . $trz_img2 . "' imagen3='" . $trz_img3 . "' img(0)='" . $p->imagenes[0] . "'");
                    $img_principal = ruta_imagen_producto($p->imagenes[0]);
                    $img_default   = base_url('assets/img/default.png');
                    $img_id        = 'prod-img-' . $p->id;
                    traza("home.php producto id={$p->id} img_principal='$img_principal'");
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
                    <!-- Categoría -->
                    <span class="text-muted small mb-1"><?php echo htmlspecialchars($p->categoria, ENT_QUOTES, 'UTF-8'); ?></span>

                    <!-- Nombre -->
                    <h6 class="card-title fw-semibold mb-2">
                        <a href="<?php echo base_url('tienda/producto/' . $p->id); ?>" class="text-dark text-decoration-none product-name-link">
                            <?php echo htmlspecialchars($p->nombre, ENT_QUOTES, 'UTF-8'); ?>
                        </a><br>
                        <span class="texto-descrip"><pre><?php echo $p->descripcion; ?></pre></span>
                    </h6>

                    <div class="mt-auto">
                        <?php if ($p->tiene_precio && $p->precio): ?>
                        <!-- Precio y botón compra -->
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
</script>
