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

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">

            <!-- Encabezado -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-uppercase">
                    Política de Cambios y Devoluciones
                </h1>
                <h2 class="h4 text-muted">BELLAROSSE</h2>

                <p class="text-muted mt-3 mb-0">
                    <i class="bi bi-calendar3"></i>
                    Última actualización: septiembre de 2026
                </p>
            </div>

            <!-- Introducción -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 p-md-5">

                    <p class="lead">
                        En <strong>BELLAROSSE</strong> queremos que tu experiencia
                        de compra sea satisfactoria. Por ello, hemos establecido la
                        siguiente Política de Cambios y Devoluciones, aplicable a las
                        compras realizadas a través de nuestra tienda online.
                    </p>

                    <p>
                        Esta política se aplica sin perjuicio de los derechos que
                        corresponden al consumidor conforme a la legislación peruana
                        de protección y defensa del consumidor.
                    </p>

                </div>
            </div>

            <!-- 1. Cambios -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    1. Cambios de productos
                </h2>

                <p>
                    Aceptamos cambios de prendas por:
                </p>

                <ul>
                    <li>Cambio de talla.</li>
                    <li>Cambio de color, sujeto a disponibilidad.</li>
                    <li>Cambio por otro modelo, sujeto a disponibilidad.</li>
                </ul>

                <p>
                    El cliente podrá solicitar el cambio dentro de los
                    <strong>7 días calendario</strong> posteriores a la
                    recepción del producto.
                </p>

                <p>
                    Para solicitar un cambio, el producto deberá encontrarse en
                    condiciones adecuadas para su devolución, considerando la
                    naturaleza de la prenda. Deberá conservar, cuando corresponda,
                    sus etiquetas y accesorios originales y no presentar señales de
                    uso, lavado, manchas, daños o alteraciones.
                </p>

                <div class="alert alert-warning" role="alert">
                    <strong>Importante:</strong>
                    Las condiciones anteriores corresponden a cambios solicitados
                    por preferencia del cliente, como talla, color o modelo. No
                    limitan los derechos que correspondan cuando el producto
                    presente una falla, defecto o no corresponda con lo ofrecido.
                </div>
            </section>

            <!-- 2. Fallas -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    2. Productos con fallas, defectos o enviados incorrectamente
                </h2>

                <p>Si recibiste:</p>

                <ul>
                    <li>Una prenda diferente a la solicitada.</li>
                    <li>Una talla diferente a la comprada.</li>
                    <li>Un color diferente al seleccionado.</li>
                    <li>Un producto con defecto de fabricación.</li>
                    <li>
                        Un producto que no corresponde con las características
                        ofrecidas en nuestra tienda.
                    </li>
                </ul>

                <p>
                    Deberás comunicarte con nosotros para evaluar el caso y
                    brindarte la solución que corresponda conforme a la normativa
                    vigente.
                </p>

                <p>
                    Dependiendo del caso, podrá corresponder el cambio, reposición,
                    reparación cuando resulte aplicable o devolución del importe
                    pagado.
                </p>
            </section>

            <!-- 3. Condiciones -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    3. Condiciones del producto para cambios por preferencia
                </h2>

                <p>
                    Para cambios de talla, color o modelo solicitados por preferencia
                    del cliente, la prenda deberá:
                </p>

                <ul>
                    <li>No haber sido utilizada.</li>
                    <li>No haber sido lavada.</li>
                    <li>
                        No presentar manchas, olores, modificaciones o daños.
                    </li>
                    <li>Conservar sus etiquetas, cuando corresponda.</li>
                    <li>
                        Ser devuelta con sus accesorios y complementos originales,
                        cuando corresponda.
                    </li>
                </ul>

                <p>
                    Estas condiciones no serán utilizadas para restringir los
                    derechos del consumidor cuando exista un defecto, falta de
                    idoneidad o incumplimiento respecto de lo ofrecido.
                </p>
            </section>

            <!-- 4. Promociones -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    4. Productos en promoción, descuento u oferta
                </h2>

                <p>
                    Los productos adquiridos mediante promociones, descuentos u
                    ofertas también están sujetos a las disposiciones de protección
                    al consumidor.
                </p>

                <p>
                    Las condiciones específicas de una promoción serán informadas
                    antes de realizar la compra.
                </p>

                <p>
                    Una promoción no elimina los derechos que correspondan al
                    consumidor cuando el producto presente defectos, no sea idóneo
                    o no corresponda con las características ofrecidas.
                </p>
            </section>

            <!-- 5. Higiene -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    5. Productos que no admiten cambio por razones de higiene
                </h2>

                <p>
                    Cuando por la naturaleza del producto existan razones objetivas
                    de higiene o protección de la salud que hagan razonable
                    restringir un cambio por preferencia del cliente, dicha
                    condición será informada claramente antes de efectuar la compra.
                </p>

                <p>
                    Esta restricción no afecta los derechos que correspondan al
                    consumidor frente a productos defectuosos o que no sean idóneos.
                </p>
            </section>

            <!-- 6. Gastos -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    6. Gastos de envío
                </h2>

                <p>
                    Cuando el cambio sea solicitado por decisión del cliente, por
                    ejemplo, por cambio de talla, color o modelo, los gastos de
                    envío asociados al cambio serán asumidos por:
                </p>

                <div class="alert alert-secondary text-center fw-bold">
                    [EL CLIENTE]
                </div>

                <p>
                    Cuando el cambio o devolución se origine por un error atribuible
                    a nuestra tienda o por un producto defectuoso o que no
                    corresponda con lo ofrecido, asumiremos los costos que
                    correspondan para solucionar el inconveniente.
                </p>
            </section>

            <!-- 7. Procedimiento -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    7. Procedimiento para solicitar un cambio o devolución
                </h2>

                <p>
                    Para iniciar una solicitud, el cliente deberá comunicarse con
                    nosotros mediante:
                </p>

                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">

                        <p class="mb-2">
                            <strong>WhatsApp:</strong>
                            <a href="https://wa.me/51991629237"
                               target="_blank"
                               class="text-decoration-none">
                                991629237
                            </a>
                        </p>

                        <p class="mb-2">
                            <strong>Correo electrónico:</strong>
                            <a href="mailto:bellarosse176@gmail.com"
                               class="text-decoration-none">
                                bellarosse176@gmail.com
                            </a>
                        </p>

                        <p class="mb-0">
                            <strong>Horario de atención:</strong>
                            de 9am - 6pm
                        </p>

                    </div>
                </div>

                <p>Deberá indicar:</p>

                <ul>
                    <li>Número de pedido.</li>
                    <li>Nombre del cliente.</li>
                    <li>Producto que desea cambiar o devolver.</li>
                    <li>Motivo de la solicitud.</li>
                    <li>
                        Fotografías del producto cuando sean necesarias para
                        evaluar el caso.
                    </li>
                </ul>

                <p>
                    Nuestro equipo revisará la solicitud y comunicará al cliente
                    los pasos para realizar el cambio o devolución.
                </p>
            </section>

            <!-- 8. Disponibilidad -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    8. Disponibilidad del producto para cambios
                </h2>

                <p>
                    Los cambios estarán sujetos a la disponibilidad de la talla,
                    color o modelo solicitado.
                </p>

                <p>
                    Si el producto solicitado no se encuentra disponible, podremos
                    ofrecer al cliente las alternativas que correspondan, según el
                    caso.
                </p>
            </section>

            <!-- 9. Devolución -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    9. Devolución del dinero
                </h2>

                <p>
                    Cuando corresponda efectuar una devolución del importe pagado,
                    esta se realizará mediante el mismo medio de pago utilizado en
                    la compra, cuando ello sea posible, o mediante otro mecanismo
                    previamente coordinado con el cliente.
                </p>

                <p>
                    El plazo de devolución dependerá del medio de pago y de los
                    procedimientos de la entidad financiera correspondiente.
                </p>
            </section>

            <!-- 10. Compras por Internet -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    10. Compras realizadas por Internet
                </h2>

                <p>
                    Las compras realizadas mediante nuestra tienda online están
                    sujetas a las normas peruanas de protección y defensa del
                    consumidor.
                </p>

                <p>
                    La información proporcionada en nuestra página web, incluyendo
                    características, precios, tallas, colores, fotografías y
                    condiciones de venta, forma parte de la información que el
                    consumidor utiliza para tomar su decisión de compra.
                </p>
            </section>

            <!-- 11. Libro de reclamaciones -->
            <section class="mb-5">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    11. Libro de Reclamaciones
                </h2>

                <p>
                    Nuestra tienda pone a disposición de los consumidores el
                    <strong>Libro de Reclamaciones</strong>, de acuerdo con la
                    normativa peruana aplicable.
                </p>

                <p>
                    El consumidor podrá presentar una queja o reclamo cuando
                    considere que existe algún inconveniente relacionado con la
                    atención, el producto adquirido o el servicio recibido.
                </p>
            </section>

            <!-- 12. Contacto -->
            <section class="mb-4">
                <h2 class="h4 fw-bold border-bottom pb-2 mb-3">
                    12. Contacto
                </h2>

                <p>
                    Para cualquier consulta relacionada con cambios o devoluciones,
                    puedes comunicarte con nosotros:
                </p>

                <div class="card border-0 bg-light shadow-sm">
                    <div class="card-body p-4">

                        <h3 class="h5 fw-bold mb-4">BELLAROSSE</h3>

                        <p class="mb-2">
                            <strong>RUC:</strong> 20615445551
                        </p>

                        <p class="mb-2">
                            <strong>Correo:</strong>
                            <a href="mailto:bellarosse176@gmail.com"
                               class="text-decoration-none">
                                bellarosse176@gmail.com
                            </a>
                        </p>

                        <p class="mb-2">
                            <strong>WhatsApp:</strong>
                            <a href="https://wa.me/51991629237"
                               target="_blank"
                               class="text-decoration-none">
                                991629237
                            </a>
                        </p>

                        <p class="mb-0">
                            <strong>Dirección:</strong><br>
                            MZA. H LOTE 19 A.H. ENMANUEL PROV. CONST. DEL CALLAO -
                            PROV. CONT. DEL CALLAO - VENTANILLA
                        </p>

                    </div>
                </div>
            </section>

            <!-- Actualización -->
            <div class="alert alert-info mt-5" role="alert">
                <strong>Actualización de la política:</strong>
                La presente política podrá ser actualizada cuando resulte necesario
                para adecuarla a cambios en nuestros procedimientos o en la
                normativa aplicable.
            </div>

        </div>
    </div>
</div>
