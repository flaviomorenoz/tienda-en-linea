</main>

<!-- FOOTER -->
<footer class="footer-tienda text-white mt-5 py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-bag-heart-fill me-2"></i>
                    <?php echo $this->config->item('tienda_nombre'); ?>
                </h5>
                <p class="text-white-50 small"><?php echo $this->config->item('tienda_slogan'); ?></p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-semibold mb-3">Contacto</h6>
                <p class="text-white-50 small mb-1">
                    <i class="bi bi-whatsapp me-2 text-success"></i>
                    <a href="https://wa.me/<?php echo $this->config->item('whatsapp_numero'); ?>"
                       class="text-white-50 text-decoration-none" target="_blank">
                        +<?php echo $this->config->item('whatsapp_numero'); ?>
                    </a>
                </p>
                <p class="text-white-50 small">
                    <i class="bi bi-envelope me-2"></i>
                    <?php echo $this->config->item('tienda_email'); ?>
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-semibold mb-3">Categorías</h6>
                <?php
                $CI =& get_instance();
                if (!isset($CI->Producto_model)) { $CI->load->model('Producto_model'); }
                $cats = $CI->Producto_model->get_categorias();
                foreach ($cats as $cat):
                ?>
                <a href="<?php echo base_url('tienda/categoria/' . urlencode($cat)); ?>"
                   class="d-block text-white-50 small text-decoration-none mb-1 footer-cat-link">
                    <i class="bi bi-chevron-right me-1"></i><?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="text-center text-white-50 small">
            &copy; <?php echo date('Y'); ?> <?php echo $this->config->item('tienda_nombre'); ?>. Todos los derechos reservados.
        </div>
    </div>
</footer>

<!-- Asistente IA -->
<div id="chat-ia-widget">
    <button id="chat-ia-toggle" class="chat-ia-toggle" type="button" aria-label="Abrir asistente">
        <i class="bi bi-chat-heart-fill"></i>
    </button>
    <div id="chat-ia-panel" class="chat-ia-panel d-none">
        <div class="chat-ia-header">
            <span><i class="bi bi-robot me-2"></i>Asistente <?php echo htmlspecialchars($this->config->item('tienda_nombre'), ENT_QUOTES, 'UTF-8'); ?></span>
            <button id="chat-ia-cerrar" type="button" class="btn-close btn-close-white" aria-label="Cerrar"></button>
        </div>
        <div id="chat-ia-mensajes" class="chat-ia-mensajes"></div>
        <div id="chat-ia-escribiendo" class="chat-ia-escribiendo d-none">Escribiendo...</div>
        <div id="chat-ia-estado" class="chat-ia-estado d-none"></div>
        <button id="chat-ia-transferir" type="button" class="chat-ia-transferir">
            <i class="bi bi-person-heart me-1"></i>Hablar con una vendedora
        </button>
        <button id="chat-ia-nueva" type="button" class="chat-ia-transferir d-none">
            <i class="bi bi-arrow-repeat me-1"></i>Iniciar nueva conversación
        </button>
        <form id="chat-ia-form" class="chat-ia-form">
            <input type="text" id="chat-ia-input" class="form-control" placeholder="Escribe tu pregunta..." autocomplete="off">
            <button type="submit" class="btn"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS Carrito -->
<script src="<?php echo base_url('assets/js/carrito.js'); ?>"></script>
<!-- JS Asistente IA -->
<script>
    window.CHAT_IA_URL = '<?php echo base_url('chat/preguntar'); ?>';
    window.CHAT_SOLICITAR_URL = '<?php echo base_url('chat/solicitar_vendedora'); ?>';
    window.CHAT_ENVIAR_URL = '<?php echo base_url('chat/enviar'); ?>';
    window.CHAT_ESTADO_URL = '<?php echo base_url('chat/estado'); ?>';
    window.CHAT_SUBIR_IMAGEN_URL = '<?php echo base_url('chat/subir_imagen'); ?>';
</script>
<script src="<?php echo base_url('assets/js/chat_ia.js'); ?>"></script>

</body>
</html>
