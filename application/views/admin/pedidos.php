<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa;}
        .admin-sidebar { background: #2d3436; min-height: 100vh; width: 240px; position: fixed; left: 0; top: 0; z-index: 100; }
        .main-content { margin-left: 240px; padding: 2rem; }
        .badge-EN_ORIGEN   { background: #dc3545; }
        .badge-EN_TRANSITO { background: #fd7e14; }
        .badge-EN_DESTINO  { background: #198754; }
        .badge-ENTREGADO   { background: #0d6efd; }
        .pago-Pagado    { background: #198754; color: #fff; }
        .pago-Fallido   { background: #dc3545; color: #fff; }
        .pago-Pendiente { background: #ffc107; color: #000; }
        .dt-buttons { display: flex; flex-wrap: wrap; gap: 4px; }
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
            <h4 class="fw-bold mb-0">Gestión de Pedidos</h4>
            <p class="text-muted small mb-0" id="pedidos-count">Cargando...</p>
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

    <!-- Filtros rápidos -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <button class="btn btn-sm btn-dark rounded-pill filter-btn" data-filtro="todos">Todos</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filtro="Pendiente">Pendientes</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filtro="Pagado">Pagados</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill filter-btn" data-filtro="Fallido">Fallidos</button>
    </div>

    <!-- Tabla de pedidos -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table id="tabla-pedidos" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>DNI</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Pago</th>
                        <th class="text-center">Codigos</th>
                        <th class="text-center">Envío</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal ingreso de códigos de envío -->
<div class="modal fade" id="modalCodigos" tabindex="-1" aria-labelledby="modalCodigosLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalCodigosLabel">Códigos de envío</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="form-codigos" novalidate>
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                       value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="modal-body">
                    <p class="small text-muted mb-3" id="modal-codigos-info"></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nro de Orden <span class="text-danger">*</span></label>
                        <input type="text" name="nro_orden" id="input-nro-orden" class="form-control"
                               placeholder="8 a 10 dígitos"
                               inputmode="numeric" pattern="\d{8,10}" minlength="8" maxlength="10" required>
                        <div class="invalid-feedback">Ingrese entre 8 y 10 dígitos numéricos.</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Código de envío <span class="text-danger">*</span></label>
                        <input type="text" name="codigo_transaccion" id="input-codigo" class="form-control"
                               placeholder="4 caracteres (ej: A1B2)"
                               pattern="[A-Za-z0-9]{4}" minlength="4" maxlength="4" required>
                        <div class="invalid-feedback">Ingrese exactamente 4 caracteres alfanuméricos.</div>
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

<!-- Modal detalle del pedido -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalDetalleLabel">Detalle del Pedido</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="detalle-loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
                    Cargando...
                </div>
                <div id="detalle-error" class="alert alert-danger m-3 d-none">
                    No se pudo cargar el detalle del pedido.
                </div>
                <div id="detalle-contenido" class="d-none p-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Talla</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detalle-tbody"></tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end">Total:</td>
                                <td class="text-end" id="detalle-total"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal cambio de estado -->
<div class="modal fade" id="modalEstado" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Actualizar estado</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="form-estado">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                       value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="modal-body">
                    <p class="small text-muted mb-3" id="modal-cliente-info"></p>
                    <label class="form-label fw-semibold">Estado de envío</label>
                    <select name="estado_envio" class="form-select" id="select-estado">
                        <option value="EN_ORIGEN">EN_ORIGEN</option>
                        <option value="EN_TRANSITO">EN_TRANSITO</option>
                        <option value="EN_DESTINO">EN_DESTINO</option>
                        <option value="ENTREGADO">ENTREGADO</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark btn-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ver comprobante de pago (imagen) -->
<div class="modal fade" id="modalImagen" tabindex="-1" aria-labelledby="modalImagenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalImagenLabel">
                    <i class="bi bi-image text-success me-1"></i>Comprobante de pago
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagen-comprobante"
                     src=""
                     alt="Comprobante de pago"
                     class="img-fluid rounded shadow-sm d-inline-block"
                     style="max-height:400px; width:auto; height:auto;"
                     onerror="this.classList.add('d-none');document.getElementById('imagen-error').classList.remove('d-none');">
                <div id="imagen-error" class="alert alert-warning d-none mt-2 mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>No se pudo cargar la imagen del comprobante.
                </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
const moneda  = <?php echo json_encode($this->config->item('moneda_simbolo')); ?>;
const ajaxUrl = <?php echo json_encode(base_url('admin/pedidos_json')); ?>;
const codigosUrl = <?php echo json_encode(base_url('admin/codigos/')); ?>;
const estadoUrl  = <?php echo json_encode(base_url('admin/estado/')); ?>;
const detalleUrl = <?php echo json_encode(base_url('admin/detalle/')); ?>;
const imagenUrl  = <?php echo json_encode(($_SERVER["RUTA_DOMINIO_ERP"] ?? '') . '/uploads/compruebas/'); ?>;

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

const table = $('#tabla-pedidos').DataTable({
    ajax: { url: ajaxUrl, type: 'GET', dataSrc: 'data' },
    processing: true,
    columns: [
        {
            data: 'id',
            render: function(d, type) {
                return type === 'display' ? '<strong>#' + d + '</strong>' : d;
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
                       '<div class="text-muted small">' + escHtml(String(row.direccion_envio).substring(0, 30)) + '...</div>';
            }
        },
        { 
            data: null,     
            className: 'small',
            render: function(d, type, row){
                return '<span class="small">' +
                    '<div><strong>Dni: </strong>' + escHtml(row.dni) + '</div>' +
                    '<div><strong>Celular: </strong>' + escHtml(row.celular) + '</div>' + '</span>';
            }
        },
        {
            data: 'total',
            className: 'text-end fw-bold',
            render: function(d, type) {
                return type === 'display' ? moneda + ' ' + parseFloat(d).toFixed(2) : d;
            }
        },
        {
            data: 'estado_pago',
            className: 'text-center',
            render: function(d, type) {
                return type === 'display'
                    ? '<span class="badge pago-' + d + '">' + escHtml(d) + '</span>'
                    : d;
            }
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(d, type, row) {
                if (type !== 'display') {
                    return row.nro_orden
                        ? 'Nro: ' + row.nro_orden + ' | Cod: ' + row.codigo_transaccion
                        : '';
                }
                if (row.nro_orden) {
                    return '<span class="small">' +
                           '<div><strong>Nro: </strong>' + escHtml(row.nro_orden) + '</div>' +
                           '<div><strong>Código: </strong>' + escHtml(row.codigo_transaccion) + '</div>' +
                           '</span>';
                }
                return '';
                /*return '<button type="button" class="btn btn-sm btn-outline-primary" ' +
                       'data-bs-toggle="modal" data-bs-target="#modalCodigos" ' +
                       'data-id="' + row.id + '" data-nro="" data-cod="">' +
                       '<i class="bi bi-plus-circle me-1"></i>Ingresar</button>';*/
            }
        },
        {
            data: 'estado_envio',
            className: 'text-center',
            render: function(d, type) {
                return type === 'display'
                    ? '<span class="badge badge-' + String(d).replace(/\s/g, '_').toUpperCase() + '">' + escHtml(d) + '</span>'
                    : d;
            }
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(d, type, row) {
                if (type !== 'display') return '';
                let cad0 = ""
                if (row.archivo.length > 0){
                    cad0 = '<a href="#" onclick="ver_imagen(\'' + row.archivo + '\');return false;" title="Ver imagen"><i class="bi bi-camera" style="font-size:18px;color:green"></i></a>';
                }
                let cad1 = ' <a href="#" onclick="ver_detalle(' + row.id + ');return false;" title="Ver detalle"><i class="bi bi-eye" style="font-size:18px"></i></a>';
                let cad2 = ' <a href=\"#\" ' +
                       'data-bs-toggle="modal" data-bs-target="#modalCodigos" ' +
                       'data-id="' + row.id + '" ' +
                       'data-nro="' + escHtml(row.nro_orden || '') + '" ' +
                       'data-cod="' + escHtml(row.codigo_transaccion || '') + '">' +
                       '<i class="bi bi-pencil-square" style="font-size:18px!important"></i></a>';
                
                return cad0 + cad1 + cad2;
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
            exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
        },
        {
            extend: 'pdf',
            text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
            className: 'btn btn-danger btn-sm',
            exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
        },
        {
            extend: 'csv',
            text: '<i class="bi bi-filetype-csv me-1"></i>CSV',
            className: 'btn btn-secondary btn-sm',
            exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
        },
        {
            extend: 'print',
            text: '<i class="bi bi-printer me-1"></i>Imprimir',
            className: 'btn btn-info btn-sm text-white',
            exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
        }
    ],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
    order: [[0, 'desc']],
    pageLength: 25,
    scrollX: true
});

table.on('draw', function() {
    const info = table.page.info();
    document.getElementById('pedidos-count').textContent =
        info.recordsDisplay + ' pedidos encontrados';
});

document.querySelectorAll('.filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const filtro = this.dataset.filtro;
        if (filtro === 'todos') {
            table.column(6).search('').draw();
        } else {
            table.column(6).search('^' + filtro + '$', true, false).draw();
        }
        document.querySelectorAll('.filter-btn').forEach(function(b) {
            b.classList.remove('btn-dark');
            b.classList.add('btn-outline-secondary');
        });
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-dark');
    });
});

document.getElementById('modalCodigos').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const id  = btn.getAttribute('data-id');
    const nro = btn.getAttribute('data-nro');
    const cod = btn.getAttribute('data-cod');

    document.getElementById('form-codigos').action = codigosUrl + id;
    document.getElementById('input-nro-orden').value = nro;
    document.getElementById('input-codigo').value = cod;
    document.getElementById('modal-codigos-info').textContent = 'Pedido #' + id;
    document.getElementById('form-codigos').classList.remove('was-validated');
});

document.getElementById('form-codigos').addEventListener('submit', function(e) {
    const nroOk = /^\d{8,10}$/.test(document.getElementById('input-nro-orden').value.trim());
    const codOk = /^[A-Za-z0-9]{4}$/.test(document.getElementById('input-codigo').value.trim());
    if (!nroOk || !codOk) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

document.getElementById('modalEstado').addEventListener('show.bs.modal', function(e) {
    const btn    = e.relatedTarget;
    const id     = btn.getAttribute('data-id');
    const estado = btn.getAttribute('data-estado');
    const cliente = btn.getAttribute('data-cliente');
    document.getElementById('form-estado').action = estadoUrl + id;
    document.getElementById('select-estado').value = estado;
    document.getElementById('modal-cliente-info').textContent = 'Pedido #' + id + ' — ' + cliente;
});

function ver_imagen(nombre_imagen) {
    
    var img  = document.getElementById('imagen-comprobante');
    var err  = document.getElementById('imagen-error');

    // Reiniciar estados
    err.classList.add('d-none');
    img.classList.remove('d-none');
    img.onerror = function() {
        img.classList.add('d-none');
        err.classList.remove('d-none');
    };

    img.src = imagenUrl + encodeURIComponent(nombre_imagen);

    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalImagen'));
    modal.show();
}

function ver_detalle(id_pedido) {
    document.getElementById('modalDetalleLabel').textContent = 'Detalle del Pedido #' + id_pedido;
    document.getElementById('detalle-loading').classList.remove('d-none');
    document.getElementById('detalle-error').classList.add('d-none');
    document.getElementById('detalle-contenido').classList.add('d-none');
    document.getElementById('detalle-tbody').innerHTML = '';

    var modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    modal.show();

    fetch(detalleUrl + id_pedido)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('detalle-loading').classList.add('d-none');

            if (!data.items || data.items.length === 0) {
                document.getElementById('detalle-error').textContent = 'No se encontraron ítems para este pedido.';
                document.getElementById('detalle-error').classList.remove('d-none');
                return;
            }

            var tbody = document.getElementById('detalle-tbody');
            var gran_total = 0;
            data.items.forEach(function(item) {
                var subtotal = item.cantidad * item.precio_unitario;
                gran_total += subtotal;
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + escHtml(item.name || '—') + '</td>' +
                    '<td class="text-center">' + escHtml(item.talla || '—') + '</td>' +
                    '<td class="text-center">' + item.cantidad + '</td>' +
                    '<td class="text-end">' + data.moneda + ' ' + parseFloat(item.precio_unitario).toFixed(2) + '</td>' +
                    '<td class="text-center">' + escHtml(item.unidad || '—') + '</td>' +
                    '<td class="text-end fw-semibold">' + data.moneda + ' ' + subtotal.toFixed(2) + '</td>';
                tbody.appendChild(tr);
            });

            document.getElementById('detalle-total').textContent = data.moneda + ' ' + gran_total.toFixed(2);
            document.getElementById('detalle-contenido').classList.remove('d-none');
        })
        .catch(function() {
            document.getElementById('detalle-loading').classList.add('d-none');
            document.getElementById('detalle-error').classList.remove('d-none');
        });
}
</script>
