<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FerrePro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 400px; width: 100%; }
        .login-header { background: #2563eb; color: white; padding: 30px; border-radius: 15px 15px 0 0; text-align: center; }
        .forgot-password-link { text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2>FerrePro</h2>
            <p class="mb-0">Sistema de Gestión</p>
        </div>
        <div class="p-4">
            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="?page=login_post">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="forgot-password-link">
                        <small><a href="?page=forgot_password">¿Olvidó su contraseña?</a></small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
            <div class="text-center mt-3 text-muted">
                <small>FerrePro v1.2.0 - Autenticación y Seguridad</small>
            </div>
        </div>
    </div>
</body>
</html>
