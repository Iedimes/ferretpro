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
<style>
.dashboard-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.25);
}
.dashboard-header h3 {
    font-weight: 700;
    margin-bottom: 5px;
    font-size: 1.8rem;
}
.dashboard-header p {
    opacity: 0.95;
    font-size: 0.95rem;
}
.stat-card {
    border: none;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}
.stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    right: -100px;
    width: 150px;
    height: 150px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    pointer-events: none;
}
.stat-card-icon {
    font-size: 3rem;
    opacity: 0.08;
    position: absolute;
    right: 15px;
    top: 15px;
}
.stat-content {
    position: relative;
    z-index: 2;
}
.stat-label {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    opacity: 0.8;
}
.stat-value {
    font-size: 2rem;
    font-weight: 800;
    margin: 10px 0;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.stat-meta {
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
    margin-top: 8px;
}
.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-top: 30px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 3px solid var(--primary);
    display: inline-block;
}
</style>

<div class="dashboard-header">
    <h3><i class="bi bi-speedometer2"></i> Dashboard</h3>
    <p>Resumen ejecutivo del día ' . date("d \\d\\e F \\d\\e Y") . '</p>
</div>

<!-- FILA 1: MÉTRICAS DE VENTAS -->
<h5 class="section-title"><i class="bi bi-graph-up-arrow"></i> Métricas de Ventas</h5>
<div class="row mb-4 mt-3">
    <div class="col-md-3">
        <a href="?page=sales" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--primary); background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-cart-check"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">Ventas Hoy</div>
                    <div class="stat-value">' . Format::money($totalVentasHoy) . '</div>
                    <div class="stat-meta text-primary"><i class="bi bi-arrow-right"></i> ' . $cantidadVentasHoy . ' transacciones</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=sales" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--success); background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-graph-up"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">Ventas del Mes</div>
                    <div class="stat-value">' . Format::money($totalVentasMes) . '</div>
                    <div class="stat-meta text-success"><i class="bi bi-arrow-right"></i> ' . $cantidadVentasMes . ' transacciones</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=expenses" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--secondary); background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">Gastos del Mes</div>
                    <div class="stat-value">' . Format::money($expensesMonth['total'] ?? 0) . '</div>
                    <div class="stat-meta text-secondary"><i class="bi bi-arrow-right"></i> gastos operativos</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-top: 5px solid var(--warning); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, transparent 100%);">
            <div class="stat-card-icon"><i class="bi bi-piggy-bank"></i></div>
            <div class="card-body stat-content">
                <div class="stat-label text-muted">Margen Estimado</div>
                <div class="stat-value">' . Format::money(max(0, $totalVentasMes - ($expensesMonth['total'] ?? 0))) . '</div>
                <div class="stat-meta text-warning"><i class="bi bi-arrow-right"></i> utilidad neta</div>
            </div>
        </div>
    </div>
</div>

<!-- FILA 2: CUENTAS TOTALES -->
<h5 class="section-title"><i class="bi bi-file-earmark-text"></i> Cuentas por Cobrar y Pagar</h5>
<div class="row mb-4 mt-3">
    <div class="col-md-3">
        <a href="?page=receivable" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--danger); background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-arrow-down-left"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">CxC Total</div>
                    <div class="stat-value">' . Format::money($totalDeuda) . '</div>
                    <div class="stat-meta text-danger"><i class="bi bi-arrow-right"></i> ' . $totalReceivableCount . ' cuentas</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=payable" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--warning); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-arrow-up-right"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">CxP Total</div>
                    <div class="stat-value">' . Format::money($totalPayableValue) . '</div>
                    <div class="stat-meta text-warning"><i class="bi bi-arrow-right"></i> ' . $totalPayableCount . ' cuentas</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=receivable&filter=10days" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--info); background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-calendar-event"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">CxC (10 días)</div>
                    <div class="stat-value text-info">' . Format::money($receivableSoon) . '</div>
                    <div class="stat-meta text-info"><i class="bi bi-arrow-right"></i> ' . $receivableCountSoon . ' próximas</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=payable&filter=10days" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--info); background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-calendar-event"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">CxP (10 días)</div>
                    <div class="stat-value text-info">' . Format::money($payableSoon) . '</div>
                    <div class="stat-meta text-info"><i class="bi bi-arrow-right"></i> ' . $payableCountSoon . ' próximas</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- FILA 3: ALERTAS Y OTROS -->
