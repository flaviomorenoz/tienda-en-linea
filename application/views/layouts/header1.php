<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') : 'Admin' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .admin-sidebar { background: #2d3436; min-height: 100vh; width: 240px; position: fixed; left: 0; top: 0; z-index: 100; }
        .main-content { margin-left: 240px; padding: 2rem; }
        .badge-abierta { background: #198754; }
        .badge-cerrada { background: #6c757d; }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .main-content { margin-left: 0; }
        }

        .chats-mensajes { flex-grow: 1; overflow-y: auto; padding: 1rem; max-height: 55vh; }
        .msg { max-width: 70%; padding: .5rem .8rem; border-radius: 12px; margin-bottom: .6rem; font-size: .9rem; white-space: pre-wrap; word-break: break-word; }
        .msg-user { background: #e9ecef; margin-right: auto; }
        .msg-assistant { background: #25d366; color: #fff; margin-left: auto; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?=menu_principal($this->config->item('tienda_nombre'), $admin_nombre ?? NULL)?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><?= $titulo ?></h4>
            <?php if (isset($subtitulo)): ?>
            <p class="text-muted small mb-0"><?= $subtitulo ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
