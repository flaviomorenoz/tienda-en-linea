<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo) ? htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') : $this->config->item('tienda_nombre'); ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Glyphicons (Bootstrap 3 font, sin conflictos con BS5) -->
    <style>
        @font-face {
            font-family: 'Glyphicons Halflings';
            src: url('https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/fonts/glyphicons-halflings-regular.woff2') format('woff2'),
                 url('https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/fonts/glyphicons-halflings-regular.woff') format('woff'),
                 url('https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/fonts/glyphicons-halflings-regular.ttf') format('truetype');
        }
        .glyphicon { position: relative; top: 1px; display: inline-block;
                     font-family: 'Glyphicons Halflings'; font-style: normal;
                     font-weight: normal; line-height: 1;
                     -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        .glyphicon-eye-open:before        { content: "\e105"; }
        .glyphicon-eye-close:before       { content: "\e106"; }
        .glyphicon-search:before          { content: "\e003"; }
        .glyphicon-pencil:before          { content: "\270f"; }
        .glyphicon-trash:before           { content: "\e020"; }
        .glyphicon-ok:before              { content: "\e013"; }
        .glyphicon-remove:before          { content: "\e014"; }
        .glyphicon-plus:before            { content: "\002b"; }
        .glyphicon-minus:before           { content: "\2212"; }
        .glyphicon-shopping-cart:before   { content: "\e116"; }
    </style>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- CSS Custom -->
    <?php
    $CI =& get_instance();
    if (!isset($CI->Ajustes_model)) { $CI->load->model('Ajustes_model'); }
    $tema_css = $CI->Ajustes_model->get_tema_activo();
    ?>
    <link href="<?php echo base_url('assets/css/' . $tema_css . '?v=5'); ?>" rel="stylesheet">
</head>
<body>
<style>
    .banner0{
        height:50px;
        background-color: var(--primary);
        font-size: 20px;
        font-style: italic;
        padding:10px;
        text-align: center;
    }
    .banner2a{
        height:50px;
        padding:10px 8px;
        border-style: none;
        border-color:gray;
        border-width:1px;
    }
    .tol-logo{
        color:var(--primary);
        font-size:24px;
        font-weight: 400;
    }
    .buscador {
        width: 100%;
        max-width: 670px;
        height: 40px;

        display: flex;
        align-items: center;

        border: 1px solid #333;
        border-radius: 25px;

        padding: 0 10px 0 15px;
        box-sizing: border-box;

        background: #fff;
    }

    /* Campo de texto */
    .buscador-input {
        flex: 1;

        width: 100%;
        height: 100%;

        border: none;
        outline: none;

        font-size: 16px;
        color: #333;
        background: transparent;
    }

    .buscador-input::placeholder {
        color: #999;
        opacity: 1;
    }

    /* Botones de iconos */
    .buscador-icono {
        width: 32px;
        height: 32px;

        display: flex;
        align-items: center;
        justify-content: center;

        border: none;
        padding: 0;
        margin: 0;

        background: transparent;
        cursor: pointer;
    }

    .buscador-icono svg {
        width: 21px;
        height: 21px;

        fill: none;
        stroke: #444;
        stroke-width: 1.7;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .buscador-icono:hover svg {
        stroke: #000;
    }

    /* Lupa */
    .buscador-submit {
        margin-left: 2px;
    }
    .tol-300{
        color:var(--primary);
        font-weight: bold;
        font-family:verdana;
    }
    .tol-301{
        font-weight: bold;
        font-family:verdana;
        font-size:12px;
    }
    @media (max-width: 768px) {
        .div-ocultar {
            display: none;
        }
    }
    .texto-corto {
        display: none;
        font-size:14px;
    }

    @media (max-width: 768px) {
        .texto-completo {
            display: none;
        }

        .texto-corto {
            display: inline;
        }
    }

    /* ===================================================
       MENU LATERAL PLEGABLE (click en rallitas)
       =================================================== */
    #btn-menu-lateral {
        cursor: pointer;
    }

    /* Fondo oscuro semitransparente detras del menu */
    .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.35);
        opacity: 0;
        visibility: hidden;
        transition: opacity .3s ease, visibility .3s ease;
        z-index: 1080;
    }
    .sidebar-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    /* Panel vertical en la parte izquierda (blanco semitransparente) */
    .sidebar-menu {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 300px;
        max-width: 88vw;
        background: rgba(255, 255, 255, 0.88);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        border-right: 1px solid rgba(250, 1, 131, 0.12);
        box-shadow: 4px 0 26px rgba(0, 0, 0, 0.15);
        transform: translateX(-100%);
        transition: transform .32s ease;
        z-index: 1090;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        font-family: var(--font);
        color: #333;
    }
    .sidebar-menu.abierto {
        transform: translateX(0);
    }

    /* Cabecera del panel */
    .sidebar-cabeza {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 16px 18px 14px;
        border-bottom: 1px solid rgba(250, 1, 131, 0.15);
        background: linear-gradient(135deg, rgba(250, 1, 131, 0.08), rgba(255, 255, 255, 0));
    }
    .sidebar-titulo {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: var(--font);
        font-size: 19px;
        font-weight: 600;
        letter-spacing: .3px;
        color: var(--primary);
    }
    .sidebar-cerrar {
        border: none;
        background: rgba(250, 1, 131, 0.10);
        color: var(--primary);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        line-height: 1;
        cursor: pointer;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }
    .sidebar-cerrar:hover {
        background: var(--primary);
        color: #fff;
        transform: rotate(90deg);
    }

    /* Lista de opciones */
    .sidebar-nav {
        list-style: none;
        margin: 0;
        padding: 12px 10px;
        flex: 1;
    }
    .sidebar-nav li {
        margin-bottom: 4px;
    }
    .sidebar-opcion {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        color: #333;
        font-size: 15px;
        font-weight: 500;
        font-family: var(--font);
        text-decoration: none;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }
    .sidebar-opcion i {
        width: 22px;
        text-align: center;
        font-size: 1.15rem;
        color: var(--primary);
        transition: color .2s ease;
    }
    .sidebar-opcion:hover {
        background: var(--primary);
        color: #fff;
        transform: translateX(4px);
    }
    .sidebar-opcion:hover i {
        color: #fff;
    }

    /* Pie del panel */
    .sidebar-pie {
        padding: 13px 18px;
        border-top: 1px solid rgba(250, 1, 131, 0.15);
        font-size: 12.5px;
        color: #888;
        font-family: var(--font);
    }
