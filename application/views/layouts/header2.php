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
    <link href="<?php echo base_url('assets/css/' . $tema_css . '?v=3'); ?>" rel="stylesheet">
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
    .banner2{
        height:75px;
        padding:12px 8px;
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
</style>
<!-- NAVBAR -->
<div class="container" style="display:block!important">
    <div class="row">
        <div class="col-sm-12 banner0">
            <span><?= isset($texto_banner) ? $texto_banner : "" ?></span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-2 banner2">
            <a class="navbar-brand fw-bold tol-logo" href="<?php echo base_url(); ?>">
                <i class="bi bi-bag-heart-fill me-2"></i>
                <?php echo $this->config->item('tienda_nombre'); ?>
            </a>
        </div>
        <div class="col-sm-6 banner2" id="busqueda">
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
        <div class="col-sm-1 banner2 div-ocultar">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="#000000" stroke-linejoin="round" stroke-width="2" d="M12 11a5 5 0 100-10 5 5 0 000 10zM1 22.91C1.21 17.92 6.029 14 12 14s10.79 4.01 11 9H1v-.09z"></path></svg>
            <span class="tol-301">Hola, inicia sesion</span>
        </div>
        <div class="col-sm-1 banner2 div-ocultar" style="padding-left:0px!important;padding-right:0px!important;">
            <!--<img src="assets/img/tiendita.png" style="height:40px">-->
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
            <span class="tol-301">Encuentra tu tienda</span>
        </div>
        <div class="col-sm-1 banner2 div-ocultar">
            <span class="tol-300">Puntos<br> Bonus</span>
        </div>
        <div class="col-sm-1 banner2">
            <a href="<?php echo base_url('carrito'); ?>" class="btn btn-outline-light btn-carrito position-relative">
                <img src="assets/img/carrito.svg" style="height:45px;">
                <?php $count = isset($carrito_count) ? (int)$carrito_count : 0; ?>
                <?php if ($count > 0): ?>
                <?php echo $count; ?>
                <?php endif; ?>
            </a>
        </div>

    </div>

</div>    


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
