<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'FerrePro' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { 
            --primary: #2563eb; 
            --success: #10b981; 
            --danger: #ef4444; 
            --warning: #f59e0b;
            --info: #06b6d4;
            --secondary: #8b5cf6;
            --dark: #1e293b; 
        }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f9ff; }
        .sidebar { width: 250px; background: var(--dark); height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto; }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 20px; border-left: 3px solid transparent; }
        .sidebar .nav-link:hover { background: #334155; color: white; border-left-color: var(--primary); }
        .sidebar .nav-link.active { background: #334155; color: white; border-left-color: var(--primary); }
        .main-content { margin-left: 250px; padding: 20px; }
        .navbar { background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%); border-radius: 10px; border: 1px solid rgba(37, 99, 235, 0.1); }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .stat-card { border-left: 4px solid; transition: all 0.3s ease; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); }
        .btn-nav-back { background: linear-gradient(135deg, var(--primary), #1d4ed8); color: white; border: none; }
        .btn-nav-back:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); color: white; }
        .table-hover tbody tr:hover { background-color: rgba(37, 99, 235, 0.05) !important; }
        .badge { border-radius: 20px; padding: 6px 12px; font-weight: 600; }
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
