<?php 
$title = 'Reportes';
$pageTitle = 'Reportes y Estadísticas';

$type = $_GET['type'] ?? 'sales';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$content = '
<div class="row mb-4">
    <div class="col-md-2">
        <label class="form-label">Tipo de Reporte</label>
        <select id="reportType" class="form-select">
            <option value="sales" ' . ($type === 'sales' ? 'selected' : '') . '>Ventas</option>
            <option value="products" ' . ($type === 'products' ? 'selected' : '') . '>Productos</option>
            <option value="clients" ' . ($type === 'clients' ? 'selected' : '') . '>Clientes</option>
            <option value="stock" ' . ($type === 'stock' ? 'selected' : '') . '>Stock</option>
            <option value="earnings" ' . ($type === 'earnings' ? 'selected' : '') . '>Ganancias</option>
            <option value="receivables" ' . ($type === 'receivables' ? 'selected' : '') . '>Cuentas por Cobrar</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Buscar</label>
        <input type="text" id="searchProduct" class="form-control" placeholder="Código o descripción..." value="' . htmlspecialchars($search) . '">
    </div>
    <div class="col-md-2" id="dateFromContainer">
        <label class="form-label">Desde</label>
        <input type="date" id="dateFrom" class="form-control" value="' . $dateFrom . '">
    </div>
    <div class="col-md-2" id="dateToContainer">
        <label class="form-label">Hasta</label>
        <input type="date" id="dateTo" class="form-control" value="' . $dateTo . '">
    </div>
    <div class="col-md-2" id="stockStatusContainer" style="display: none;">
        <label class="form-label">Estado Stock</label>
        <select id="stockStatus" class="form-select">
            <option value="">Todos</option>
            <option value="low"' . ($_GET['stock_status'] === 'low' ? 'selected' : '') . '>Bajo</option>
            <option value="none"' . ($_GET['stock_status'] === 'none' ? 'selected' : '') . '>Sin Stock</option>
            <option value="over"' . ($_GET['stock_status'] === 'over' ? 'selected' : '') . '>Con Stock</option>
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="button" onclick="loadReport()" class="btn btn-primary me-2">Generar</button>
        <button type="button" onclick="clearFilters()" class="btn btn-outline-secondary">Todos</button>
    </div>
</div>

<script>
function updateFields() {
    var type = document.getElementById("reportType").value;
    var dateFromContainer = document.getElementById("dateFromContainer");
    var dateToContainer = document.getElementById("dateToContainer");
    var stockStatusContainer = document.getElementById("stockStatusContainer");
    var searchLabel = document.querySelector("label[for=\"searchProduct\"]");
    var searchInput = document.getElementById("searchProduct");
    
    if (type === "stock") {
        dateFromContainer.style.display = "none";
        dateToContainer.style.display = "none";
        stockStatusContainer.style.display = "block";
        if (searchLabel) searchLabel.textContent = "Buscar";
        if (searchInput) searchInput.placeholder = "Código o descripción...";
    } else if (type === "receivables") {
        dateFromContainer.style.display = "none";
        dateToContainer.style.display = "none";
        stockStatusContainer.style.display = "none";
        if (searchLabel) searchLabel.textContent = "Cliente";
        if (searchInput) searchInput.placeholder = "Buscar cliente...";
    } else {
        dateFromContainer.style.display = "block";
        dateToContainer.style.display = "block";
        stockStatusContainer.style.display = "none";
        if (searchLabel) searchLabel.textContent = "Buscar";
        if (searchInput) searchInput.placeholder = type === "products" ? "Código o descripción..." : "";
    }
}

function loadReport() {
    var type = document.getElementById("reportType").value;
    var search = document.getElementById("searchProduct").value;
    var dateFrom = document.getElementById("dateFrom").value;
    var dateTo = document.getElementById("dateTo").value;
    var stockStatus = document.getElementById("stockStatus") ? document.getElementById("stockStatus").value : "";
    var url = "?page=reports&type=" + type;
    if (search) url += "&search=" + encodeURIComponent(search);
    if (dateFrom) url += "&date_from=" + dateFrom;
    if (dateTo) url += "&date_to=" + dateTo;
    if (stockStatus) url += "&stock_status=" + stockStatus;
    window.location.href = url;
}

function clearFilters() {
    document.getElementById("searchProduct").value = "";
    document.getElementById("dateFrom").value = "";
    document.getElementById("dateTo").value = "";
    if (document.getElementById("stockStatus")) document.getElementById("stockStatus").value = "";
    loadReport();
}

document.getElementById("reportType").addEventListener("change", function() { updateFields(); loadReport(); });
document.getElementById("searchProduct").addEventListener("keypress", function(e) {
    if (e.key === "Enter") loadReport();
});
updateFields();
</script>';

