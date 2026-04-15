<?php 
$title = 'Notas de Crédito';
$pageTitle = 'Notas de Crédito';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=home" class="btn btn-secondary">← Volver</a>
    </div>
    <div class="col-md-6 text-end">
        <a href="?page=credit_notes&action=new" class="btn btn-primary">+ Nueva Nota de Crédito</a>
    </div>
</div>';

$creditNotes = db()->query("
    SELECT cn.*, c.name as client_name, u.name as user_name, s.total as sale_total
    FROM credit_notes cn
    LEFT JOIN clients c ON cn.client_id = c.id
    LEFT JOIN users u ON cn.user_id = u.id
    LEFT JOIN sales s ON cn.sale_id = s.id
    ORDER BY cn.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$content .= '
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Notas de Crédito</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Venta</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

if (count($creditNotes) == 0) {
    $content .= '<tr><td colspan="7" class="text-center">No hay notas de crédito</td></tr>';
} else {
    foreach ($creditNotes as $cn) {
        $statusClass = $cn['status'] === 'pending' ? 'warning' : ($cn['status'] === 'applied' ? 'success' : 'secondary');
        $statusLabel = $cn['status'] === 'pending' ? 'Pendiente' : ($cn['status'] === 'applied' ? 'Aplicada' : 'Anulada');
        
        $content .= '<tr>
            <td>#' . $cn['id'] . '</td>
            <td>' . Format::date($cn['created_at']) . '</td>
            <td>#' . $cn['sale_id'] . '</td>
            <td>' . htmlspecialchars($cn['client_name'] ?? 'Mostrador') . '</td>
            <td>' . Format::money($cn['total']) . '</td>
            <td><span class="badge bg-' . $statusClass . '">' . $statusLabel . '</span></td>
            <td>
                <a href="?page=credit_notes&action=view&id=' . $cn['id'] . '" class="btn btn-sm btn-primary">Ver</a>
            </td>
        </tr>';
    }
}

$content .= '</tbody>
        </table>
    </div>
</div>';
