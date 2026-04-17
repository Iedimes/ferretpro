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
        
        /* Sidebar Improvements */
        .sidebar { 
            width: 250px; 
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            overflow-y: auto;
            box-shadow: 2px 0 15px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        
        .sidebar .logo-section {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
        }
        
        .sidebar .logo-section h5 {
            color: white;
            margin: 0;
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
        }
        
        .sidebar nav {
            padding: 15px 0;
        }
        
        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            margin: 0 10px;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .sidebar .nav-link i {
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.15) 0%, rgba(139, 92, 246, 0.1) 100%);
            color: white;
            border-left-color: var(--primary);
            padding-left: 24px;
        }
        
        .sidebar .nav-link:hover i {
            transform: translateX(4px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.3) 0%, rgba(37, 99, 235, 0.1) 100%);
            color: #3b82f6;
            border-left-color: #3b82f6;
            font-weight: 600;
            padding-left: 24px;
        }
        
        .sidebar .nav-link.active i {
            color: #3b82f6;
        }
        
        /* Sidebar sections */
        .nav-section {
            padding: 15px 15px 5px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }
        
        /* Main content */
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
        <div class="logo-section">
            <h5><i class="bi bi-hammer-wrench"></i> FerrePro</h5>
        </div>
        <nav class="nav flex-column">
            <!-- Core Section -->
            <div class="nav-section">Inicio</div>
            <a class="nav-link" href="?page=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
            
            <!-- Ventas Section -->
            <div class="nav-section">Ventas & Operaciones</div>
            <a class="nav-link" href="?page=pos"><i class="bi bi-cart-check"></i> Punto de Venta</a>
            <a class="nav-link" href="?page=sales"><i class="bi bi-graph-up"></i> Ventas</a>
            <a class="nav-link" href="?page=quotes"><i class="bi bi-file-earmark-text"></i> Cotizaciones</a>
            <a class="nav-link" href="?page=credit_notes"><i class="bi bi-file-earmark-minus"></i> Notas Crédito</a>
            <a class="nav-link" href="?page=purchases"><i class="bi bi-bag"></i> Compras</a>
            
            <!-- Inventario Section -->
            <div class="nav-section">Inventario</div>
            <a class="nav-link" href="?page=products"><i class="bi bi-box2"></i> Productos</a>
            <a class="nav-link" href="?page=categories"><i class="bi bi-tags"></i> Categorías</a>
            
            <!-- Gestión Section -->
            <div class="nav-section">Gestión</div>
            <a class="nav-link" href="?page=clients"><i class="bi bi-people"></i> Clientes</a>
            <a class="nav-link" href="?page=providers"><i class="bi bi-person-badge"></i> Proveedores</a>
            <a class="nav-link" href="?page=receivable"><i class="bi bi-cash-coin"></i> Cuentas por Cobrar</a>
            <a class="nav-link" href="?page=payable"><i class="bi bi-credit-card"></i> Cuentas por Pagar</a>
            
            <!-- Finanzas Section -->
            <div class="nav-section">Finanzas</div>
            <a class="nav-link" href="?page=expenses"><i class="bi bi-wallet2"></i> Gastos</a>
            <a class="nav-link" href="?page=cash"><i class="bi bi-safe"></i> Caja</a>
            <a class="nav-link" href="?page=reports"><i class="bi bi-bar-chart"></i> Reportes</a>
            
            <!-- Sistema Section -->
            <div class="nav-section">Sistema</div>
            <a class="nav-link" href="?page=backup"><i class="bi bi-cloud-arrow-down"></i> Backup</a>
            <a class="nav-link" href="?page=users"><i class="bi bi-shield-lock"></i> Usuarios</a>
            <a class="nav-link" href="?page=settings"><i class="bi bi-gear"></i> Configuración</a>
            <a class="nav-link" href="?page=logout"><i class="bi bi-box-arrow-right"></i> Salir</a>
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
    <script>
        // Set active nav link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 'dashboard';
            
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                const href = link.getAttribute('href');
                const linkPage = new URLSearchParams(href.split('?')[1]).get('page');
                
                if (linkPage === currentPage) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