</style>
<!-- NAVBAR -->
<div class="container" style="display:block!important">
    <div class="row">
        <div class="col-sm-12 banner0">
            <span class="texto-completo"><?= isset($texto_banner) ? $texto_banner : "" ?></span>
            <span class="texto-corto"><?= isset($texto_banner) ? $texto_banner : "" ?></span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-5 col-md-4 col-lg-4 banner2a">
            <a class="navbar-brand fw-bold tol-logo" href="<?php echo base_url(); ?>">
                <!--<i class="bi bi-bag-heart-fill me-2"></i>-->
                <img src="<?=base_url()?>assets/img/rallas.png" id="btn-menu-lateral" role="button" tabindex="0" alt="Abrir menú" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu-lateral" style="height:25px;margin-bottom:4px;">
                <?php echo $this->config->item('tienda_nombre'); ?>
            </a>
        </div>
        <div class="col-8 col-sm-5 col-md-6 col-lg-4 banner2a" id="busqueda" style="padding-top:15px;">
            <form action="<?php echo base_url(); ?>" method="get" class="buscador" role="search">
                <input
                    type="text"
                    name="q"
                    class="buscador-input"
                    placeholder="Buscar producto"
                    value="<?php echo isset($termino_busqueda) ? htmlspecialchars($termino_busqueda, ENT_QUOTES, 'UTF-8') : ''; ?>"
                    autocomplete="off"
                >

                <button type="button" class="buscador-icono" aria-label="Buscar por imagen">
                    <!-- Cámara -->
                    <svg viewBox="0 0 24 24">
                        <path d="M9 4l1.5-2h3L15 4h3a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h3z"/>
                        <circle cx="12" cy="11.5" r="3.2"/>
                    </svg>
                </button>

                <button type="button" class="buscador-icono" aria-label="Buscar por voz">
                    <!-- Micrófono -->
                    <svg viewBox="0 0 24 24">
                        <rect x="9" y="3" width="6" height="11" rx="3"/>
                        <path d="M5 11a7 7 0 0 0 14 0"/>
                        <path d="M12 18v3"/>
                        <path d="M9 21h6"/>
                    </svg>
                </button>

                <button type="submit" class="buscador-icono buscador-submit" aria-label="Buscar">
                    <!-- Lupa -->
                    <svg viewBox="0 0 24 24">
                        <circle cx="10.8" cy="10.8" r="6.5"/>
                        <path d="M16 16l5 5"/>
                    </svg>
                </button>
            </form>
        </div>
        <div class="col-3 col-sm-1 banner2a div-ocultar">
            <div class="row">
                <!--<div class="col-sm-4" style="padding:9px 0px 0px 5px">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000000" stroke-linejoin="round" stroke-width="2" d="M12 11a5 5 0 100-10 5 5 0 000 10zM1 22.91C1.21 17.92 6.029 14 12 14s10.79 4.01 11 9H1v-.09z"></path></svg>
                </div>
                <div class="col-sm-8" style="padding:4px 0px 0px 2px">
                    <div class="tol-301" style="width:60px">Inicia sesion</div>
                </div>-->
            </div>
        </div>
        <div class="col-3 col-sm-1 banner2a div-ocultar" style="padding-left:0px!important;padding-right:0px!important;">
            <!--<img src="assets/img/tiendita.png" style="height:40px">-->
            <div class="row">
                <div class="col-sm-3" style="padding:9px 0px 0px 5px">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 64 64"
                        width="24"
                        height="24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <path d="M10 21 L14 12 Q15 10 18 10 H46 Q49 10 50 12 L54 21" />
                        <path d="M10 21 H54 V25 Q54 27 52 27 H12 Q10 27 10 25 Z" />
                        <path d="M13 27 V51" />
                        <path d="M51 27 V51" />
                        <path d="M13 51 H51" />
                        <path d="M32 27 V51" />
                        <path d="M29 38 H35" />
                        <path d="M10 51 H54" />
                    </svg>
                </div>
                <div class="col-sm-9" style="padding:4px 0px 0px 2px">
                    <div class="tol-301">Encuentra tu tienda</div>
                </div>
            </div>
        </div>
        <div class="col-3 col-sm-1 banner2a div-ocultar text-center">
            <span class="tol-300">
                <i class="bi bi-bag-heart-fill me-2" style="font-size: 1.5rem;"></i>
            </span>
        </div>
        <div class="col-4 col-sm-2 col-md-2 col-lg-1 banner2a" style="padding-top: 0px;">
            <a href="<?php echo base_url('carrito'); ?>" class="btn btn-outline-light">
                <?php $count = isset($carrito_count) ? (int)$carrito_count : 0; ?>
                <span class="position-relative d-inline-block">
                    <img src="<?php echo base_url('assets/img/carrito.svg'); ?>" style="height:45px;" alt="Carrito de compras">
                    <!-- Contador del carrito: visible en la parte superior derecha del icono.
                         Se pinta con PHP en cada carga de página y conserva el id #carrito-badge
                         para que assets/js/carrito.js pueda actualizarlo por AJAX
                         (mismo contrato que layouts/header.php). -->
                    <span id="carrito-badge"
                          class="badge rounded-pill position-absolute top-0 start-100 translate-middle"
                          style="background-color:#E91E63;"><?php echo $count; ?></span>
                </span>
            </a>
        </div>

    </div>

