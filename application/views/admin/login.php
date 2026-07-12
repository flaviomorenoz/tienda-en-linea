<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo $this->config->item('tienda_nombre'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border-radius: 16px; overflow: hidden; max-width: 420px; width: 100%; }
        .login-header { background: linear-gradient(135deg, #2d3436, #636e72); }
    </style>
</head>
<body>
    <div class="login-card card border-0 shadow-lg">
        <div class="login-header text-white text-center p-4">
            <i class="bi bi-bag-heart-fill fs-1 mb-2 d-block"></i>
            <h4 class="fw-bold mb-0"><?php echo $this->config->item('tienda_nombre'); ?></h4>
            <p class="text-white-50 small mb-0">Panel de Administración</p>
        </div>
        <div class="card-body p-4">

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?php echo htmlspecialchars($this->session->flashdata('error')); ?>
            </div>
            <?php endif; ?>

            <form action="<?php echo base_url('admin/login_post'); ?>" method="POST">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                       value="<?php echo $this->security->get_csrf_hash(); ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="usuario" class="form-control" placeholder="admin" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center text-muted small py-2 bg-light">
            <a href="<?php echo base_url(); ?>" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left me-1"></i>Volver a la tienda
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
