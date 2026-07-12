<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats con clientes - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .admin-sidebar { background: #2d3436; min-height: 100vh; width: 240px; position: fixed; left: 0; top: 0; z-index: 100; }
        .main-content { margin-left: 240px; padding: 2rem; height: 100vh; }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .main-content { margin-left: 0; }
        }

        .chats-layout { height: calc(100vh - 8rem); display: flex; gap: 1rem; }
        .chats-listas { width: 320px; flex-shrink: 0; overflow-y: auto; }
        .chats-ventana { flex-grow: 1; display: flex; flex-direction: column; background: #fff; border-radius: 8px; }

        .lista-titulo { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; font-weight: 600; margin: 1rem 0 .5rem; }
        .conv-item { cursor: pointer; border-radius: 8px; padding: .6rem .75rem; margin-bottom: .4rem; background: #fff; border: 1px solid #eee; }
        .conv-item:hover { border-color: #adb5bd; }
        .conv-item.activo { border-color: #2d3436; background: #f1f1f1; }
        .conv-item .nombre { font-weight: 600; font-size: .9rem; }
        .conv-item .motivo { font-size: .78rem; color: #6c757d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .chats-mensajes { flex-grow: 1; overflow-y: auto; padding: 1rem; }
        .chats-mensajes .vacio { color: #adb5bd; text-align: center; margin-top: 2rem; }
        .msg { max-width: 70%; padding: .5rem .8rem; border-radius: 12px; margin-bottom: .6rem; font-size: .9rem; white-space: pre-wrap; word-break: break-word; }
        .msg-cliente { background: #e9ecef; margin-right: auto; }
        .msg-vendedor { background: #2d3436; color: #fff; margin-left: auto; }
        .msg-ia { background: #eef6ff; margin-right: auto; border: 1px dashed #90caf9; }
        .msg-sistema { background: transparent; color: #adb5bd; font-size: .78rem; text-align: center; margin: .5rem auto; max-width: 100%; }

        .chats-form { border-top: 1px solid #eee; padding: .75rem; display: none; }
        .badge-espera { background: #dc3545; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="admin-sidebar text-white d-flex flex-column py-4 px-3">
    <div class="text-center mb-4">
        <i class="bi bi-bag-heart-fill fs-2 d-block mb-1"></i>
        <span class="fw-bold small"><?php echo $this->config->item('tienda_nombre'); ?></span>
        <div class="text-white-50 x-small">Admin Panel</div>
    </div>
    <nav class="flex-grow-1">
        <a href="<?php echo base_url('admin/pedidos'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-list-ul"></i> Pedidos
        </a>
        <a href="<?php echo base_url('admin/chats'); ?>" class="d-flex align-items-center gap-2 text-white text-decoration-none py-2 px-3 rounded mb-1 bg-white bg-opacity-10">
            <i class="bi bi-chat-dots-fill"></i> Chats
            <span id="badge-espera" class="badge badge-espera rounded-pill ms-auto d-none">0</span>
        </a>
        <a href="<?php echo base_url('admin/chats/historial'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-clock-history"></i> Historial
        </a>
        <a href="<?php echo base_url(); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1" target="_blank">
            <i class="bi bi-shop"></i> Ver tienda
        </a>
    </nav>
    <div class="border-top border-secondary pt-3 mt-3">
        <div class="text-white-50 small mb-2">
            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($admin_nombre ?? 'Vendedora'); ?>
        </div>
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" role="switch" id="switch-disponible"
                   <?php echo ($vendedora->estado === 'disponible') ? 'checked' : ''; ?>>
            <label class="form-check-label text-white-50 small" for="switch-disponible" id="label-disponible">
                <?php echo ($vendedora->estado === 'disponible') ? 'Disponible' : 'Ocupada'; ?>
            </label>
        </div>
        <a href="<?php echo base_url('admin/logout'); ?>" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
        </a>
    </div>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Chats con clientes</h4>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($this->session->flashdata('error')); ?></div>
    <?php endif; ?>

    <div class="chats-layout">
        <div class="chats-listas">
            <div class="lista-titulo">En espera</div>
            <div id="lista-espera"><p class="text-muted small">No hay clientes esperando.</p></div>

            <div class="lista-titulo">Mis conversaciones</div>
            <div id="lista-mias"><p class="text-muted small">Aún no tienes chats asignados.</p></div>
        </div>

        <div class="chats-ventana">
            <div id="chats-mensajes" class="chats-mensajes">
                <p class="vacio">Selecciona un cliente de la lista para ver la conversación.</p>
            </div>
            <div id="chats-form" class="chats-form">
                <form id="form-responder" class="d-flex gap-2 mb-2">
                    <input type="text" id="input-respuesta" class="form-control" placeholder="Escribe tu respuesta..." autocomplete="off">
                    <button type="submit" class="btn btn-dark"><i class="bi bi-send-fill"></i></button>
                </form>
                <button id="btn-cerrar-chat" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Cerrar conversación
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.VENDEDORA_URLS = {
        enEspera:        <?php echo json_encode(base_url('admin/chats/en_espera')); ?>,
        misConversaciones: <?php echo json_encode(base_url('admin/chats/mis_conversaciones')); ?>,
        tomar:           <?php echo json_encode(base_url('admin/chats/tomar/')); ?>,
        mensajes:        <?php echo json_encode(base_url('admin/chats/mensajes/')); ?>,
        responder:       <?php echo json_encode(base_url('admin/chats/responder/')); ?>,
        cerrar:          <?php echo json_encode(base_url('admin/chats/cerrar/')); ?>,
        disponibilidad:  <?php echo json_encode(base_url('admin/chats/disponibilidad')); ?>
    };
</script>
<script src="<?php echo base_url('assets/js/chat_vendedora.js'); ?>"></script>

</body>
</html>
