<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de chats - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .admin-sidebar { background: #2d3436; min-height: 100vh; width: 240px; position: fixed; left: 0; top: 0; z-index: 100; }
        .main-content { margin-left: 240px; padding: 2rem; }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .main-content { margin-left: 0; }
        }

        .chats-mensajes { flex-grow: 1; overflow-y: auto; padding: 1rem; max-height: 55vh; }
        .msg { max-width: 70%; padding: .5rem .8rem; border-radius: 12px; margin-bottom: .6rem; font-size: .9rem; white-space: pre-wrap; word-break: break-word; }
        .msg-cliente { background: #e9ecef; margin-right: auto; }
        .msg-vendedor { background: #2d3436; color: #fff; margin-left: auto; }
        .msg-ia { background: #eef6ff; margin-right: auto; border: 1px dashed #90caf9; }
        .msg-sistema { background: transparent; color: #adb5bd; font-size: .78rem; text-align: center; margin: .5rem auto; max-width: 100%; }
        .msg-img { display: block; max-width: 100%; border-radius: 8px; cursor: zoom-in; margin-bottom: .3rem; }
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
        <a href="<?php echo base_url('admin/chats'); ?>" class="d-flex align-items-center gap-2 text-white-50 text-decoration-none py-2 px-3 rounded mb-1">
            <i class="bi bi-chat-dots-fill"></i> Chats
        </a>
        <a href="<?php echo base_url('admin/chats/historial'); ?>" class="d-flex align-items-center gap-2 text-white text-decoration-none py-2 px-3 rounded mb-1 bg-white bg-opacity-10">
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
        <a href="<?php echo base_url('admin/logout'); ?>" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
        </a>
    </div>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Historial de mis chats</h4>
            <p class="text-muted small mb-0">Conversaciones cerradas del último mes</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table id="tabla-historial" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Motivo</th>
                        <th>Iniciado</th>
                        <th>Cerrado</th>
                        <th class="text-center">Mensajes</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal transcripción -->
<div class="modal fade" id="modalTranscripcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalTranscripcionLabel">Conversación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="transcripcion-loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
                    Cargando...
                </div>
                <div id="transcripcion-error" class="alert alert-danger m-3 d-none">
                    No se pudo cargar la conversación.
                </div>
                <div id="chats-mensajes" class="chats-mensajes d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
const ajaxUrl     = <?php echo json_encode(base_url('admin/chats/historial_json')); ?>;
const mensajesUrl = <?php echo json_encode(base_url('admin/chats/mensajes/')); ?>;

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function fmtFecha(str) {
    if (!str) return '—';
    var d = new Date(str.replace(' ', 'T'));
    if (isNaN(d.getTime())) return str;
    return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const table = $('#tabla-historial').DataTable({
    ajax: { url: ajaxUrl, type: 'GET', dataSrc: 'data' },
    processing: true,
    columns: [
        { data: 'nombre_cliente', render: function(d) { return escHtml(d || 'Cliente sin nombre'); } },
        { data: 'celular_cliente', render: function(d) { return escHtml(d || '—'); } },
        { data: 'motivo', render: function(d) { return escHtml(d || '—'); } },
        {
            data: 'iniciado_en', className: 'small text-muted',
            render: function(d, type) { return type === 'display' ? fmtFecha(d) : d; }
        },
        {
            data: 'cerrado_en', className: 'small text-muted',
            render: function(d, type) { return type === 'display' ? fmtFecha(d) : d; }
        },
        { data: 'total_mensajes', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(d, type, row) {
                if (type !== 'display') return '';
                return '<a href="#" onclick="ver_transcripcion(' + row.id + ');return false;" title="Ver conversación">' +
                       '<i class="bi bi-chat-square-text" style="font-size:18px"></i></a>';
            }
        }
    ],
    dom:
        "<'row mb-3'<'col-md-6'><'col-md-6 d-flex justify-content-end align-items-center'f>>" +
        "<'row'<'col-12'tr>>" +
        "<'row mt-2'<'col-md-5 small text-muted'i><'col-md-7 d-flex justify-content-end'p>>",
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
    order: [[4, 'desc']],
    pageLength: 25
});

function ver_transcripcion(id_conversacion) {
    document.getElementById('modalTranscripcionLabel').textContent = 'Conversación #' + id_conversacion;
    document.getElementById('transcripcion-loading').classList.remove('d-none');
    document.getElementById('transcripcion-error').classList.add('d-none');
    document.getElementById('chats-mensajes').classList.add('d-none');
    document.getElementById('chats-mensajes').innerHTML = '';

    var modal = new bootstrap.Modal(document.getElementById('modalTranscripcion'));
    modal.show();

    fetch(mensajesUrl + id_conversacion)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('transcripcion-loading').classList.add('d-none');

            if (!data || !data.ok || !data.mensajes || data.mensajes.length === 0) {
                document.getElementById('transcripcion-error').classList.remove('d-none');
                return;
            }

            var cont = document.getElementById('chats-mensajes');
            data.mensajes.forEach(function(m) {
                var div = document.createElement('div');
                div.className = 'msg msg-' + m.emisor;
                if (m.imagen) {
                    var img = document.createElement('img');
                    img.src = m.imagen;
                    img.className = 'msg-img';
                    img.alt = 'Imagen enviada por el cliente';
                    div.appendChild(img);
                }
                if (m.texto) {
                    var texto = document.createElement('div');
                    texto.textContent = m.texto;
                    div.appendChild(texto);
                }
                cont.appendChild(div);
            });
            cont.classList.remove('d-none');
        })
        .catch(function() {
            document.getElementById('transcripcion-loading').classList.add('d-none');
            document.getElementById('transcripcion-error').classList.remove('d-none');
        });
}
</script>

</body>
</html>