<h5 class="section-title"><i class="bi bi-exclamation-triangle"></i> Alertas y Gestión</h5>
<div class="row mb-4 mt-3">
    <div class="col-md-3">
        <a href="?page=receivable&filter=overdue" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--danger); background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-clock-history"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">CxC Vencidas</div>
                    <div class="stat-value text-danger">' . Format::money($receivableOverdue) . '</div>
                    <div class="stat-meta text-danger"><i class="bi bi-exclamation-circle"></i> ' . $receivableCountOverdue . ' vencidas</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=payable&filter=overdue" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--danger); background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-clock-history"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">CxP Vencidas</div>
                    <div class="stat-value text-danger">' . Format::money($payableOverdue) . '</div>
                    <div class="stat-meta text-danger"><i class="bi bi-exclamation-circle"></i> ' . $payableCountOverdue . ' vencidas</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=cash" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--dark); background: linear-gradient(135deg, rgba(30, 41, 59, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-safe"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">Caja</div>
                    <div class="stat-value">-</div>
                    <div class="stat-meta"><i class="bi bi-arrow-right"></i> Ver movimientos</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?page=products&sort=stock" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--warning); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">Stock Bajo</div>
                    <div class="stat-value text-warning">' . $stockBajo . '</div>
                    <div class="stat-meta text-warning"><i class="bi bi-arrow-right"></i> productos</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- FILA 4: BACKUP -->
<div class="row mb-4">
    <div class="col-md-3">
        <a href="?page=backup" class="text-decoration-none">
            <div class="card stat-card" style="border-top: 5px solid var(--info); background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, transparent 100%);">
                <div class="stat-card-icon"><i class="bi bi-cloud-download"></i></div>
                <div class="card-body stat-content">
                    <div class="stat-label text-muted">Backup</div>
                    <div class="stat-value">-</div>
                    <div class="stat-meta text-info"><i class="bi bi-arrow-right"></i> Respaldar BD</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- TABLAS INFORMATIVAS -->
<h5 class="section-title mt-5"><i class="bi bi-table"></i> Resumen de Actividades</h5>
<div class="row mb-4 mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-box2"></i> Productos Más Vendidos</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Producto</th><th>Cant.</th><th>Total</th></tr></thead>
                    <tbody>';
if (!empty($topProducts)) {
    foreach ($topProducts as $p) {
        $content .= '<tr><td>' . htmlspecialchars($p['name']) . '</td><td><span class="badge bg-info">' . $p['quantity_sold'] . '</span></td><td><strong>' . Format::money($p['total_vendido']) . '</strong></td></tr>';
    }
} else {
    $content .= '<tr><td colspan="3" class="text-center text-muted">Sin ventas este mes</td></tr>';
}
$content .= '</tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-person-check"></i> Ventas por Vendedor</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Vendedor</th><th>Ventas</th><th>Total</th></tr></thead>
                    <tbody>';
if (!empty($salesByUser)) {
    foreach ($salesByUser as $u) {
        $content .= '<tr><td>' . htmlspecialchars($u['name']) . '</td><td><span class="badge bg-success">' . $u['ventas'] . '</span></td><td><strong>' . Format::money($u['total']) . '</strong></td></tr>';
    }
} else {
    $content .= '<tr><td colspan="3" class="text-center text-muted">Sin ventas este mes</td></tr>';
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
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-receipt"></i> Últimas Ventas</h6>
                <a href="?page=sales" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i> Ver todas</a>
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>';

if (count($recentSales) == 0) {
    $content .= '<tr><td colspan="7" class="text-center text-muted">No hay ventas</td></tr>';
} else {
    foreach ($recentSales as $s) {
        $statusClass = $s['status'] === 'pagada' ? 'success' : 'warning';
        $typeLabel = $s['type'] === 'contado' ? 'Contado' : 'Crédito';
        $typeBadge = $s['type'] === 'contado' ? 'bg-success' : 'bg-info';
        
        $content .= '<tr>
            <td><strong>#' . $s['id'] . '</strong></td>
            <td>' . Format::date($s['created_at']) . '</td>
            <td>' . htmlspecialchars($s['client_name'] ?? 'Mostrador') . '</td>
            <td><strong>' . Format::money($s['total']) . '</strong></td>
            <td><span class="badge ' . $typeBadge . '">' . $typeLabel . '</span></td>
            <td><span class="badge bg-' . $statusClass . '">' . ($s['status'] === 'pagada' ? 'Pagada' : 'Pendiente') . '</span></td>
            <td><a href="?page=sales&action=print&id=' . $s['id'] . '" class="btn btn-sm btn-primary" target="_blank"><i class="bi bi-printer"></i></a></td>
        </tr>';
    }
}

$content .= '</tbody>
                </table>
            </div>
        </div>
    </div>
</div>';
