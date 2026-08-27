<style>
    /* pequeños ajustes para que el footer se vea como el de la imagen */
    .footer-bella {
      background-color: var(--primary-light); /*#f8f6f2; fondo claro, similar a un tono crudo */
      border-top: 1px solid #e0dcd5;
      padding-top: 2.5rem;
      padding-bottom: 1.5rem;
    }
    .footer-bella h6 {
      font-weight: 600;
      letter-spacing: 0.5px;
      color: #000000;
      margin-bottom: 1rem;
      font-size: 1rem;
      text-transform: uppercase;
    }
    .footer-bella a {
      text-decoration: none;
      color: #000000;
      font-size: 0.95rem;
      display: inline-block;
      transition: color 0.2s;
    }
    .footer-bella a:hover {
      color: #b68a6b; /* tono cálido, como "Bella Rose" */
    }
    .footer-bella .list-unstyled li {
      margin-bottom: 0.5rem;
    }
    .footer-bella .brand-footer {
      font-family: 'Georgia', serif;
      font-size: 1.8rem;
      font-weight: 400;
      letter-spacing: 1px;
      color: #050505;
      margin-bottom: 0.25rem;
    }
    .footer-bella .brand-sub {
      font-size: 0.8rem;
      color: #080808;
      letter-spacing: 2px;
      margin-bottom: 1.2rem;
    }
    .footer-bella .separator-dots {
      color: #b8b2a8;
      font-size: 1.2rem;
      padding: 0 6px;
    }
    .footer-bella .bottom-bar {
      border-top: 1px solid #e0dcd5;
      padding-top: 1.2rem;
      margin-top: 1.8rem;
      font-size: 0.8rem;
      color: #6b6760;
    }
    .footer-bella .bottom-bar a {
      font-size: 0.8rem;
      color: #000000;
    }
    .footer-bella .bottom-bar a:hover {
      color: #b68a6b;
    }
    /* para que se vea el "d f" y "Disfruta!" tal cual en la imagen */
    .footer-bella .disfruta-line {
      font-size: 1.2rem;
      font-weight: 300;
      color: #4f4b45;
      letter-spacing: 2px;
      margin-bottom: 0.2rem;
    }
    .footer-bella .df-line {
      font-size: 0.9rem;
      color: #8b867e;
      letter-spacing: 4px;
      margin-bottom: 0.5rem;
    }
    .footer-bella .legal-link {
      font-size: 0.8rem;
      color: #8b867e;
    }
  </style>

<!-- EJEMPLO DE CONTENIDO PREVIO (solo para contextualizar) -->
<!--
<div class="container py-4">
  <div class="p-4 bg-light rounded-3 text-center">
    <h1 class="display-6">Catálogo Bella Rose</h1>
    <p class="lead">Productos seleccionados con estilo</p>
    <hr>
    <p><i class="fas fa-chevron-down text-secondary"></i>  desplázate para ver el footer  <i class="fas fa-chevron-down text-secondary"></i></p>
  </div>
</div>
-->
<!-- ============ FOOTER ============ -->
<footer class="footer-bella">
  <div class="container">

    <!-- Fila principal: 4 columnas (logo + 3 columnas de enlaces) -->
    <div class="row g-4">

      <!-- Columna 1: Marca + "Disfruta! d f" -->
      <div class="col-md-3">
        <div class="brand-footer">Bella Rose</div>
        <div class="brand-sub"><a href="<?php echo base_url('quienes-somos'); ?>">Quiénes somos</a></div>
        <div class="brand-sub">Tiendas Otros</div>
        <!-- líneas que aparecen en la imagen: "Disfruta!" y "d f" -->
        <div class="disfruta-line mt-2">Disfruta!</div>
        <div class="df-line"></div>
        <!-- pequeño espacio y enlace "Info legal" (aparece en la imagen como "Info lega...") -->
      </div>

      <!-- Columna 2: Servicio al cliente -->
      <div class="col-md-3">
        <h6>Servicio al cliente</h6>
        <ul class="list-unstyled">
          <li><a href="#">Cómo comprar</a></li>
          <li><a href="#">Cambios</a></li>
          <li><a href="#">Medio de Pago</a></li>
          <li><a href="#">Libro de Reclamaciones</a></li>
        </ul>
      </div>

      <!-- Columna 3: (podría ser "Información" o similar, pero en la imagen no hay más texto; lo dejamos como "Tiendas" y "Otros" para reflejar el contenido) 
           Pero la imagen muestra "Bella Rose / Quienes somos. Tiendas Otros." como parte del branding. 
           Ya lo pusimos en la columna 1. 
           Aquí podemos poner un bloque extra con "Síguenos" o "Contacto" para dar equilibrio, 
           pero respetando el contenido de la imagen. 
           En la imagen solo se ven esas secciones: "Bella Rose", "Quienes somos. Tiendas Otros.", "Disfruta! d f", 
           "Servicio al cliente" con sus 4 items y "Info lega..." 
           Por lo tanto, para que el footer no quede vacío, voy a crear una columna "Tiendas" y otra "Otros" 
           (tal como sugiere el texto "Tiendas Otros") pero en la imagen son parte del brand. 
           Sin embargo, para dar más estructura y que se vea como un catálogo, pondré en la columna 3 "Tiendas" y en la columna 4 "Otros" con enlaces genéricos.
           También respetaré "Info lega..." que está al final. 
        -->
      <div class="col-md-3">
        <h6>Tiendas</h6>
        <ul class="list-unstyled">
          <li><a href="#">Bella Rose Madrid</a></li>
          <li><a href="#">Bella Rose Barcelona</a></li>
          <li><a href="#">Bella Rose Valencia</a></li>
          <li><a href="#">Outlet Online</a></li>
        </ul>
      </div>

      <!-- Columna 4: Otros (enlaces adicionales) -->
      <div class="col-md-3">
        <h6>Otros</h6>
        <ul class="list-unstyled">
          <li><a href="<?php echo base_url('quienes-somos'); ?>">Sobre nosotros</a></li>
          <li><a href="#">Blog</a></li>
          <li><a href="#">Contacto</a></li>
          <li><a href="#">Preguntas frecuentes</a></li>
        </ul>
        <!-- mostramos "Info lega..." tal cual aparece en la imagen (abreviado) -->
        <div class="mt-3">
          <a href="#" class="legal-link"><i class="far fa-file-alt me-1"></i>Info lega...</a>
        </div>
      </div>
    </div>

    <!-- Línea inferior con copyright y enlaces legales (similar al estilo de la imagen) -->
    <div class="bottom-bar d-flex flex-wrap justify-content-between align-items-center">
      <span>© 2026 Bella Rose · Todos los derechos reservados</span>
      <span>
        <a href="#" class="me-3">Política de privacidad</a>
        <a href="#" class="me-3">Cookies</a>
        <a href="#">Condiciones de uso</a>
      </span>
    </div>

  </div>
</footer>
    </body>
</html>