if ($type === 'sales') {
    $whereClause = "1=1";
    $params = [];
    
    if ($dateFrom && $dateTo) {
        $whereClause .= " AND DATE(created_at) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }
    
    $salesData = db()->prepare("
        SELECT DATE(created_at) as fecha, COUNT(*) as cantidad, SUM(total) as total
        FROM sales
        WHERE $whereClause
        GROUP BY DATE(created_at)
        ORDER BY fecha
    ");
    $salesData->execute($params);
    $salesData = $salesData->fetchAll(PDO::FETCH_ASSOC);
    
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
    $whereClause = "1=1";
    $params = [];
    
    if ($dateFrom && $dateTo) {
        $whereClause .= " AND s.created_at BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }
    
    if ($search) {
        $whereClause .= " AND (p.code LIKE ? OR p.name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $productsData = db()->prepare("
        SELECT p.id, p.name, p.code, SUM(sd.quantity) as cantidad_vendida, SUM(sd.subtotal) as total_vendido
        FROM sale_details sd
        JOIN products p ON sd.product_id = p.id
        JOIN sales s ON sd.sale_id = s.id
        WHERE $whereClause
        GROUP BY p.id
        ORDER BY total_vendido DESC
        LIMIT 50
    ");
    $productsData->execute($params);
    $productsData = $productsData->fetchAll(PDO::FETCH_ASSOC);
    
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
    $stockStatus = $_GET['stock_status'] ?? '';
    $stockWhere = "active = 1";
    $stockOrder = "ORDER BY stock";
    $stockParams = [];
    
    if ($search) {
        $stockWhere .= " AND (code LIKE ? OR name LIKE ?)";
        $searchTerm = "%$search%";
        $stockParams[] = $searchTerm;
        $stockParams[] = $searchTerm;
    }
    
    if ($stockStatus === 'low') {
        $stockWhere .= " AND stock <= min_stock AND stock > 0";
    } elseif ($stockStatus === 'none') {
        $stockWhere .= " AND stock = 0";
    } elseif ($stockStatus === 'over') {
        $stockWhere .= " AND stock > min_stock";
    }
    
    $stmt = db()->prepare("SELECT * FROM products WHERE $stockWhere $stockOrder");
    $stmt->execute($stockParams);
    $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $lowStock = array_filter($stockData, fn($p) => $p['stock'] <= $p['min_stock'] && $p['stock'] > 0);
    $noStock = array_filter($stockData, fn($p) => $p['stock'] == 0);
    $overStock = array_filter($stockData, fn($p) => $p['stock'] > $p['min_stock']);
    
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
        <div class="col-md-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <h6 class="text-muted">Con Stock</h6>
                    <h3>' . count($overStock) . '</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Todos los Productos (Ordenados por Stock)</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($stockData as $p) {
        $statusClass = $p['stock'] == 0 ? 'text-danger' : ($p['stock'] <= $p['min_stock'] ? 'text-warning' : 'text-success');
        $statusLabel = $p['stock'] == 0 ? 'Sin Stock' : ($p['stock'] <= $p['min_stock'] ? 'Bajo' : 'OK');
        $content .= '<tr>
            <td>' . $p['code'] . '</td>
            <td>' . htmlspecialchars($p['name']) . '</td>
            <td class="' . $statusClass . ' fw-bold">' . $p['stock'] . '</td>
            <td>' . $p['min_stock'] . '</td>
            <td><span class="badge bg-' . ($p['stock'] == 0 ? 'danger' : ($p['stock'] <= $p['min_stock'] ? 'warning' : 'success')) . '">' . $statusLabel . '</span></td>
        </tr>';
    }
    $content .= '</tbody>
            </table>
        </div>
    </div>';
}

if ($type === 'earnings') {
    $whereClause = "s.status = 'pagada'";
    $params = [];
    
    if ($dateFrom && $dateTo) {
        $whereClause .= " AND s.created_at BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }
    
    $earningsStmt = db()->prepare("
        SELECT 
            COALESCE(SUM(sd.subtotal), 0) as ingresos,
            COALESCE(SUM(sd.quantity * p.cost_price), 0) as costos
        FROM sale_details sd
        JOIN products p ON sd.product_id = p.id
        JOIN sales s ON sd.sale_id = s.id
        WHERE $whereClause
    ");
    $earningsStmt->execute($params);
    $earnings = $earningsStmt->fetch(PDO::FETCH_ASSOC);
    
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

if ($type === 'receivables') {
    $clientSearch = $_GET['search'] ?? '';
    
    $whereClause = "ar.status != 'cancelada'";
    $params = [];
    
    if ($clientSearch) {
        $whereClause .= " AND c.name LIKE ?";
        $params[] = "%$clientSearch%";
    }
    
    $receivablesStmt = db()->prepare("
        SELECT ar.*, c.name as client_name, c.document, c.phone,
               s.created_at as sale_date
        FROM accounts_receivable ar
        JOIN clients c ON ar.client_id = c.id
        LEFT JOIN sales s ON ar.sale_id = s.id
        WHERE $whereClause
        ORDER BY ar.due_date ASC
    ");
    $receivablesStmt->execute($params);
    $receivables = $receivablesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPending = array_sum(array_map(fn($r) => $r['amount'] - $r['paid_amount'], $receivables));
    $totalOverdue = 0;
    $today = date('Y-m-d');
    foreach ($receivables as $r) {
        if ($r['due_date'] < $today && $r['status'] === 'pendiente') {
            $totalOverdue += $r['amount'] - $r['paid_amount'];
        }
    }
    
    $content .= '
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card warning">
                <div class="card-body">
                    <h6 class="text-muted">Total Pendiente</h6>
                    <h3>' . Format::money($totalPending) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card danger">
                <div class="card-body">
                    <h6 class="text-muted">Vencido</h6>
                    <h3>' . Format::money($totalOverdue) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card primary">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas</h6>
                    <h3>' . count($receivables) . '</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Estado de Cuentas por Cliente</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Documento</th>
                        <th>Venta</th>
                        <th>Fecha Venta</th>
                        <th>Monto</th>
                        <th>Pagado</th>
                        <th>Pendiente</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($receivables as $r) {
        $pending = $r['amount'] - $r['paid_amount'];
        $isOverdue = $r['due_date'] < $today && $r['status'] === 'pendiente';
        $statusBadge = $r['status'] === 'cancelada' ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-warning');
        $statusLabel = $r['status'] === 'cancelada' ? 'Cancelada' : ($isOverdue ? 'Vencido' : 'Pendiente');
        
        $content .= '<tr>
            <td>' . htmlspecialchars($r['client_name']) . '</td>
            <td>' . htmlspecialchars($r['document']) . '</td>
            <td>#' . $r['sale_id'] . '</td>
            <td>' . Format::date($r['sale_date']) . '</td>
            <td>' . Format::money($r['amount']) . '</td>
            <td>' . Format::money($r['paid_amount']) . '</td>
            <td class="fw-bold">' . Format::money($pending) . '</td>
            <td>' . Format::date($r['due_date']) . '</td>
            <td><span class="badge ' . $statusBadge . '">' . $statusLabel . '</span></td>
        </tr>';
    }
    $content .= '</tbody>
            </table>
        </div>
    </div>';
}

if ($type === 'clients') {
    $clientSearch = $_GET['search'] ?? '';
    
    $whereClause = "active = 1";
    $params = [];
    
    if ($clientSearch) {
        $whereClause .= " AND (name LIKE ? OR document LIKE ? OR email LIKE ?)";
        $params = ["%$clientSearch%", "%$clientSearch%", "%$clientSearch%"];
    }
    
    $clientsStmt = db()->prepare("
        SELECT c.*, 
               COUNT(DISTINCT ar.id) as accounts_count,
               SUM(CASE WHEN ar.status = 'pendiente' THEN ar.amount - ar.paid_amount ELSE 0 END) as total_pending
        FROM clients c
        LEFT JOIN accounts_receivable ar ON c.id = ar.client_id
        WHERE $whereClause
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $clientsStmt->execute($params);
    $clients = $clientsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalClients = count($clients);
    $totalCredit = array_sum(array_map(fn($c) => $c['credit_limit'], $clients));
    $totalBalance = array_sum(array_map(fn($c) => $c['balance'], $clients));
    $totalPending = array_sum(array_map(fn($c) => $c['total_pending'] ?? 0, $clients));
    
    $content .= '
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <h6 class="text-muted">Total de Clientes</h6>
                    <h3>' . $totalClients . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <h6 class="text-muted">Límite de Crédito Total</h6>
                    <h3>' . Format::money($totalCredit) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card info">
                <div class="card-body">
                    <h6 class="text-muted">Saldo Utilizado</h6>
                    <h3>' . Format::money($totalBalance) . '</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas Pendientes</h6>
                    <h3>' . Format::money($totalPending) . '</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people"></i> Listado de Clientes</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Categoría</th>
                        <th>Límite Crédito</th>
                        <th>Saldo</th>
                        <th>Cuentas</th>
                        <th>Pendiente</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($clients as $c) {
        $available = $c['credit_limit'] - $c['balance'];
        $balanceClass = $c['balance'] >= $c['credit_limit'] ? 'text-danger fw-bold' : '';
        $categoryBadge = match($c['category']) {
            'mayorista' => 'bg-primary',
            'minorista' => 'bg-info',
            'distribuidor' => 'bg-success',
            default => 'bg-secondary'
        };
        
        $content .= '<tr>
            <td><strong>' . htmlspecialchars($c['name']) . '</strong></td>
            <td>' . htmlspecialchars($c['document'] ?? '-') . '</td>
            <td>' . htmlspecialchars($c['phone'] ?? '-') . '</td>
            <td>' . htmlspecialchars($c['email'] ?? '-') . '</td>
            <td><span class="badge ' . $categoryBadge . '">' . ucfirst($c['category']) . '</span></td>
            <td>' . Format::money($c['credit_limit']) . '</td>
            <td class="' . $balanceClass . '">' . Format::money($c['balance']) . '</td>
            <td><span class="badge bg-secondary">' . ($c['accounts_count'] ?? 0) . '</span></td>
            <td>' . Format::money($c['total_pending'] ?? 0) . '</td>
        </tr>';
    }
    
    $content .= '</tbody>
            </table>
        </div>
    </div>';
}

?>
