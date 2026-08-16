<style>
    /*body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }*/
    .admin-sidebar { background: #2d3436; min-height: 100vh; width: 240px; position: fixed; left: 0; top: 0; z-index: 100; }
    .container { margin-left: 240px; padding: 2rem; }
    @media (max-width: 768px) {
        .admin-sidebar { display: none; }
        .main-content { margin-left: 0; }
    }

    /* ===== Chat estilo WhatsApp ===== */
    .chats-mensajes {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem;
        max-height: 60vh;
        background: #e5ddd5;
        background-image: linear-gradient(rgba(255,255,255,.2) 1px, transparent 1px),
                          linear-gradient(90deg, rgba(255,255,255,.2) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .chat-fila {
        display: flex;
        margin-bottom: .6rem;
    }
    .chat-fila.recibido { justify-content: flex-start; }
    .chat-fila.enviado   { justify-content: flex-end; }
    .burbuja {
        position: relative;
        max-width: 75%;
        padding: .5rem .8rem 1.4rem;
        border-radius: 12px;
        font-size: .9rem;
        white-space: pre-wrap;
        word-break: break-word;
        box-shadow: 0 1px 1px rgba(0,0,0,.13);
        line-height: 1.4;
    }
    .burbuja.recibido {
        background: #ffffff;
        color: #111;
        border-top-left-radius: 2px;
    }
    .burbuja.enviado {
        background: #dcf8c6;
        color: #111;
        border-top-right-radius: 2px;
    }
    .burbuja .hora {
        position: absolute;
        bottom: .25rem;
        right: .6rem;
        font-size: .68rem;
        color: #999;
        display: flex;
        align-items: center;
        gap: .25rem;
    }
    .burbuja.enviado .hora { color: #6b8f5e; }
    .burbuja .hora .bi-check2-all { color: #53bdeb; font-size: .8rem; }
</style>

<!-- Contenido principal -->
<!--<div class="main-content" style="padding-top:1px!important">-->

    <!--
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table id="tabla-whatsapp" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Id</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    -->
<div class="container">
    <div class="row">
        <div class="col-sm-10 col-lg-8">
            <table id="example" class="display" style="width:98%; font-size: 14px!important; margin-bottom: 20px;" data-page-length='22'>
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha</th>
                        <th>Origen</th>
                        <th>Nombre</th>
                        <th>Nro. Msjes</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal conversación estilo chat -->
<div class="modal fade" id="modalTranscripcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalTranscripcionLabel">
                    <i class="bi bi-whatsapp text-success me-1"></i> Conversación
                </h6>
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

<!-- Modal enviar mensaje -->
<div class="modal fade" id="modalEnviar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-send text-success me-1"></i> Enviar mensaje
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Número origen</label>
                    <input type="text" id="enviar-origen" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Número destino</label>
                    <input type="text" id="enviar-destino" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje</label>
                    <textarea id="enviar-mensaje" class="form-control" rows="4" placeholder="Escribe el mensaje..."></textarea>
                </div>
                <div id="enviar-resultado"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-enviar" onclick="enviar_mensaje()">
                    <i class="bi bi-send me-1"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    const conversacionUrl = <?php echo json_encode(base_url('wts/conversacion/')); ?>;

    $(document).ready(function() {
        $('#example').DataTable({
            "order": [[0, "desc"]],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            "ajax": "<?= base_url("wts/historial_json") ?>",
            "columnDefs": [
                { visible: true, "targets": [2]},
                { 
                    render: function(data, type, row){
                        var telefono = row[2] || '';
                        let cad = "<a href='#' title='Ver' onclick='detalle(" + row[0] + ")'><span class='bi bi-eye' style=\"font-size:16px\"></span></a>&nbsp;"
                        cad += "<a href='#' title='Enviar' onclick=\"abrirEnviar(" + row[0] + ",'"  + row[6] + "', '" + telefono + "')\"><span class='bi bi-send' style=\"font-size:16px\"></span></a>&nbsp;"
                        return cad
                    },
                    "targets":  [5]
                }
            ]
        });
    });

    function detalle(id) {
        const $loading = $('#transcripcion-loading');
        const $error   = $('#transcripcion-error');
        const $chat    = $('#chats-mensajes');

        $loading.removeClass('d-none');
        $error.addClass('d-none');
        $chat.addClass('d-none').empty();

        fetch(conversacionUrl + id)
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                $loading.addClass('d-none');

                if (!data.ok || !Array.isArray(data.mensajes) || data.mensajes.length === 0) {
                    $error.removeClass('d-none');
                    return;
                }

                data.mensajes.forEach(function(m) {
                    var tipo = (m.tipo || '').trim().toUpperCase();
                    var esEnviado = (tipo === 'ENVIADO');

                    var fila = document.createElement('div');
                    fila.className = 'chat-fila ' + (esEnviado ? 'enviado' : 'recibido');

                    var burbuja = document.createElement('div');
                    burbuja.className = 'burbuja ' + (esEnviado ? 'enviado' : 'recibido');

                    var texto = document.createElement('div');
                    texto.textContent = m.mensaje || '';

                    var hora = document.createElement('div');
                    hora.className = 'hora';
                    hora.textContent = fmtFechaHora(m.fecha);
                    if (esEnviado) {
                        hora.innerHTML = fmtFechaHora(m.fecha) + ' <i class="bi bi-check2-all"></i>';
                    }

                    burbuja.appendChild(texto);
                    burbuja.appendChild(hora);
                    fila.appendChild(burbuja);
                    $chat.append(fila);
                });

                $chat.removeClass('d-none');

                // Scroll al inicio (mensaje más antiguo)
                $chat.scrollTop(0);
            })
            .catch(function() {
                $loading.addClass('d-none');
                $error.removeClass('d-none');
            });

        var modalEl = document.getElementById('modalTranscripcion');
        var modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    var telDestinoEnvio = '';
    var telOrigenEnvio = '';

    function abrirEnviar(id, origen, telefono) {
        // Limpiar campos del modal
        $('#enviar-mensaje').val('');
        $('#enviar-resultado').empty();
        $('#btn-enviar').prop('disabled', false).html('<i class="bi bi-send me-1"></i> Enviar');

        // Quitar prefijo "whatsapp:" y espacios
        var numero = String(telefono || '').trim();
        numero = numero.replace(/^whatsapp:/i, '');
        telDestinoEnvio = numero;
        $('#enviar-destino').val(numero);

        var numeroD = String(origen || '').trim();
        numeroD = numeroD.replace(/^whatsapp:/i, '');
        telOrigenEnvio = numeroD;
        $('#enviar-origen').val(numeroD);

        var modalEl = document.getElementById('modalEnviar');
        var modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function enviar_mensaje() {
        var msg = $('#enviar-mensaje').val().trim();
        var $btn = $('#btn-enviar');
        var $res = $('#enviar-resultado');

        $res.empty();

        if (!msg) {
            $res.html('<div class="alert alert-warning py-2 mb-0">Escribe un mensaje antes de enviar.</div>');
            return;
        }

        // Se le extrae el signo "+" al inicio del número
        var destino = telDestinoEnvio.replace(/^\+/, '');
        var origen = telOrigenEnvio.replace(/^\+/, '');

        // URL local del controlador (proxy) — evita el bloqueo de CORS
        //console.log("Flavito")
        var url = <?php echo json_encode(base_url('../varios/twilios/twilio_enviar_mensajes.php')); ?>
            + '?origen=' + encodeURIComponent(origen)
            + '&destino=' + encodeURIComponent(destino)
            + '&msg=' + encodeURIComponent(msg);

        console.log("base_url: " + "<?= base_url("") ?>");
        console.log(url);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Enviando...');

        fetch(url)
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> Enviar');

                if (data && data.ok) {
                    $res.html('<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle me-1"></i> Mensaje enviado correctamente.</div>');
                    $('#enviar-mensaje').val('');
                } else {
                    var errorMsg = (data && data.error) ? data.error : 'El servicio no confirmó el envío.';
                    $res.html('<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle me-1"></i> ' + errorMsg + '</div>');
                }
            })
            .catch(function() {
                $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> Enviar');
                $res.html('<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle me-1"></i> No se pudo enviar el mensaje. Verifica la conexión con el servicio.</div>');
            });
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&')
            .replace(/</g, '<')
            .replace(/>/g, '>')
            .replace(/"/g, '"');
    }

    function fmtFecha(str) {
        if (!str) return '—';
        var d = new Date(str.replace(' ', 'T'));
        if (isNaN(d.getTime())) return str;
        return d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function fmtFechaHora(str) {
        if (!str) return '';
        // La fecha puede venir como "YYYY-MM-DD HH:MM:SS"
        var d = new Date(String(str).replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        var fecha = d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
        var hora  = d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
        return fecha + ' ' + hora;
    }
</script>