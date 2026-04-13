<?php 
$title = 'Reportes';
$pageTitle = 'Reportes y Estadísticas';

$type = $_GET['type'] ?? 'sales';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

$content = '
<div class="row mb-4">
    <div class="col-md-3">
        <label class="form-label">Tipo de Reporte</label>
        <select id="reportType" class="form-select">
            <option value="sales" ' . ($type === 'sales' ? 'selected' : '') . '>Ventas</option>
            <option value="products" ' . ($type === 'products' ? 'selected' : '') . '>Productos</option>
            <option value="clients" ' . ($type === 'clients' ? 'selected' : '') . '>Clientes</option>
            <option value="stock" ' . ($type === 'stock' ? 'selected' : '') . '>Stock</option>
            <option value="earnings" ' . ($type === 'earnings' ? 'selected' : '') . '>Ganancias</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Desde</label>
        <input type="date" id="dateFrom" class="form-control" value="' . $dateFrom . '">
    </div>
    <div class="col-md-3">
        <label class="form-label">Hasta</label>
        <input type="date" id="dateTo" class="form-control" value="' . $dateTo . '">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="button" onclick="loadReport()" class="btn btn-primary w-100">Generar</button>
    </div>
</div>

<script>
function loadReport() {
    var type = document.getElementById("reportType").value;
    var dateFrom = document.getElementById("dateFrom").value;
    var dateTo = document.getElementById("dateTo").value;
    window.location.href = "?page=reports&type=" + type + "&date_from=" + dateFrom + "&date_to=" + dateTo;
}

document.getElementById("reportType").addEventListener("change", loadReport);
</script>';

if ($type === 'sales') {
    $salesData = db()->query("
        SELECT DATE(created_at) as fecha, COUNT(*) as cantidad, SUM(total) as total
        FROM sales
        WHERE DATE(created_at) BETWEEN '$dateFrom' AND '$dateTo'
        GROUP BY DATE(created_at)
        ORDER BY fecha
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $totalVentas = array_sum(array_map(fn($s) => $s['total'], $salesData));
    $totalCantidad = array_sum(array_map(fn($s) => $s['cantidad'], $salesData));
    $promedio = $totalCantidad > 0 ? $totalVentas / $totalCantidad : 0;
    
    $content .= '
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Ventas</h6>
                    <h3>' . Format::money($totalVentas) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <h6 class="text-muted">Transacciones</h6>
                    <h3>' . $totalCantidad . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card warning">
                <div class="card-body">
                    <h6 class="text-muted">Ticket Promedio</h6>
                    <h3>' . Format::money($promedio) . '</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detalle de Ventas por Día</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Promedio</th>
                    </tr>
                </thead>
                <tbody>';
     foreach ($salesData as $s) {
         $content .= '<tr>
             <td>' . Format::date($s['fecha']) . '</td>
             <td>' . $s['cantidad'] . '</td>
             <td>' . Format::money($s['total']) . '</td>
             <td>' . Format::money($s['total'] / $s['cantidad']) . '</td>
         </tr>';
     }
    $content .= '</tbody>
            </table>
        </div>
    </div>';
}

if ($type === 'products') {
    $productsData = db()->query("
        SELECT p.id, p.name, p.code, SUM(sd.quantity) as cantidad_vendida, SUM(sd.subtotal) as total_vendido
        FROM sale_details sd
        JOIN products p ON sd.product_id = p.id
        JOIN sales s ON sd.sale_id = s.id
        WHERE s.created_at BETWEEN '$dateFrom' AND '$dateTo'
        GROUP BY p.id
        ORDER BY total_vendido DESC
        LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $content .= '
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Productos Más Vendidos</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total Vendido</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($productsData as $p) {
         $content .= '<tr>
             <td>' . $p['code'] . '</td>
             <td>' . htmlspecialchars($p['name']) . '</td>
             <td>' . $p['cantidad_vendida'] . '</td>
             <td>' . Format::money($p['total_vendido']) . '</td>
         </tr>';
     }
    $content .= '</tbody>
            </table>
        </div>
    </div>';
}

if ($type === 'stock') {
    $lowStock = db()->query("SELECT * FROM products WHERE active = 1 AND stock <= min_stock ORDER BY stock")->fetchAll(PDO::FETCH_ASSOC);
    $noStock = db()->query("SELECT * FROM products WHERE active = 1 AND stock = 0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $overStock = db()->query("SELECT * FROM products WHERE active = 1 AND stock > 50 ORDER BY stock DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    
    $content .= '
    <div class="row">
        <div class="col-md-4">
            <div class="card stat-card danger">
                <div class="card-body">
                    <h6 class="text-muted">Stock Bajo</h6>
                    <h3>' . count($lowStock) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card warning">
                <div class="card-body">
                    <h6 class="text-muted">Sin Stock</h6>
                    <h3>' . count($noStock) . '</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Productos con Stock Bajo</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($lowStock as $p) {
        $content .= '<tr>
            <td>' . $p['code'] . '</td>
            <td>' . htmlspecialchars($p['name']) . '</td>
            <td class="text-danger fw-bold">' . $p['stock'] . '</td>
            <td>' . $p['min_stock'] . '</td>
        </tr>';
    }
    $content .= '</tbody>
            </table>
        </div>
    </div>';
}

if ($type === 'earnings') {
    $earnings = db()->query("
        SELECT 
            SUM(sd.subtotal) as ingresos,
            SUM(sd.quantity * p.cost_price) as costos
        FROM sale_details sd
        JOIN products p ON sd.product_id = p.id
        JOIN sales s ON sd.sale_id = s.id
        WHERE s.created_at BETWEEN '$dateFrom' AND '$dateTo' AND s.status = 'pagada'
    ")->fetch(PDO::FETCH_ASSOC);
    
    $ganancia = $earnings['ingresos'] - $earnings['costos'];
    $margen = $earnings['ingresos'] > 0 ? ($ganancia / $earnings['ingresos']) * 100 : 0;
    
    $content .= '
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <h6 class="text-muted">Ingresos</h6>
                    <h3>' . Format::money($earnings['ingresos']) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card danger">
                <div class="card-body">
                    <h6 class="text-muted">Costos</h6>
                    <h3>' . Format::money($earnings['costos']) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card primary">
                <div class="card-body">
                    <h6 class="text-muted">Ganancia Neta</h6>
                    <h3>' . Format::money($ganancia) . '</h3>
                    <small class="text-muted">Margen: ' . Format::percentage($margen) . '</small>
                </div>
            </div>
        </div>
    </div>';
}


