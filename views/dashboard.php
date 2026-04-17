<?php 
$title = 'Dashboard';
$pageTitle = 'Dashboard';

$totalVentasHoy = $salesToday['COALESCE(SUM(total), 0)'] ?? 0;
$cantidadVentasHoy = $salesToday['COUNT(*)'] ?? 0;
$totalVentasMes = $salesMonth['COALESCE(SUM(total), 0)'] ?? 0;
$cantidadVentasMes = $salesMonth['COUNT(*)'] ?? 0;
$stockBajo = $productsLow['COUNT(*)'] ?? 0;
$clientesDeuda = $clientsDebt['COUNT(*)'] ?? 0;

// Calcular total CxC desde accounts_receivable
$totalDeuda = 0;
$totalReceivableCount = 0;
if (!empty($overdueReceivables)) {
    foreach ($overdueReceivables as $rec) {
        $totalDeuda += $rec['amount'];
        $totalReceivableCount++;
    }
}

// Calcular total CxP desde accounts_payable
$totalPayableValue = 0;
$totalPayableCount = 0;
if (!empty($overduePayables)) {
    foreach ($overduePayables as $pay) {
        $totalPayableValue += $pay['amount'];
        $totalPayableCount++;
    }
}

// Calcular cuentas por cobrar próximos 10 días (solo las que NO están vencidas)
$receivableSoon = 0;
$receivableCountSoon = 0;
if (!empty($overdueReceivables)) {
    $today = new DateTime();
    foreach ($overdueReceivables as $rec) {
        $dueDate = new DateTime($rec['due_date']);
        // Solo incluir cuentas que NO están vencidas Y vencen en los próximos 10 días
        if ($dueDate >= $today && $today->diff($dueDate)->days <= 10) {
            $receivableSoon += $rec['amount'];
            $receivableCountSoon++;
        }
    }
}

// Calcular cuentas por pagar próximos 10 días (solo las que NO están vencidas)
$payableSoon = 0;
$payableCountSoon = 0;
if (!empty($overduePayables)) {
    $today = new DateTime();
    foreach ($overduePayables as $pay) {
        $dueDate = new DateTime($pay['due_date']);
        // Solo incluir cuentas que NO están vencidas Y vencen en los próximos 10 días
        if ($dueDate >= $today && $today->diff($dueDate)->days <= 10) {
            $payableSoon += $pay['amount'];
            $payableCountSoon++;
        }
    }
}

// Calcular cuentas vencidas (ya pasadas)
$receivableOverdue = 0;
$receivableCountOverdue = 0;
if (!empty($overdueReceivables)) {
    foreach ($overdueReceivables as $rec) {
        $dueDate = new DateTime($rec['due_date']);
        $today = new DateTime();
        if ($today > $dueDate) {
            $receivableOverdue += $rec['amount'];
            $receivableCountOverdue++;
        }
    }
}

$payableOverdue = 0;
$payableCountOverdue = 0;
if (!empty($overduePayables)) {
    foreach ($overduePayables as $pay) {
        $dueDate = new DateTime($pay['due_date']);
        $today = new DateTime();
        if ($today > $dueDate) {
            $payableOverdue += $pay['amount'];
            $payableCountOverdue++;
        }
    }
}

$content = '
<div class="row mb-4">
    <div class="col-md-3">
        <a href="?page=sales" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--primary);">
                <div class="card-body">
                    <h6 class="text-muted">Ventas Hoy</h6>
                    <h3 class="mb-0">' . Format::money($totalVentasHoy) . '</h3>
                    <small class="text-muted">' . $cantidadVentasHoy . ' trans.</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=sales" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--success);">
                <div class="card-body">
                    <h6 class="text-muted">Ventas del Mes</h6>
                    <h3 class="mb-0">' . Format::money($totalVentasMes) . '</h3>
                    <small class="text-muted">' . $cantidadVentasMes . ' trans.</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=products&sort=stock" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--warning);">
                <div class="card-body">
                    <h6 class="text-muted">Stock Bajo</h6>
                    <h3 class="mb-0">' . $stockBajo . '</h3>
                    <small class="text-muted">productos</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=receivable" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--danger);">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas por Cobrar (Total)</h6>
                    <h3 class="mb-0">' . Format::money($totalDeuda) . '</h3>
                    <small class="text-muted">' . $totalReceivableCount . ' cuentas</small>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <a href="?page=receivable&filter=10days" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--warning);">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas por Cobrar (10 días)</h6>
                    <h3 class="mb-0 text-warning">' . Format::money($receivableSoon) . '</h3>
                    <small class="text-muted">' . $receivableCountSoon . ' cuentas</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=payable" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--warning);">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas por Pagar (Total)</h6>
                    <h3 class="mb-0">' . Format::money($totalPayableValue) . '</h3>
                    <small class="text-muted">' . $totalPayableCount . ' cuentas</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=payable&filter=10days" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--warning);">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas por Pagar (10 días)</h6>
                    <h3 class="mb-0 text-warning">' . Format::money($payableSoon) . '</h3>
                    <small class="text-muted">' . $payableCountSoon . ' cuentas</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=expenses" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--secondary);">
                <div class="card-body">
                    <h6 class="text-muted">Gastos del Mes</h6>
                    <h3 class="mb-0">' . Format::money($expensesMonth['total'] ?? 0) . '</h3>
                    <small class="text-muted">operativos</small>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="border-color: var(--primary);">
            <div class="card-body">
                <h6 class="text-muted">Margen Estimado</h6>
                <h3 class="mb-0">' . Format::money(max(0, $totalVentasMes - ($expensesMonth['total'] ?? 0))) . '</h3>
                <small class="text-muted">ventas - gastos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <a href="?page=cash" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--dark);">
                <div class="card-body">
                    <h6 class="text-muted">Caja</h6>
                    <h3 class="mb-0"><i class="bi bi-safe"></i></h3>
                    <small class="text-muted">Ver movimientos</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=backup" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--info);">
                <div class="card-body">
                    <h6 class="text-muted">Backup</h6>
                    <h3 class="mb-0"><i class="bi bi-download"></i></h3>
                    <small class="text-muted">Respaldar BD</small>
                </div>
            </div>
        </a>
    </div>
