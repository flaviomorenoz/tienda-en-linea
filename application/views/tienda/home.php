<style>
    :root {
        --c1: #ff6b6b;
        --c2: #6c5ce7;
        --c3: #00cec9;
        --c4: #55efc4;
        --c5: #fdcb6e;
    }

    .hero {
        position: relative;
        overflow: hidden;

        /*height: 230px;*/
        border-radius: 20px;
        padding: 40px;

        color: white;
        /*background: #111;*/
    }

    .hero::before {
        content: "";

        position: absolute;
        inset: -50%;

        background:
            radial-gradient(circle, var(--c1) 0%, transparent 50%),
            radial-gradient(circle, var(--c2) 0%, transparent 50%),
            radial-gradient(circle, var(--c3) 0%, transparent 50%),
            radial-gradient(circle, var(--c4) 0%, transparent 50%),
            radial-gradient(circle, var(--c5) 0%, transparent 50%);

        background-size: 50% 50%;

        filter: blur(80px);

        animation: aurora 20s linear infinite;
    }

    .hero > * {
        position: relative;
        z-index: 1;
    }

    @keyframes aurora {
        0% {
            transform: rotate(0deg) scale(1);
        }
        50% {
            transform: rotate(180deg) scale(1.2);
        }
        100% {
            transform: rotate(360deg) scale(1);
        }
    }

    /* Ocultar imágenes en móviles */
    .imagen-movil-oculta {
        display: none;
    }

    /* Mostrar solo en desktop */
    @media (min-width: 768px) {
        .imagen-movil-oculta {
            display: inline-block; /* o inline-block según tu layout */
        }
    }

    /* Fila de logos del hero: se achican en lugar de pasar a otra fila */
    .hero-logos {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        overflow: hidden;
        width: 100%;
    }
    .hero-logos img {
        height: auto;
        max-height: 100px;
        max-width: 100px;
        min-width: 24px;
        flex: 1 1 70px;
        object-fit: contain;
    }

    /* Si quieres mostrar solo una imagen en móvil */
    @media (max-width: 767px) {
        .imagen-movil-visible {
            display: inline-block;
        }
        .imagen-movil-oculta {
            display: none;
        }
    }

    /* Miniaturas de producto */
    .product-thumbs {
        display: flex;
        gap: 5px;
        justify-content: center;
        padding: 6px 8px 2px;
    }
    .product-thumbs img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 5px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s, opacity 0.2s;
        opacity: 0.65;
    }
    .product-thumbs img.active,
    .product-thumbs img:hover {
        border-color: #343a40;
        opacity: 1;
    }
</style>
<div class="container">

    <!-- Hero banner -->
    <div class="hero hero-banner rounded-4 mb-4 text-white d-flex align-items-center">
        <div>
            <h1 class="display-5 fw-bold mb-2"><?php echo $this->config->item('tienda_nombre'); ?></h1>
            <!--<p class="lead mb-3"><?php echo $this->config->item('tienda_slogan'); ?></p>-->
            <a href="#productos" class="btn btn-light btn-lg fw-semibold">
                <i class="bi bi-grid-3x3-gap me-2"></i>Ropa y accesorios para todos
            </a>
        </div>
        <div class="flex-grow-1 imagen-movil-oculta hero-logos" style="padding-left: 55px;">
            <?php for ($i = 0; $i < 6; $i++): 
                //$color = ['#ff6b6b', '#6c5ce7', '#00cec9', '#55efc4', '#fdcb6e', '#ff6b6b'];
                $color = ['#3a0b0b', '#5c1818', '#801c1c', '#a83535', '#da4f4f', '#ff6b6b'];
            ?>
            <!--<img src="<?php echo base_url('assets/img/logox.png'); ?>" alt="Hero Image">-->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1260 1260">
                <rect x="0" y="0" width="1260" height="1260" rx="185" ry="185" fill="<?php echo $color[$i]; ?>"/>
                <line x1="835" y1="437" x2="1260" y2="437" stroke="#ffffff" stroke-width="10"/>
                <line x1="0" y1="691" x2="192" y2="691" stroke="#ffffff" stroke-width="10"/>
                <text x="192" y="595" font-family="Georgia, 'Times New Roman', serif" font-size="300" fill="#ffffff">Bella</text>
                <text x="408" y="845" font-family="Georgia, 'Times New Roman', serif" font-size="300" fill="#ffffff">Rosse</text>
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
                             onerror="this.onerror=null;this.src='<?php echo $img_default; ?>'">
                             data-combre="<?php echo htmlspecialchars($img_principal, ENT_QUOTES, 'UTF-8'); ?>"
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
