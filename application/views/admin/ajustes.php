<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Ajustes</h4>
            <p class="text-muted small mb-0">Configuración general de la tienda</p>
        </div>
    </div>

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
        <div class="card-body">

            <p class="text-muted small mb-4">
                Seleccione el estilo de tema que desea aplicar al catálogo en línea.
                El cambio se guarda y se aplica en toda la tienda.
            </p>

            <?php echo form_open('ajustes/guardar_tema'); ?>

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

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Guardar tema
                    </button>
                    <a href="<?php echo base_url(); ?>" class="btn btn-outline-secondary" target="_blank">
                        <i class="bi bi-eye me-1"></i>Ver tienda
                    </a>
                </div>

            <?php echo form_close(); ?>

        </div>
    </div>

</div>