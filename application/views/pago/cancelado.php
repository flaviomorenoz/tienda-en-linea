<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <i class="bi bi-x-circle-fill text-danger mb-3" style="font-size:5rem;"></i>
            <h2 class="fw-bold text-danger">Pago no procesado</h2>
            <p class="text-muted mb-4">Hubo un problema al procesar tu pago. Tu pedido no fue completado.</p>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle me-2"></i>
                Los productos en tu carrito siguen guardados. Puedes intentar nuevamente.
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-3">
                <a href="<?php echo base_url('checkout'); ?>" class="btn btn-dark btn-lg">
                    <i class="bi bi-arrow-repeat me-2"></i>Intentar nuevamente
                </a>
                <a href="<?php echo base_url('carrito'); ?>" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-cart3 me-2"></i>Ver carrito
                </a>
            </div>
        </div>
    </div>
</div>
