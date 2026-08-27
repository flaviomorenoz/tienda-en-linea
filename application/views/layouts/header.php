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

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="navbar-tienda">
    <div class="container">
        <!-- Logo / Nombre tienda -->
        <a class="navbar-brand fw-bold" href="<?php echo base_url(); ?>">
            <i class="bi bi-bag-heart-fill me-2"></i>
            <?php echo $this->config->item('tienda_nombre'); ?>
        </a>

        <!-- Toggler móvil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Categorías -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (!isset($categoria_activa) || !$categoria_activa) ? 'active' : ''; ?>"
                       href="<?php echo base_url(); ?>">Todos</a>
                </li>
                <?php
                $CI =& get_instance();
                if (!isset($CI->Producto_model)) { $CI->load->model('Producto_model'); }
                $cats = $CI->Producto_model->get_categorias();
                foreach ($cats as $cat):
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($categoria_activa) && $categoria_activa == $cat) ? 'active' : ''; ?>"
                       href="<?php echo base_url('tienda/categoria/' . urlencode($cat)); ?>">
                        <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('quienes-somos'); ?>">Quiénes somos</a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Carrito -->
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo base_url('carrito'); ?>" class="btn btn-outline-light btn-carrito position-relative">
                    <i class="bi bi-cart3 fs-5"></i>
                    <?php $count = isset($carrito_count) ? (int)$carrito_count : 0; ?>
                    <?php if ($count > 0): ?>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" id="carrito-badge">
                        <?php echo $count; ?>
                    </span>
                    <?php else: ?>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle d-none" id="carrito-badge">0</span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Mensajes flash -->
<div class="container mt-3">
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
