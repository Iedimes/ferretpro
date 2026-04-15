<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'FerrePro' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #2563eb; --dark: #1e293b; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; }
        .sidebar { width: 250px; background: var(--dark); height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto; }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: white; }
        .main-content { margin-left: 250px; padding: 20px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card { border-left: 4px solid; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="p-3 text-center border-bottom border-secondary">
            <h5 class="text-white mb-0">FerrePro</h5>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="?page=dashboard">Dashboard</a>
            <a class="nav-link" href="?page=pos">Punto de Venta</a>
            <a class="nav-link" href="?page=sales">Ventas</a>
            <a class="nav-link" href="?page=quotes">Cotizaciones</a>
            <a class="nav-link" href="?page=credit_notes">Notas Crédito</a>
            <a class="nav-link" href="?page=purchases">Compras</a>
            <a class="nav-link" href="?page=products">Productos</a>
            <a class="nav-link" href="?page=clients">Clientes</a>
            <a class="nav-link" href="?page=providers">Proveedores</a>
            <a class="nav-link" href="?page=receivable">Cuentas por Cobrar</a>
            <a class="nav-link" href="?page=payable">Cuentas por Pagar</a>
            <a class="nav-link" href="?page=expenses">Gastos</a>
            <a class="nav-link" href="?page=cash">Caja</a>
            <a class="nav-link" href="?page=categories">Categorías</a>
            <a class="nav-link" href="?page=reports">Reportes</a>
            <a class="nav-link" href="?page=backup">Backup</a>
            <a class="nav-link" href="?page=users">Usuarios</a>
            <a class="nav-link" href="?page=settings">Configuración</a>
            <a class="nav-link" href="?page=logout">Salir</a>
        </nav>
    </div>
    
    <div class="main-content">
        <nav class="navbar navbar-expand-lg mb-4 rounded">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1"><?= $pageTitle ?? 'FerrePro' ?></span>
                <div class="d-flex align-items-center gap-3">
                    <span><?= user()['name'] ?? '' ?></span>
                    <a href="?page=profile" class="btn btn-sm btn-outline-primary">Mi Perfil</a>
                </div>
            </div>
        </nav>
        
        <?php echo Flash::renderAll(); ?>
        
        <?= $content ?? '<!-- content vacio -->' ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
