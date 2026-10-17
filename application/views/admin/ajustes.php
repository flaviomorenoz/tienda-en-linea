<?php
/**
 * Modulo AJUSTES (panel admin).
 *
 * Permite configurar:
 *  - Apariencia: tema CSS de la tienda (config_ajustes.tema_css).
 *  - Textos: TEXTO_BANNER y NOMBRE_NEGOCIO (tec_variables).
 *  - CRUD de cualquier variable de la tienda (tabla tec_variables).
 *
 * @var string $tema_activo         Tema CSS activo.
 * @var array  $temas_disponibles   Temas disponibles (archivo => descripcion).
 * @var string $texto_banner        Texto actual del banner superior.
 * @var string $nombre_negocio      Nombre actual del negocio.
 * @var array  $variables           Variables registradas en tec_variables.
 * @var int    $variable_name_len   Largo maximo de la columna name.
 * @var int    $variable_max_len    Largo maximo de la columna dato.
 */
?>
<style>
    #tabla-variables { font-size: .85rem; }
    #tabla-variables td, #tabla-variables th { vertical-align: middle; }
    .ajustes-var-dato { max-width: 460px; word-break: break-word; }
    .ajustes-acciones { white-space: nowrap; }
</style>

<div class="main-content">

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

    <!-- Tarjeta: Configuración de temas -->
    <div class="card shadow-sm border-0" style="max-width: 640px;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-palette me-2 text-primary"></i>Apariencia de la tienda
            </h5>
        </div>
        <?php echo form_open('ajustes/guardar_tema'); ?>

            <div class="card-body">

                <p class="text-muted small mb-4">
                    Seleccione el estilo de tema que desea aplicar al catálogo en línea.
                    El cambio se guarda y se aplica en toda la tienda.
                </p>

                <div class="mb-3">
                    <label for="tema_css" class="form-label fw-semibold">Tema de estilo</label>
                    <select name="tema_css" id="tema_css" class="form-select form-select-lg">
                        <?php foreach ($temas_disponibles as $archivo => $nombre): ?>
                        <option value="<?php echo $archivo; ?>" <?php echo ($tema_activo === $archivo) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        Previsualice los temas en la tienda antes de guardar.
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label for="texto_banner" class="form-label fw-semibold">Texto del banner superior</label>
                    <input type="text" name="texto_banner" id="texto_banner"
                           value="<?php echo htmlspecialchars($texto_banner, ENT_QUOTES, 'UTF-8'); ?>"
                           maxlength="<?php echo (int)$variable_max_len; ?>" required
                           class="form-control" placeholder="DESCUENTOS POR TEMPORADA...">
                    <div class="form-text">
                        Se guarda en la variable <code>TEXTO_BANNER</code> y se muestra en la franja superior de la tienda.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="nombre_negocio" class="form-label fw-semibold">Nombre del negocio</label>
                    <input type="text" name="nombre_negocio" id="nombre_negocio"
                           value="<?php echo htmlspecialchars($nombre_negocio, ENT_QUOTES, 'UTF-8'); ?>"
                           maxlength="120" required
                           class="form-control" placeholder="BELLAROSSE">
                    <div class="form-text">
                        Se guarda en la variable <code>NOMBRE_NEGOCIO</code> y se usa en la tienda, el panel y el pie de página.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Guardar Cambios
                    </button>
                    <a href="<?php echo base_url(); ?>" class="btn btn-outline-secondary" target="_blank">
                        <i class="bi bi-eye me-1"></i>Ver tienda
                    </a>
                </div>

            </div>

        <?php echo form_close(); ?>

    </div>

    <!-- Tarjeta: Variables de la tienda (CRUD sobre tec_variables) -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-sliders me-2 text-primary"></i>Variables de la tienda
            </h5>
            <button type="button" class="btn btn-primary btn-sm" id="btn-nueva-variable">
                <i class="bi bi-plus-lg me-1"></i>Nueva variable
            </button>
        </div>

        <div class="card-body">
            <p class="text-muted small mb-3">
                Estos son los textos configurables que usa la tienda en línea:
                <code>TEXTO_BANNER</code> (banner superior) y <code>NOMBRE_NEGOCIO</code> (nombre del negocio).
                Puede registrar variables adicionales para reutilizarlas en la tienda.
            </p>

            <div class="table-responsive">
                <table id="tabla-variables" class="table table-striped table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th style="width: 220px;">Nombre</th>
                            <th>Valor (dato)</th>
                            <th style="width: 110px;">Registro</th>
                            <th style="width: 130px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($variables)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay variables registradas. Use "Nueva variable" para registrar la primera.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($variables as $variable): ?>
                        <tr>
                            <td class="text-muted"><?php echo (int)$variable->id; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($variable->name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="ajustes-var-dato"><?php echo nl2br(htmlspecialchars($variable->dato, ENT_QUOTES, 'UTF-8')); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars((string)$variable->fec_reg, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-center ajustes-acciones">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-editar-variable"
                                        data-id="<?php echo (int)$variable->id; ?>"
                                        data-name="<?php echo htmlspecialchars($variable->name, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-dato="<?php echo htmlspecialchars($variable->dato, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-store="<?php echo (int)$variable->store_id; ?>"
                                        title="Editar variable">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <?php echo form_open('ajustes/eliminar_variable/' . (int)$variable->id, array('class' => 'd-inline form-eliminar-variable')); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar variable">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: crear / editar variable de la tienda -->
<div class="modal fade" id="modalVariable" tabindex="-1" aria-labelledby="modalVariableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?php echo form_open('ajustes/guardar_variable', array('id' => 'form-variable')); ?>

                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalVariableLabel">Nueva variable</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="variable_id" id="variable_id" value="0">
                    <input type="hidden" name="store_id" id="store_id" value="1">

                    <div class="mb-3">
                        <label for="variable_name" class="form-label fw-semibold">Nombre</label>
                        <input type="text" class="form-control" id="variable_name" name="name"
                               maxlength="<?php echo (int)$variable_name_len; ?>" required
                               autocomplete="off" placeholder="TEXTO_BANNER">
                        <div class="form-text">
                            Solo mayúsculas, números y guion bajo (máximo <?php echo (int)$variable_name_len; ?> caracteres).
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="variable_dato" class="form-label fw-semibold">Valor (dato)</label>
                        <textarea class="form-control" id="variable_dato" name="dato" rows="3"
                                  maxlength="<?php echo (int)$variable_max_len; ?>" required></textarea>
                        <div class="form-text">
                            <span id="variable_dato_count">0</span> / <?php echo (int)$variable_max_len; ?> caracteres.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>


<script>
$(document).ready(function () {

    // Buscador / paginación del listado de variables
    $('#tabla-variables').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: [4] }],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
    });

    var modalVariable = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVariable'));

    function contar_dato() {
        $('#variable_dato_count').text($('#variable_dato').val().length);
    }

    // Nueva variable
    $('#btn-nueva-variable').on('click', function () {
        $('#modalVariableLabel').text('Nueva variable');
        $('#variable_id').val(0);
        $('#store_id').val(1);
        $('#variable_name').val('');
        $('#variable_dato').val('');
        contar_dato();
        modalVariable.show();
    });

    // Editar variable (los datos vienen en los data-* del botón)
    $('#tabla-variables').on('click', '.btn-editar-variable', function () {
        var boton = $(this);
        $('#modalVariableLabel').text('Editar variable');
        $('#variable_id').val(boton.data('id'));
        $('#store_id').val(boton.data('store') || 1);
        $('#variable_name').val(boton.data('name'));
        $('#variable_dato').val(boton.data('dato'));
        contar_dato();
        modalVariable.show();
    });

    // El nombre solo admite mayúsculas, números y guion bajo
    $('#variable_name').on('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
    });

    // Contador de caracteres del dato
    $('#variable_dato').on('input', contar_dato);

    // Confirmación antes de eliminar una variable
    $(document).on('submit', '.form-eliminar-variable', function () {
        var nombre = $(this).closest('tr').find('td').eq(1).text().trim();
        return confirm('¿Desea eliminar la variable ' + nombre + '? Esta acción no se puede deshacer.');
    });

});
</script>

