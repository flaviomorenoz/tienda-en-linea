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

<!-- Contenido principal -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Conversaciones del Asistente IA</h4>
            <p class="text-muted small mb-0">Historial de conversaciones por WhatsApp (solo lectura)</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table id="tabla-tia-conversaciones" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th class="text-center">Mensajes</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal conversación -->
<div class="modal fade" id="modalConversacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalConversacionLabel">Conversación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="conversacion-loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
                    Cargando...
                </div>
                <div id="conversacion-error" class="alert alert-danger m-3 d-none">
                    No se pudo cargar la conversación.
                </div>
                <div id="conversacion-mensajes" class="chats-mensajes d-none"></div>
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
const ajaxUrl     = <?php echo json_encode(base_url('tia_conversa/json')); ?>;
const mensajesUrl = <?php echo json_encode(base_url('tia_conversa/mensajes/')); ?>;

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

const table = $('#tabla-tia-conversaciones').DataTable({
    ajax: { url: ajaxUrl, type: 'GET', dataSrc: 'data' },
    processing: true,
    columns: [
        { data: 'id', className: 'text-center' },
        { data: 'contacto' },
        {
            data: 'estado', className: 'text-center',
            render: function(d, type) {
                if (type !== 'display') return d;
                return '<span class="badge badge-' + d + '">' + escHtml(d) + '</span>';
            }
        },
        {
            data: 'fecha_inicio', className: 'small text-muted',
            render: function(d, type) { return type === 'display' ? fmtFecha(d) : d; }
        },
        {
            data: 'fecha_fin', className: 'small text-muted',
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
                return '<a href="#" onclick="ver_conversacion(' + row.id + ', \'' + escHtml(row.contacto) + '\');return false;" title="Ver conversación">' +
                       '<i class="bi bi-chat-square-text" style="font-size:18px"></i></a>';
            }
        }
    ],
    dom:
        "<'row mb-3'<'col-md-6'><'col-md-6 d-flex justify-content-end align-items-center'f>>" +
        "<'row'<'col-12'tr>>" +
        "<'row mt-2'<'col-md-5 small text-muted'i><'col-md-7 d-flex justify-content-end'p>>",
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
    order: [[3, 'desc']],
    pageLength: 25
});

function ver_conversacion(id, contacto) {
    document.getElementById('modalConversacionLabel').textContent = 'Conversación #' + id + ' · ' + contacto;
    document.getElementById('conversacion-loading').classList.remove('d-none');
    document.getElementById('conversacion-error').classList.add('d-none');
    document.getElementById('conversacion-mensajes').classList.add('d-none');
    document.getElementById('conversacion-mensajes').innerHTML = '';

    var modal = new bootstrap.Modal(document.getElementById('modalConversacion'));
    modal.show();

    fetch(mensajesUrl + encodeURIComponent(id))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('conversacion-loading').classList.add('d-none');

            if (!data || !data.ok || !data.mensajes || data.mensajes.length === 0) {
                document.getElementById('conversacion-error').classList.remove('d-none');
                return;
            }

            var cont = document.getElementById('conversacion-mensajes');
            data.mensajes.forEach(function(m) {
                var div = document.createElement('div');
                div.className = 'msg msg-' + m.rol;
                div.textContent = m.texto;
                cont.appendChild(div);
            });
            cont.classList.remove('d-none');
        })
        .catch(function() {
            document.getElementById('conversacion-loading').classList.add('d-none');
            document.getElementById('conversacion-error').classList.remove('d-none');
        });
}
</script>

</body>
</html>
