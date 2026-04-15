<?php 
$title = 'Cotizaciones';
$pageTitle = 'Cotizaciones y Presupuestos';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=home" class="btn btn-secondary">← Volver</a>
    </div>
    <div class="col-md-6 text-end">
        <a href="?page=quotes&action=new" class="btn btn-primary">+ Nueva Cotización</a>
    </div>
</div>';

$quotes = db()->query("
    SELECT q.*, c.name as client_name, u.name as user_name
    FROM quotes q
    LEFT JOIN clients c ON q.client_id = c.id
    LEFT JOIN users u ON q.user_id = u.id
    ORDER BY q.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$content .= '
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cotizaciones</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Validez</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

if (count($quotes) == 0) {
    $content .= '<tr><td colspan="7" class="text-center">No hay cotizaciones</td></tr>';
} else {
    foreach ($quotes as $q) {
        $statusClass = $q['status'] === 'pending' ? 'warning' : ($q['status'] === 'accepted' ? 'success' : ($q['status'] === 'rejected' ? 'danger' : 'secondary'));
        $statusLabel = $q['status'] === 'pending' ? 'Pendiente' : ($q['status'] === 'accepted' ? 'Aceptada' : ($q['status'] === 'rejected' ? 'Rechazada' : 'Vencida'));
        
        $content .= '<tr>
            <td>#' . $q['id'] . '</td>
            <td>' . Format::date($q['created_at']) . '</td>
            <td>' . htmlspecialchars($q['client_name'] ?? $q['client_name'] ?? 'Sin cliente') . '</td>
            <td>' . Format::money($q['total']) . '</td>
            <td>' . $q['validity_days'] . ' días</td>
            <td><span class="badge bg-' . $statusClass . '">' . $statusLabel . '</span></td>
            <td>
                <a href="?page=quotes&action=view&id=' . $q['id'] . '" class="btn btn-sm btn-primary">Ver</a>
                <a href="?page=quotes&action=convert&id=' . $q['id'] . '" class="btn btn-sm btn-success">Convertir a Venta</a>
            </td>
        </tr>';
    }
}

$content .= '</tbody>
        </table>
    </div>
</div>';
