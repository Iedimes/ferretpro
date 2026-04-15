<?php 
$title = 'Cuentas por Pagar';
$pageTitle = 'Cuentas por Pagar';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=home" class="btn btn-secondary">← Volver</a>
    </div>
</div>';

$content .= '
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cuentas por Pagar</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Proveedor</th>
                    <th>Monto</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

if (count($accounts) == 0) {
    $content .= '<tr><td colspan="6" class="text-center">No hay cuentas por pagar</td></tr>';
} else {
    foreach ($accounts as $ac) {
        $statusClass = $ac['status'] === 'pendiente' ? 'warning' : 'success';
        $statusLabel = $ac['status'] === 'pendiente' ? 'Pendiente' : 'Pagado';
        
        $content .= '<tr>
            <td>#' . $ac['id'] . '</td>
            <td>' . htmlspecialchars($ac['provider_name']) . '</td>
            <td>' . Format::money($ac['amount']) . '</td>
            <td>' . Format::date($ac['due_date']) . '</td>
            <td><span class="badge bg-' . $statusClass . '">' . $statusLabel . '</span></td>
            <td>' . ($ac['status'] === 'pendiente' ? '<a href="?page=payable&action=pay&id=' . $ac['id'] . '" class="btn btn-sm btn-success" onclick="return confirm(\'Marcar como pagado?\')">Pagar</a>' : '-') . '</td>
        </tr>';
    }
}

$content .= '</tbody>
        </table>
    </div>
</div>';

if (count($accounts) > 0) {
    $totalPendiente = array_sum(array_map(fn($a) => $a['status'] === 'pendiente' ? $a['amount'] : 0, $accounts));
    $content .= '
<div class="alert alert-info mt-3">
    <strong>Total pendiente: ' . Format::money($totalPendiente) . '</strong>
</div>';
}