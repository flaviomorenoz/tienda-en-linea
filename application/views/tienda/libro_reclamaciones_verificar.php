<style>
    .lr-page {
        font-family: var(--font, 'Poppins', sans-serif);
        color: var(--texto, #3A3735);
        letter-spacing: 0.02em;
    }
    .lr-kicker {
        display: inline-block;
        background: var(--primary, #FA0082);
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        padding: 0.4rem 0.9rem;
        margin-bottom: 1rem;
    }
    .lr-title {
        font-family: 'Fraunces', serif;
        font-weight: 800;
        color: var(--negro, #1D1D1B);
        text-transform: uppercase;
        letter-spacing: 0.01em;
    }
    .lr-card {
        border: 1px solid var(--borde, #e8e6e3);
        border-top: 3px solid var(--primary, #FA0082);
        border-radius: 0;
        background: #ffffff;
        padding: 1.6rem 1.5rem;
    }
    .lr-input,
    .lr-input:focus {
        border-radius: 0;
        border-color: var(--borde, #d9d5cf);
        box-shadow: none;
    }
    .lr-input:focus {
        border-color: var(--primary, #FA0082);
    }
    .lr-boton {
        border-radius: 0;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .lr-tabla td, .lr-tabla th {
        font-size: 0.9rem;
        border-color: var(--borde, #e8e6e3);
    }
    .lr-tabla th {
        color: var(--texto-muted, #8a8580);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.1em;
        width: 38%;
    }
    .lr-estado-badge {
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .lr-respuesta {
        background: var(--light-bg, #f7f6f4);
        border-left: 4px solid #198754;
        padding: 1rem 1.2rem;
        font-size: 0.92rem;
        line-height: 1.7;
        white-space: pre-line;
    }
</style>

<div class="container lr-page py-4">

    <div class="text-center mb-4">
        <span class="lr-kicker">Consulta de estado</span>
        <h1 class="lr-title">Consultar mi solicitud</h1>
        <p class="mx-auto mt-2" style="max-width:680px;">
            Ingrese el código que recibió al registrar su reclamo o queja (formato
            <strong>LR-AAAAMMDD-NNNN</strong>).
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <?php if (isset($error) && $error !== ''): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="lr-card mb-4">
                <?php echo form_open('libro-reclamaciones/consultar', array('id' => 'form-verificar', 'class' => 'd-flex gap-2')); ?>
                <div class="flex-grow-1">
                    <input type="text" name="codigo" id="codigo" maxlength="30"
                           class="form-control lr-input text-uppercase"
                           placeholder="Ej: LR-20260831-0001"
                           value="<?php echo htmlspecialchars($this->input->post('codigo'), ENT_QUOTES, 'UTF-8'); ?>"
                           required>
                </div>
                <button type="submit" class="btn btn-dark lr-boton">
                    <i class="bi bi-search me-1"></i>Consultar
                </button>
                <?php echo form_close(); ?>
            </div>

            <?php if (isset($reclamo) && $reclamo): ?>
            <div class="lr-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-journal-check me-2"></i>Solicitud <?php echo htmlspecialchars($reclamo->codigo, ENT_QUOTES, 'UTF-8'); ?></h5>
                        <small class="text-muted">Registrada el <?php echo date('d/m/Y H:i', strtotime($reclamo->created_at)); ?></small>
                    </div>
                    <?php
                    $badge = 'secondary';
                    if ($reclamo->estado === 'EN_PROCESO')  $badge = 'warning text-dark';
                    if ($reclamo->estado === 'RESPONDIDO')  $badge = 'success';
                    if ($reclamo->estado === 'ARCHIVADO')   $badge = 'dark';
                    ?>
                    <span class="badge bg-<?php echo $badge; ?> lr-estado-badge"><?php echo $reclamo->estado; ?></span>
                </div>

                <table class="table lr-tabla mb-4">
                    <tbody>
                        <tr><th scope="row">Tipo</th><td><?php echo $reclamo->tipo === 'QUEJA' ? 'Queja' : 'Reclamo'; ?></td></tr>
                        <tr><th scope="row">Consumidor</th><td><?php echo htmlspecialchars($reclamo->nombres, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <tr><th scope="row">Documento</th><td><?php echo htmlspecialchars($reclamo->tipo_documento, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($reclamo->numero_documento, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <tr><th scope="row">Producto / servicio</th><td><?php echo htmlspecialchars($reclamo->producto_servicio, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <?php if (!empty($reclamo->numero_pedido)): ?>
                        <tr><th scope="row">N° de pedido</th><td><?php echo htmlspecialchars($reclamo->numero_pedido, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($reclamo->monto_reclamado !== NULL && $reclamo->monto_reclamado !== ''): ?>
                        <tr><th scope="row">Monto reclamado</th><td><?php echo $this->config->item('moneda_simbolo'); ?> <?php echo number_format((float)$reclamo->monto_reclamado, 2); ?></td></tr>
                        <?php endif; ?>
                        <tr><th scope="row">Detalle</th><td style="white-space:pre-line;"><?php echo htmlspecialchars($reclamo->detalle, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                        <?php if (!empty($reclamo->respuesta)): ?>
                        <tr>
                            <th scope="row">Respuesta del proveedor</th>
                            <td>
                                <div class="lr-respuesta"><?php echo htmlspecialchars($reclamo->respuesta, ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php if (!empty($reclamo->fecha_respuesta)): ?>
                                <small class="text-muted">Respondida el <?php echo date('d/m/Y H:i', strtotime($reclamo->fecha_respuesta)); ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="text-center">
                    <a href="<?php echo base_url(); ?>" class="btn btn-outline-secondary lr-boton">
                        <i class="bi bi-shop me-2"></i>Volver a la tienda
                    </a>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

