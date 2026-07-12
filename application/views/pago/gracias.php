<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <!-- Confirmación -->
            <div class="text-center mb-4">
                <div class="success-icon mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:5rem;"></i>
                </div>
                <h2 class="fw-bold text-success">¡Pedido confirmado!</h2>
                <p class="text-muted">Tu pago fue procesado exitosamente. Gracias por tu compra.</p>
            </div>

            <!-- Datos del pedido -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white fw-semibold">
                    <i class="bi bi-receipt me-2"></i>Detalle del pedido #<?php echo $pedido->id; ?>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Fecha</small>
                            <span class="fw-semibold"><?php echo date('d/m/Y H:i', strtotime($pedido->fecha)); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Estado</small>
                            <span class="badge bg-success"><?php echo htmlspecialchars($pedido->estado_pago, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Cliente</small>
                            <span class="fw-semibold"><?php echo htmlspecialchars($pedido->nombres, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">DNI</small>
                            <span><?php echo htmlspecialchars($pedido->dni, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Dirección de envío</small>
                            <span><?php echo htmlspecialchars($pedido->direccion_envio, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Celular</small>
                            <span><?php echo htmlspecialchars($pedido->celular); ?></span>
                        </div>
                        <?php if ($pedido->codigo_transaccion): ?>
                        <div class="col-12">
                            <small class="text-muted d-block">Código de transacción</small>
                            <code><?php echo htmlspecialchars($pedido->codigo_transaccion); ?></code>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Items del pedido -->
                    <table class="table table-sm table-borderless mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Talla</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d->nombre); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($d->talla); ?></td>
                                <td class="text-center"><?php echo $d->cantidad; ?></td>
                                <td class="text-end">
                                    <?php echo $this->config->item('moneda_simbolo'); ?>
                                    <?php echo number_format($d->precio_unitario * $d->cantidad, 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3">Total</td>
                                <td class="text-end precio">
                                    <?php echo $this->config->item('moneda_simbolo'); ?>
                                    <?php echo number_format($pedido->total, 2); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="d-grid gap-2">
                <a href="<?php echo base_url(); ?>" class="btn btn-dark btn-lg">
                    <i class="bi bi-grid me-2"></i>Seguir comprando
                </a>
                <a href="https://wa.me/<?php echo $this->config->item('whatsapp_numero'); ?>?text=<?php echo urlencode('Hola, acabo de realizar el pedido #' . $pedido->id . '. ¿Cuándo me lo enviarán?'); ?>"
                   class="btn btn-consultar" target="_blank">
                    <i class="bi bi-whatsapp me-2"></i>Preguntar sobre mi pedido
                </a>
            </div>
        </div>
    </div>
</div>
