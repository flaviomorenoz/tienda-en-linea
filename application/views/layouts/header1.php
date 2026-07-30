<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversaciones IA - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
<?=menu_principal($this->config->item('tienda_nombre'))?>
