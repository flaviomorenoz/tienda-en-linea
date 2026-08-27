<style>
    /* =====================================================
       QUIÉNES SOMOS · Tema 5 (estilo Aruma)
       Magenta #FA0082 · Texto #3A3735 · Negro #1D1D1B
       ===================================================== */
    .qs-page {
        font-family: var(--font, 'Poppins', sans-serif);
        color: var(--texto, #3A3735);
        letter-spacing: 0.02em;
    }

    .qs-kicker {
        display: inline-block;
        background: var(--primary, #FA0082);
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.32em;
        text-transform: uppercase;
        padding: 0.45rem 1rem;
        margin-bottom: 1.1rem;
    }

    .qs-title {
        font-family: 'Fraunces', serif;
        font-weight: 800;
        color: var(--negro, #1D1D1B);
        text-transform: uppercase;
        letter-spacing: 0.01em;
    }

    .qs-subtitle {
        color: var(--texto-muted, #8a8580);
        font-weight: 600;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        font-size: 0.72rem;
    }

    /* ---- Hero ---- */
    .qs-hero {
        background: linear-gradient(135deg, #1D1D1B 0%, #42122b 55%, #AC0078 100%);
        color: #ffffff;
        text-align: center;
        padding: 4rem 1.5rem 3.6rem;
        position: relative;
    }
    .qs-hero::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 4px;
        background: var(--primary, #FA0082);
    }
    .qs-hero h1 {
        font-family: 'Fraunces', serif;
        font-weight: 800;
        font-size: clamp(2.1rem, 5vw, 3.5rem);
        letter-spacing: 0.01em;
        margin-bottom: 1rem;
        text-transform: uppercase;
    }
    .qs-hero .qs-slogan {
        max-width: 760px;
        margin: 0 auto;
        font-size: 1.05rem;
        line-height: 1.7;
        color: #f6ebf0;
    }
    .qs-hero .qs-meta {
        margin-top: 1.4rem;
        font-size: 0.75rem;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        color: #e8b8cd;
    }
    /* ---- Secciones ---- */
    .qs-section {
        margin-bottom: 3.2rem;
    }

    .qs-copy {
        font-size: 1.02rem;
        line-height: 1.9;
        color: var(--texto, #3A3735);
    }

    /* Resaltado (primer párrafo) */
    .qs-lead-box {
        border-left: 4px solid var(--primary, #FA0082);
        background: var(--light-bg, #f7f6f4);
        padding: 1.4rem 1.6rem;
        margin-bottom: 1.6rem;
    }
    .qs-lead-box p {
        margin: 0;
        font-size: 1.08rem;
        line-height: 1.85;
        color: var(--negro, #1D1D1B);
        font-weight: 500;
    }

    /* ---- Cards de oferta ---- */
    .qs-card {
        border: 1px solid var(--borde, #e8e6e3);
        border-top: 3px solid var(--primary, #FA0082);
        border-radius: 0;
        background: #ffffff;
        height: 100%;
        padding: 1.6rem 1.4rem;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .qs-card:hover {
        box-shadow: 0 10px 24px rgba(26, 26, 24, 0.08);
        transform: translateY(-3px);
    }
    .qs-card .qs-ico {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary, #FA0082);
        color: #ffffff;
        font-size: 1.35rem;
        margin-bottom: 1rem;
        border-radius: 0;
    }
    .qs-card h5 {
        font-family: 'Fraunces', serif;
        font-weight: 700;
        color: var(--negro, #1D1D1B);
        text-transform: uppercase;
        font-size: 1rem;
        letter-spacing: 0.03em;
        margin-bottom: 0.6rem;
    }
    .qs-card p {
        font-size: 0.92rem;
        line-height: 1.75;
        color: var(--texto, #3A3735);
        margin: 0;
    }
    /* ---- Visión (banda oscura) ---- */
    .qs-vision {
        background: var(--negro, #1D1D1B);
        color: #ffffff;
        padding: 3rem 2rem;
        border-radius: 0;
        position: relative;
    }
    .qs-vision::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        height: 4px;
        width: 100%;
        background: var(--primary, #FA0082);
    }
    .qs-vision .qs-subtitle {
        color: #e8b8cd;
    }
    .qs-vision h2 {
        font-family: 'Fraunces', serif;
        font-weight: 800;
        font-size: clamp(1.6rem, 3.5vw, 2.4rem);
        text-transform: uppercase;
        letter-spacing: 0.01em;
        margin-bottom: 1.1rem;
    }
    .qs-vision p {
        font-size: 1.05rem;
        line-height: 1.9;
        color: #f0e6eb;
        max-width: 860px;
    }

    /* ---- Valores (pills) ---- */
    .qs-value {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid var(--primary, #FA0082);
        color: var(--primary, #FA0082);
        background: #ffffff;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        padding: 0.55rem 1.15rem;
        border-radius: 0;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .qs-value:hover {
        background: var(--primary, #FA0082);
        color: #ffffff;
    }

    /* ---- CTA final ---- */
    .qs-cta {
        background: var(--primary, #FA0082);
        color: #ffffff;
        text-align: center;
        padding: 3.2rem 1.5rem;
        border-radius: 0;
    }
    .qs-cta .qs-cta-brand {
        font-family: 'Fraunces', serif;
        font-weight: 900;
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .qs-cta .qs-cta-tagline {
        font-size: 1.05rem;
        font-weight: 500;
        letter-spacing: 0.04em;
        margin: 0.6rem auto 1.6rem;
        max-width: 640px;
    }
    .qs-cta .btn-qs {
        border-radius: 0;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        padding: 0.7rem 1.6rem;
    }
    .qs-cta .btn-qs-dark {
        background: var(--negro, #1D1D1B);
        color: #ffffff;
        border: 1px solid var(--negro, #1D1D1B);
    }
    .qs-cta .btn-qs-dark:hover {
        background: #000000;
        color: #ffffff;
    }
    .qs-cta .btn-qs-outline {
        background: transparent;
        color: #ffffff;
        border: 1px solid #ffffff;
    }
    .qs-cta .btn-qs-outline:hover {
        background: #ffffff;
        color: var(--primary, #FA0082);
    }
</style>
<div class="container qs-page" style="padding-top:0px!important;">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mt-2 mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>" class="text-decoration-none">Tienda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Quiénes somos</li>
        </ol>
    </nav>

    <!-- ================= HERO ================= -->
    <section class="qs-hero mb-5">
        <span class="qs-kicker"><i class="bi bi-bag-heart-fill me-2"></i>BELLA ROSSE</span>
        <h1>Quiénes somos</h1>
        <p class="qs-slogan">
            Ropa interior para tu familia y oportunidades para tu negocio.
        </p>
        <div class="qs-meta">
            <i class="bi bi-geo-alt-fill me-1"></i> Ventanilla, Callao · Desde 2019 ·
            <i class="bi bi-truck ms-1 me-1"></i> Envíos a todo el Perú
        </div>
    </section>

    <!-- ================= NUESTRA HISTORIA ================= -->
    <section class="qs-section">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <span class="qs-subtitle">Nuestra historia</span>
                <h2 class="qs-title h1 mb-3">Una familia que crece contigo</h2>
            </div>
            <div class="col-lg-7">
                <div class="qs-lead-box">
                    <p>
                        <strong>BELLA ROSSE</strong> nació en el año 2019 en Ventanilla, Callao,
                        con el propósito de ofrecer ropa interior para toda la familia, combinando
                        variedad, precios accesibles y una atención cercana y personalizada.
                    </p>
                </div>
                <p class="qs-copy">
                    Lo que comenzó como un pequeño emprendimiento ha ido creciendo gracias a la
                    confianza de nuestros clientes. Actualmente ofrecemos <strong>truzas para damas
                    y niñas</strong>, <strong>bóxers para niños y caballeros</strong>, atendiendo
                    tanto ventas <strong>al por mayor</strong> como <strong>al por menor</strong>.
                </p>
                <p class="qs-copy">
                    Trabajamos para familias, público en general y especialmente para
                    <strong>emprendedores</strong> que encuentran en BELLA ROSSE una oportunidad para
                    iniciar o hacer crecer su propio negocio, accediendo a productos a precios competitivos.
                </p>
            </div>
        </div>
    </section>
    <!-- ================= QUÉ OFRECEMOS ================= -->
    <section class="qs-section">
        <div class="text-center mb-4">
            <span class="qs-subtitle">Nuestro catálogo</span>
            <h2 class="qs-title h1">¿Qué ofrecemos?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="qs-card">
                    <div class="qs-ico"><i class="bi bi-flower1"></i></div>
                    <h5>Truzas</h5>
                    <p>Para damas y niñas, en variedad de modelos, tallas y colores que acompañan cada etapa y estilo.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="qs-card">
                    <div class="qs-ico"><i class="bi bi-gem"></i></div>
                    <h5>Bóxers</h5>
                    <p>Para niños y caballeros, con la comodidad y durabilidad que exige el día a día.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="qs-card">
                    <div class="qs-ico"><i class="bi bi-shop"></i></div>
                    <h5>Mayor y menor</h5>
                    <p>Vendemos tanto al por mayor como al por menor, pensando en tu familia y en tu negocio.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= NOS CARACTERIZA ================= -->
    <section class="qs-section">
        <div class="text-center mb-4">
            <span class="qs-subtitle">¿Por qué elegirnos?</span>
            <h2 class="qs-title h1">Nos caracteriza</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="qs-card">
                    <div class="qs-ico"><i class="bi bi-person-heart"></i></div>
                    <h5>Atención personalizada</h5>
                    <p>Te atendemos de cerca, como a una familia, resolviendo tus dudas en cada compra.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="qs-card">
                    <div class="qs-ico"><i class="bi bi-tag"></i></div>
                    <h5>Precios accesibles</h5>
                    <p>Productos de calidad a precios competitivos para el público en general y emprendedores.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="qs-card">
                    <div class="qs-ico"><i class="bi bi-truck"></i></div>
                    <h5>Despachos rápidos</h5>
                    <p>Realizamos envíos <strong>todos los días a todo el Perú</strong>.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- ================= VISIÓN ================= -->
    <section class="qs-vision qs-section">
        <div class="row align-items-center g-4">
            <div class="col-lg-4">
                <span class="qs-subtitle">Nuestra visión</span>
                <h2 class="mb-0">Hacia dónde vamos</h2>
            </div>
            <div class="col-lg-8">
                <p class="mb-0">
                    Nuestra visión es continuar creciendo hasta convertirnos en una de las
                    <strong>principales empresas distribuidoras de ropa interior del Perú</strong>,
                    manteniendo siempre los valores que nos representan.
                </p>
            </div>
        </div>
    </section>

    <!-- ================= VALORES ================= -->
    <section class="qs-section">
        <div class="text-center mb-4">
            <span class="qs-subtitle">Lo que nos representa</span>
            <h2 class="qs-title h1">Nuestros valores</h2>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <span class="qs-value"><i class="bi bi-check-circle-fill"></i>Confianza</span>
            <span class="qs-value"><i class="bi bi-check-circle-fill"></i>Calidad</span>
            <span class="qs-value"><i class="bi bi-check-circle-fill"></i>Compromiso</span>
            <span class="qs-value"><i class="bi bi-check-circle-fill"></i>Cercanía</span>
            <span class="qs-value"><i class="bi bi-check-circle-fill"></i>Responsabilidad</span>
        </div>
    </section>

    <!-- ================= CTA FINAL ================= -->
    <section class="qs-cta mb-4">
        <div class="qs-cta-brand">BELLA ROSSE</div>
        <p class="qs-cta-tagline">
            Ropa interior para tu familia y oportunidades para tu negocio.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?php echo base_url(); ?>" class="btn btn-qs btn-qs-dark">
                <i class="bi bi-bag-heart me-2"></i>Ver catálogo
            </a>
            <a href="https://wa.me/<?php echo $this->config->item('whatsapp_numero'); ?>?text=<?php echo urlencode($this->config->item('whatsapp_mensaje') . ' Quiénes somos'); ?>"
               class="btn btn-qs btn-qs-outline" target="_blank">
                <i class="bi bi-whatsapp me-2"></i>Escríbenos
            </a>
        </div>
    </section>

</div>





