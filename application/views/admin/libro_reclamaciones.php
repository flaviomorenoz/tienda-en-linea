<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa;}
        .admin-sidebar { background: #2d3436; min-height: 100vh; width: 240px; position: fixed; left: 0; top: 0; z-index: 100; }
        .main-content { margin-left: 240px; padding: 2rem; }
        .badge-ESTADO { min-width: 84px; }
        .est-RECIBIDO   { background: #6c757d; color: #fff; }
        .est-EN_PROCESO { background: #ffc107; color: #000; }
        .est-RESPONDIDO { background: #198754; color: #fff; }
        .est-ARCHIVADO  { background: #343a40; color: #fff; }
        .tip-RECLAMO    { background: #dc3545; color: #fff; }
        .tip-QUEJA      { background: #0dcaf0; color: #000; }
        .dt-buttons { display: flex; flex-wrap: wrap; gap: 4px; }
        .detalle-tabla th { width: 32%; }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>

<!-- Sidebar -->
<?=menu_principal($this->config->item('tienda_nombre'))?>

<!-- Contenido principal -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Libro de Reclamaciones</h4>
            <p class="text-muted small mb-0" id="reclamos-count">Cargando...</p>
        </div>
        <a href="<?php echo base_url(); ?>" class="btn btn-outline-dark btn-sm" target="_blank">
            <i class="bi bi-shop me-1"></i>Ver tienda
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        <i class="bi bi-check-circle me-1"></i>
        <?php echo htmlspecialchars($this->session->flashdata('success')); ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2 small">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php echo htmlspecialchars($this->session->flashdata('error')); ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="small text-muted mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Ley N° 29571 (arts. 150-152) · D.S. N° 011-2011-PCM · D.S. N° 004-2024-PCM — Atender en un plazo máximo de
        <strong>15 días hábiles</strong>.
    </div>

    <!-- Filtros rápidos -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <button class="btn btn-sm btn-dark rounded-pill filter-btn" data-filtro="todos">Todos</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filtro="RECIBIDO">Recibidos</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filtro="EN_PROCESO">En proceso</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filtro="RESPONDIDO">Respondidos</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filtro="ARCHIVADO">Archivados</button>
    </div>

    <!-- Tabla de hojas -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table id="tabla-reclamos" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Documento</th>
                        <th>Producto / Servicio</th>
                        <th>Tipo</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal detalle de la hoja -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalDetalleLabel">
                    <i class="bi bi-journal-text me-1"></i>Hoja de Reclamación
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4" id="detalle-loading">
                    <div class="spinner-border text-secondary" role="status"></div>
                </div>
                <div class="alert alert-danger d-none" id="detalle-error"></div>
                <div id="detalle-contenido" class="d-none">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <strong id="detalle-codigo" class="fs-5"></strong>
                            <div class="text-muted small" id="detalle-fecha"></div>
                        </div>
                        <div>
                            <span class="badge" id="detalle-tipo"></span>
                            <span class="badge" id="detalle-estado"></span>
                        </div>
                    </div>
                    <table class="table table-sm detalle-tabla">
                        <tbody>
                            <tr><th>Consumidor</th><td id="detalle-nombres"></td></tr>
                            <tr><th>Documento</th><td id="detalle-documento"></td></tr>
                            <tr><th>Domicilio</th><td id="detalle-domicilio"></td></tr>
                            <tr><th>Teléfono</th><td id="detalle-telefono"></td></tr>
                            <tr><th>Correo</th><td id="detalle-email"></td></tr>
                            <tr><th>Ubicación</th><td id="detalle-ubicacion"></td></tr>
                            <tr><th>Producto / servicio</th><td id="detalle-producto"></td></tr>
                            <tr><th>N° de pedido</th><td id="detalle-pedido"></td></tr>
                            <tr><th>Monto reclamado</th><td id="detalle-monto"></td></tr>
                            <tr><th>Detalle</th><td id="detalle-texto" style="white-space:pre-line;"></td></tr>
                            <tr id="fila-respuesta" class="d-none">
                                <th>Respuesta del proveedor</th>
                                <td>
                                    <div class="p-2 bg-light rounded small" style="white-space:pre-line;" id="detalle-respuesta"></div>
                                    <small class="text-muted" id="detalle-fecha-respuesta"></small>
                                </td>
                            </tr>
                            <tr><th>IP de origen</th><td id="detalle-ip" class="small text-muted"></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal responder hoja -->
<div class="modal fade" id="modalResponder" tabindex="-1" aria-labelledby="modalResponderLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalResponderLabel">
                    <i class="bi bi-chat-left-text me-1"></i>Atender / Responder hoja
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="form-responder" novalidate>
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                       value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="modal-body">
                    <p class="small text-muted mb-3" id="responder-info"></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" class="form-select" id="select-estado">
                            <option value="RECIBIDO">Recibido</option>
                            <option value="EN_PROCESO">En proceso</option>
                            <option value="RESPONDIDO">Respondido</option>
                            <option value="ARCHIVADO">Archivado</option>
                        </select>
                        <div class="form-text">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Al marcar como <strong>Respondido</strong> la respuesta se hará visible para el cliente en la consulta pública.
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Respuesta del proveedor</label>
                        <textarea name="respuesta" id="textarea-respuesta" rows="5" maxlength="3000"
                                  class="form-control" placeholder="Redacte la respuesta que recibirá el consumidor..."></textarea>
                        <div class="d-flex justify-content-between">
                            <small class="text-danger d-none" id="respuesta-error">La respuesta es obligatoria al marcar como "Respondido".</small>
                            <small class="text-muted ms-auto" id="contador-respuesta">0/3000</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark btn-sm">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

<script>
const ajaxUrl    = <?php echo json_encode(base_url('admin/libro-reclamaciones/json')); ?>;
const detalleUrl = <?php echo json_encode(base_url('admin/libro-reclamaciones/detalle/')); ?>;
const responderUrl = <?php echo json_encode(base_url('admin/libro-reclamaciones/responder/')); ?>;
const moneda     = <?php echo json_encode($this->config->item('moneda_simbolo')); ?>;

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function badgeEstado(estado) {
    return '<span class="badge badge-ESTADO est-' + estado + '">' + escHtml(estado.replace(/_/g, ' ')) + '</span>';
}

const table = $('#tabla-reclamos').DataTable({
    ajax: { url: ajaxUrl, type: 'GET', dataSrc: 'data' },
    processing: true,
    columns: [
        {
            data: 'codigo',
            render: function(d, type) {
                return type === 'display' ? '<strong>' + escHtml(d) + '</strong>' : d;
            }
        },
        {
            data: 'fecha',
            className: 'small text-muted',
            render: function(d, type, row) {
                return type !== 'display' ? row.fecha_raw : d;
            }
        },
        {
            data: 'nombres',
            render: function(d, type, row) {
                if (type !== 'display') return d;
                return '<div class="fw-semibold">' + escHtml(d) + '</div>' +
                       '<div class="text-muted small">' + escHtml(row.email) + '</div>';
            }
        },
        {
            data: null,
            className: 'small',
            render: function(d, type, row) {
                return '<div>' + escHtml(row.tipo_documento) + ': ' + escHtml(row.numero_documento) + '</div>' +
                       (row.telefono ? '<div class="text-muted">' + escHtml(row.telefono) + '</div>' : '');
            }
        },
        {
            data: 'producto_servicio',
            render: function(d, type, row) {
                if (type !== 'display') return d;
                return escHtml(d) + (row.numero_pedido ? ' <span class="text-muted small">(Pedido: ' + escHtml(row.numero_pedido) + ')</span>' : '');
            }
        },
        {
            data: 'tipo',
            className: 'text-center',
            render: function(d, type) {
                return type === 'display'
                    ? '<span class="badge tip-' + d + '">' + escHtml(d) + '</span>'
                    : d;
            }
        },
        {
            data: 'monto',
            className: 'text-end',
            render: function(d, type) {
                if (d === null || d === undefined) return type === 'display' ? '—' : -1;
                return type === 'display' ? moneda + ' ' + parseFloat(d).toFixed(2) : d;
            }
        },
        {
            data: 'estado',
            className: 'text-center',
            render: function(d, type) {
                return type === 'display' ? badgeEstado(d) : d;
            }
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(d, type, row) {
                if (type !== 'display') return '';
                return '<button type="button" class="btn btn-sm btn-outline-secondary me-1 btn-ver" data-id="' + row.id + '" title="Ver detalle">' +
                       '<i class="bi bi-eye"></i></button>' +
                       '<button type="button" class="btn btn-sm btn-outline-primary btn-responder" data-id="' + row.id + '" data-codigo="' + escHtml(row.codigo) + '" data-estado="' + row.estado + '" title="Atender / responder">' +
                       '<i class="bi bi-chat-left-text"></i></button>';
            }
        }
    ],
    dom:
        "<'row mb-3'<'col-md-6'B><'col-md-6 d-flex justify-content-end align-items-center'f>>" +
        "<'row'<'col-12'tr>>" +
        "<'row mt-2'<'col-md-5 small text-muted'i><'col-md-7 d-flex justify-content-end'p>>",
    buttons: [
        {
            extend: 'excel',
            text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel',
            className: 'btn btn-success btn-sm',
            exportOptions: { columns: [0,1,2,3,4,5,6,7] }
        },
        {
            extend: 'pdf',
            text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
            className: 'btn btn-danger btn-sm',
            exportOptions: { columns: [0,1,2,3,4,5,6,7] }
        },
        {
            extend: 'csv',
            text: '<i class="bi bi-filetype-csv me-1"></i>CSV',
            className: 'btn btn-secondary btn-sm',
            exportOptions: { columns: [0,1,2,3,4,5,6,7] }
        },
        {
            extend: 'print',
            text: '<i class="bi bi-printer me-1"></i>Imprimir',
            className: 'btn btn-info btn-sm text-white',
            exportOptions: { columns: [0,1,2,3,4,5,6,7] }
        }
    ],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
    order: [[0, 'desc']],
    pageLength: 25,
    scrollX: true
});

table.on('draw', function() {
    const info = table.page.info();
    document.getElementById('reclamos-count').textContent =
        info.recordsDisplay + ' hoja(s) de reclamación encontradas';
});

// Filtros rápidos (columna 7 = estado)
document.querySelectorAll('.filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const filtro = this.dataset.filtro;
        if (filtro === 'todos') {
            table.column(7).search('').draw();
        } else {
            table.column(7).search('^' + filtro + '$', true, false).draw();
        }
        document.querySelectorAll('.filter-btn').forEach(function(b) {
            b.classList.remove('btn-dark');
            b.classList.add('btn-outline-secondary');
        });
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-dark');
    });
});

// Ver detalle de una hoja
function verDetalle(id) {
    document.getElementById('modalDetalleLabel').textContent = 'Hoja de Reclamación';
    document.getElementById('detalle-loading').classList.remove('d-none');
    document.getElementById('detalle-error').classList.add('d-none');
    document.getElementById('detalle-contenido').classList.add('d-none');

    var modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    modal.show();

    fetch(detalleUrl + id)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            document.getElementById('detalle-loading').classList.add('d-none');
            if (!d.ok) {
                document.getElementById('detalle-error').textContent = d.error || 'Error al cargar el detalle.';
                document.getElementById('detalle-error').classList.remove('d-none');
                return;
            }
            document.getElementById('detalle-codigo').textContent = d.codigo;
            document.getElementById('detalle-fecha').textContent = 'Registrada el ' + d.fecha;
            document.getElementById('detalle-tipo').textContent = d.tipo;
            document.getElementById('detalle-tipo').className = 'badge tip-' + d.tipo;
            document.getElementById('detalle-estado').textContent = d.estado.replace(/_/g, ' ');
            document.getElementById('detalle-estado').className = 'badge est-' + d.estado;
            document.getElementById('detalle-nombres').textContent = d.nombres;
            document.getElementById('detalle-documento').textContent = d.tipo_documento + ' ' + d.numero_documento;
            document.getElementById('detalle-domicilio').textContent = d.domicilio || '—';
            document.getElementById('detalle-telefono').textContent = d.telefono || '—';
            document.getElementById('detalle-email').textContent = d.email;
            document.getElementById('detalle-ubicacion').textContent = [d.departamento, d.provincia, d.distrito].filter(Boolean).join(' / ') || '—';
            document.getElementById('detalle-producto').textContent = d.producto_servicio;
            document.getElementById('detalle-pedido').textContent = d.numero_pedido || '—';
            document.getElementById('detalle-monto').textContent = d.monto !== null && d.monto !== undefined ? moneda + ' ' + parseFloat(d.monto).toFixed(2) : '—';
            document.getElementById('detalle-texto').textContent = d.detalle;
            document.getElementById('detalle-ip').textContent = d.ip || '—';

            var filaRespuesta = document.getElementById('fila-respuesta');
            if (d.respuesta) {
                document.getElementById('detalle-respuesta').textContent = d.respuesta;
                document.getElementById('detalle-fecha-respuesta').textContent = d.fecha_respuesta ? 'Respondida el ' + d.fecha_respuesta : '';
                filaRespuesta.classList.remove('d-none');
            } else {
                filaRespuesta.classList.add('d-none');
            }

            document.getElementById('detalle-contenido').classList.remove('d-none');
        })
        .catch(function() {
            document.getElementById('detalle-loading').classList.add('d-none');
            document.getElementById('detalle-error').textContent = 'Error de conexión al cargar el detalle.';
            document.getElementById('detalle-error').classList.remove('d-none');
        });
}

// Contador y validación del modal responder
var textareaRespuesta = document.getElementById('textarea-respuesta');
textareaRespuesta.addEventListener('input', function() {
    document.getElementById('contador-respuesta').textContent = textareaRespuesta.value.length + '/3000';
});

document.getElementById('form-responder').addEventListener('submit', function(e) {
    var estadoSel = document.getElementById('select-estado').value;
    if (estadoSel === 'RESPONDIDO' && textareaRespuesta.value.trim() === '') {
        e.preventDefault();
        document.getElementById('respuesta-error').classList.remove('d-none');
        textareaRespuesta.classList.add('is-invalid');
        return;
    }
    document.getElementById('respuesta-error').classList.add('d-none');
});

// Delegación de eventos de la tabla (ver / responder)
document.querySelector('#tabla-reclamos tbody').addEventListener('click', function(e) {
    var verBtn = e.target.closest('.btn-ver');
    if (verBtn) {
        verDetalle(verBtn.getAttribute('data-id'));
        return;
    }
    var resBtn = e.target.closest('.btn-responder');
    if (resBtn) {
        document.getElementById('form-responder').action = responderUrl + resBtn.getAttribute('data-id');
        document.getElementById('responder-info').textContent = 'Hoja ' + resBtn.getAttribute('data-codigo');
        document.getElementById('select-estado').value = resBtn.getAttribute('data-estado');
        document.getElementById('textarea-respuesta').value = '';
        document.getElementById('respuesta-error').classList.add('d-none');
        document.getElementById('contador-respuesta').textContent = '0/3000';
        var modal = new bootstrap.Modal(document.getElementById('modalResponder'));
        modal.show();
    }
});
</script>