</div>    


<!-- MENU LATERAL PLEGABLE (blanco semitransparente, lado izquierdo) -->
<div id="menu-lateral-backdrop" class="sidebar-backdrop"></div>
<aside id="menu-lateral" class="sidebar-menu" aria-label="Menú de navegación">
    <div class="sidebar-cabeza">
        <span class="sidebar-titulo">
            <i class="bi bi-grid-fill"></i> Menú
        </span>
        <button type="button" id="menu-lateral-cerrar" class="sidebar-cerrar" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a class="sidebar-opcion" href="<?php echo base_url(); ?>">
                <i class="bi bi-house-heart-fill"></i> Inicio
            </a>
        </li>
        <li>
            <a class="sidebar-opcion" href="<?php echo base_url('tienda'); ?>">
                <i class="bi bi-bag-heart-fill"></i> Catálogo
            </a>
        </li>
        <li>
            <a class="sidebar-opcion" href="<?php echo base_url('carrito'); ?>">
                <i class="bi bi-basket2-fill"></i> Mi carrito
            </a>
        </li>
        <li>
            <a class="sidebar-opcion" href="<?php echo base_url('quienes-somos'); ?>">
                <i class="bi bi-people-fill"></i> Quiénes somos
            </a>
        </li>
        <li>
            <a class="sidebar-opcion" href="<?php echo base_url('libro-reclamaciones'); ?>">
                <i class="bi bi-journal-text"></i> Libro de Reclamaciones
            </a>
        </li>
    </ul>
    <div class="sidebar-pie">
        <?php echo $this->config->item('tienda_slogan'); ?>
    </div>
</aside>

<!-- Mensajes flash -->
<div class="container mt-3 div-ocultar">
    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
</div>

<!-- Contenido principal -->
<main class="py-4">
<script>
    // ===================================================
    //  MENU LATERAL PLEGABLE
    // ===================================================
    (function () {
        var btn     = document.getElementById('btn-menu-lateral');
        var menu    = document.getElementById('menu-lateral');
        var backdrop = document.getElementById('menu-lateral-backdrop');
        var cerrar  = document.getElementById('menu-lateral-cerrar');
        if (!btn || !menu || !backdrop) return;

        function abrirMenu(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            menu.classList.add('abierto');
            backdrop.classList.add('show');
            btn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function cerrarMenu() {
            menu.classList.remove('abierto');
            backdrop.classList.remove('show');
            btn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        // Abrir al hacer click en la imagen de "rallas"
        btn.addEventListener('click', abrirMenu);
        // Soporte de teclado (Enter / Espacio) al estar enfocada
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                abrirMenu(e);
            }
        });

        // Cerrar al hacer click fuera (fondo oscuro), en la X o con Escape
        backdrop.addEventListener('click', cerrarMenu);
        if (cerrar) cerrar.addEventListener('click', cerrarMenu);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarMenu();
        });

        // Cerrar el panel al elegir una opción
        var enlaces = menu.querySelectorAll('a');
        for (var i = 0; i < enlaces.length; i++) {
            enlaces[i].addEventListener('click', cerrarMenu);
        }
    })();
</script>
