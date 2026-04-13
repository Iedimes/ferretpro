<?php 
$title = 'Dashboard';
$pageTitle = 'Dashboard';

$totalVentasHoy = $salesToday['COALESCE(SUM(total), 0)'] ?? 0;
$cantidadVentasHoy = $salesToday['COUNT(*)'] ?? 0;
$totalVentasMes = $salesMonth['COALESCE(SUM(total), 0)'] ?? 0;
$cantidadVentasMes = $salesMonth['COUNT(*)'] ?? 0;
$stockBajo = $productsLow['COUNT(*)'] ?? 0;
$clientesDeuda = $clientsDebt['COUNT(*)'] ?? 0;
$totalDeuda = $clientsDebt['COALESCE(SUM(balance), 0)'] ?? 0;

$content = '
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: var(--primary);">
            <div class="card-body">
                <h6 class="text-muted">Ventas Hoy</h6>
                <h3 class="mb-0">' . Format::money($totalVentasHoy) . '</h3>
                <small class="text-muted">' . $cantidadVentasHoy . ' transacciones</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: var(--success);">
            <div class="card-body">
                <h6 class="text-muted">Ventas del Mes</h6>
                <h3 class="mb-0">' . Format::money($totalVentasMes) . '</h3>
                <small class="text-muted">' . $cantidadVentasMes . ' transacciones</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: var(--warning);">
            <div class="card-body">
                <h6 class="text-muted">Stock Bajo</h6>
                <h3 class="mb-0">' . $stockBajo . '</h3>
                <small class="text-muted">productos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: var(--danger);">
            <div class="card-body">
                <h6 class="text-muted">Cuentas por Cobrar</h6>
                <h3 class="mb-0">' . Format::money($totalDeuda) . '</h3>
                <small class="text-muted">' . $clientesDeuda . ' clientes</small>
            </div>
        </div>
    </div>
</div>

';
if (!empty($overdueReceivables)) {
    $content .= '
<div class="row mt-3">
    <div class="col-12">
        <div class="alert alert-danger">
            <h5><i class="bi bi-exclamation-triangle-fill"></i> Alerta: Créditos Vencidos</h5>
            <table class="table table-sm table-danger mb-0 mt-2">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Venta #</th>
                        <th>Monto</th>
                        <th>Vencimiento</th>
                        <th>Días Vencido</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($overdueReceivables as $rec) {
        $dueDate = new DateTime($rec['due_date']);
        $today = new DateTime();
        $daysOverdue = $today->diff($dueDate)->days;
        
        $content .= '<tr>
            <td><strong>' . htmlspecialchars($rec['client_name']) . '</strong></td>
            <td>' . $rec['sale_id'] . '</td>
            <td>' . Format::money($rec['amount']) . '</td>
            <td>' . Format::date($rec['due_date']) . '</td>
            <td><span class="badge bg-danger">' . $daysOverdue . ' días</span></td>
            <td><a href="?page=receivable&action=pay&id=' . $rec['id'] . '" class="btn btn-sm btn-danger">Cobrar</a></td>
        </tr>';
    }
    $content .= '</tbody>
            </table>
        </div>
    </div>
</div>';
}

$content .= '

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Últimas Ventas</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>';
                    if (empty($recentSales)) {
                        $content .= '<tr><td colspan="6" class="text-center text-muted">No hay ventas registradas</td></tr>';
                    } else {
                        foreach ($recentSales as $sale) {
                            $content .= '<tr>
                                <td>' . $sale['id'] . '</td>
                                <td>' . Format::datetime($sale['created_at']) . '</td>
                                <td>' . ($sale['client_name'] ?? 'Mostrador') . '</td>
                                <td>' . Format::money($sale['total']) . '</td>
                                <td><span class="badge bg-' . ($sale['type'] === 'contado' ? 'info' : 'primary') . '">' . $sale['type'] . '</span></td>
                                <td><span class="badge bg-' . (($sale['status'] === 'pagada' || $sale['status'] === 'completada') ? 'success' : 'warning') . '">' . $sale['status'] . '</span></td>
                            </tr>';
                        }
                    }
                    $content .= '</tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="?page=pos" class="btn btn-outline-primary w-100 py-3">Nueva Venta</a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=products" class="btn btn-outline-success w-100 py-3">Productos</a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=clients" class="btn btn-outline-warning w-100 py-3">Clientes</a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=receivable" class="btn btn-outline-danger w-100 py-3">Cobrar Deudas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';
