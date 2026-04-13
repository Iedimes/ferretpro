<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - FerrePro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 450px; width: 100%; }
        .card-header { background: #2563eb; color: white; padding: 30px; border-radius: 15px 15px 0 0; text-align: center; }
        .back-link { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2>Recuperar Contraseña</h2>
            <p class="mb-0">FerrePro - Sistema de Gestión</p>
        </div>
        <div class="p-4">
            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            
            <?php if (empty($_GET['success'])): ?>
                <p class="text-muted mb-4">
                    Ingrese su dirección de email y recibirá un link para reiniciar su contraseña.
                </p>
                
                <form method="POST" action="?page=forgot_password_post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                        <small class="text-muted">Debe ser el email registrado en su cuenta</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar Link de Reinicio</button>
                </form>
            <?php endif; ?>
            
            <div class="text-center back-link">
                <a href="?page=login" class="btn btn-link">Volver al Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