</div>';

if ($receivableOverdue > 0 || $payableOverdue > 0) {
    $content .= '
<div class="row mb-4">
    <div class="col-md-3">
        <a href="?page=receivable&filter=overdue" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--danger);">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas por Cobrar Vencidas</h6>
                    <h3 class="mb-0 text-danger">' . Format::money($receivableOverdue) . '</h3>
                    <small class="text-muted">' . $receivableCountOverdue . ' cuentas</small>
                </div>
            </div>
        </a>
    </div>';
    if ($payableOverdue > 0) {
        $content .= '
    <div class="col-md-3">
        <a href="?page=payable&filter=overdue" class="text-decoration-none">
            <div class="card stat-card" style="border-color: var(--danger);">
                <div class="card-body">
                    <h6 class="text-muted">Cuentas por Pagar Vencidas</h6>
                    <h3 class="mb-0 text-danger">' . Format::money($payableOverdue) . '</h3>
                    <small class="text-muted">' . $payableCountOverdue . ' cuentas</small>
                </div>
            </div>
        </a>
    </div>';
    }
    $content .= '
</div>';
}

$content .= '
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Productos Más Vendidos del Mes</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Producto</th><th>Cantidad</th><th>Total</th></tr></thead>
                    <tbody>';
if (!empty($topProducts)) {
    foreach ($topProducts as $p) {
        $content .= '<tr><td>' . htmlspecialchars($p['name']) . '</td><td>' . $p['quantity_sold'] . '</td><td>' . Format::money($p['total_vendido']) . '</td></tr>';
    }
} else {
    $content .= '<tr><td colspan="3">Sin ventas este mes</td></tr>';
}
$content .= '</tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ventas por Vendedor</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Vendedor</th><th>Ventas</th><th>Total</th></tr></thead>
                    <tbody>';
if (!empty($salesByUser)) {
    foreach ($salesByUser as $u) {
        $content .= '<tr><td>' . htmlspecialchars($u['name']) . '</td><td>' . $u['ventas'] . '</td><td>' . Format::money($u['total']) . '</td></tr>';
    }
} else {
    $content .= '<tr><td colspan="3">Sin ventas este mes</td></tr>';
}
$content .= '</tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>';

if (count($recentSales) == 0) {
    $content .= '<tr><td colspan="7" class="text-center">No hay ventas</td></tr>';
} else {
    foreach ($recentSales as $s) {
        $statusClass = $s['status'] === 'pagada' ? 'success' : 'warning';
        
        $content .= '<tr>
            <td>#' . $s['id'] . '</td>
            <td>' . Format::date($s['created_at']) . '</td>
            <td>' . htmlspecialchars($s['client_name'] ?? 'Mostrador') . '</td>
            <td>' . Format::money($s['total']) . '</td>
            <td>' . ($s['type'] === 'contado' ? 'Contado' : 'Crédito') . '</td>
            <td><span class="badge bg-' . $statusClass . '">' . ($s['status'] === 'pagada' ? 'Pagada' : 'Pendiente') . '</span></td>
            <td><a href="?page=sales&action=print&id=' . $s['id'] . '" class="btn btn-sm btn-primary" target="_blank">Imprimir</a></td>
        </tr>';
    }
}

$content .= '</tbody>
                </table>
            </div>
        </div>
    </div>
</div>';