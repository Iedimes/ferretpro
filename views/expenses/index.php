<?php 
$title = 'Gastos';
$pageTitle = 'Gastos Operativos';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=home" class="btn btn-secondary">← Volver</a>
    </div>
    <div class="col-md-6 text-end">
        <a href="?page=expenses&action=new" class="btn btn-primary">+ Nuevo Gasto</a>
    </div>
</div>';

$content .= '
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gastos Operativos</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
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
    $content .= '<tr><td colspan="7" class="text-center">No hay gastos registrados</td></tr>';
} else {
    foreach ($expenses as $e) {
        $methodLabel = $e['payment_method'] === 'efectivo' ? 'Efectivo' : ($e['payment_method'] === 'transferencia' ? 'Transferencia' : 'Banco');
        
        $content .= '<tr>
            <td>#' . $e['id'] . '</td>
            <td>' . Format::date($e['date']) . '</td>
            <td>' . htmlspecialchars($e['category']) . '</td>
            <td>' . htmlspecialchars($e['description'] ?? '-') . '</td>
            <td>' . $methodLabel . '</td>
            <td>' . Format::money($e['amount']) . '</td>
            <td>' . htmlspecialchars($e['user_name']) . '</td>
        </tr>';
    }
}

$content .= '</tbody>
        </table>
    </div>
</div>';

if (count($expenses) > 0) {
    $total = array_sum(array_map(fn($e) => $e['amount'], $expenses));
    $content .= '
<div class="alert alert-danger mt-3">
    <strong>Total de gastos: ' . Format::money($total) . '</strong>
</div>';
}