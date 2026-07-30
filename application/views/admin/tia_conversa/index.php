<!-- Contenido principal -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Conversaciones del Asistente IA</h4>
            <p class="text-muted small mb-0">Historial de conversaciones por WhatsApp</p>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        <i class="bi bi-check-circle me-1"></i>
        <?php echo htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2 small">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php echo htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

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

<!-- Form oculto para cerrar una conversación -->
<form action="" method="POST" id="form-cerrar" class="d-none">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
const ajaxUrl     = <?php echo json_encode(base_url('tia_conversa/json')); ?>;
const mensajesUrl = <?php echo json_encode(base_url('tia_conversa/mensajes/')); ?>;
const cerrarUrl   = <?php echo json_encode(base_url('tia_conversa/cerrar/')); ?>;

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
                var html = '<a href="#" class="me-3" onclick="ver_conversacion(' + row.id + ', \'' + escHtml(row.contacto) + '\');return false;" title="Ver conversación">' +
                           '<i class="bi bi-chat-square-text" style="font-size:18px"></i></a>';
                if (row.estado === 'abierta') {
                    html += '<a href="#" onclick="cerrar_conversacion(' + row.id + ');return false;" title="Marcar como cerrada">' +
                            '<i class="bi bi-x-circle text-danger" style="font-size:18px"></i></a>';
                }
                return html;
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

function cerrar_conversacion(id) {
    if (!confirm('¿Marcar la conversación #' + id + ' como cerrada?')) return;

    var form = document.getElementById('form-cerrar');
    form.action = cerrarUrl + id;
    form.submit();
}

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
