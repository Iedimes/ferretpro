<?php 
$title = 'Gastos';
$pageTitle = 'Gastos Operativos';

$total = count($expenses) > 0 ? array_sum(array_map(fn($e) => $e['amount'], $expenses)) : 0;

$content = '
<div class="mb-4">
    <a href="?page=dashboard" class="btn btn-nav-back me-2"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
</div>

<h4 class="mb-4"><i class="bi bi-cash-coin"></i> ' . htmlspecialchars($pageTitle) . '</h4>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card" style="border-top: 5px solid var(--danger); background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-danger mb-1"><i class="bi bi-graph-down"></i> Total de Gastos</h6>
                <h3 class="mb-0 text-danger" style="font-size: 2rem; font-weight: 700;">' . Format::money($total) . '</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" style="border-top: 5px solid var(--primary); background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), transparent);">
            <div class="card-body">
                <h6 class="text-primary mb-1"><i class="bi bi-receipt"></i> Cantidad de Gastos</h6>
                <h3 class="mb-0 text-primary" style="font-size: 2rem; font-weight: 700;">' . count($expenses) . '</h3>
                <a href="?page=expenses&action=new" class="btn btn-sm btn-primary mt-3"><i class="bi bi-plus-circle"></i> Nuevo Gasto</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-table"></i> Detalle de Gastos</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Método</th>
                        <th>Monto</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>';

if (count($expenses) == 0) {
    $content .= '<tr><td colspan="7" class="text-center text-muted">No hay gastos registrados</td></tr>';
} else {
    foreach ($expenses as $e) {
        $methodLabel = $e['payment_method'] === 'efectivo' ? 'Efectivo' : ($e['payment_method'] === 'transferencia' ? 'Transferencia' : 'Banco');
        $methodBadge = $e['payment_method'] === 'efectivo' ? 'bg-success' : ($e['payment_method'] === 'transferencia' ? 'bg-info' : 'bg-secondary');
        
        $content .= '<tr>
            <td><strong>#' . $e['id'] . '</strong></td>
            <td>' . Format::date($e['date']) . '</td>
            <td><span class="badge bg-warning">' . htmlspecialchars($e['category']) . '</span></td>
            <td>' . htmlspecialchars($e['description'] ?? '-') . '</td>
            <td><span class="badge ' . $methodBadge . '">' . $methodLabel . '</span></td>
            <td><strong class="text-danger">' . Format::money($e['amount']) . '</strong></td>
            <td>' . htmlspecialchars($e['user_name']) . '</td>
        </tr>';
    }
}

$content .= '</tbody>
                </table>
            </div>
    </div>
</div>';