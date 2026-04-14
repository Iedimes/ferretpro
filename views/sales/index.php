<?php 
$title = 'Ventas';
$pageTitle = 'Gestión de Ventas';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

$sales = db()->query("
    SELECT s.*, u.name as user_name, c.name as client_name 
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.id 
    LEFT JOIN clients c ON s.client_id = c.id
    ORDER BY s.id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$totalSales = db()->query("SELECT COALESCE(SUM(total), 0) as total FROM sales")->fetch(PDO::FETCH_ASSOC);

$content = '
<form method="GET" class="row mb-3">
    <input type="hidden" name="page" value="sales">
    <div class="col-md-2">
        <label class="form-label">Desde</label>
        <input type="date" name="date_from" class="form-control" value="' . $dateFrom . '">
    </div>
    <div class="col-md-2">
        <label class="form-label">Hasta</label>
        <input type="date" name="date_to" class="form-control" value="' . $dateTo . '">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <a href="?page=pos" class="btn btn-success w-100">Nueva Venta</a>
    </div>
</form>

<div class="alert alert-info">
    <strong>Total:</strong> ' . Format::money($totalSales['total']) . '
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Tipo</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';
if (empty($sales)) {
    $content .= '<tr><td colspan="10" class="text-center text-muted">No hay ventas</td></tr>';
} else {
    foreach ($sales as $s) {
        // Determinar estado combinado: Tipo + Estado de pago
        $saleType = $s['type'];
        $saleStatus = $s['status'] ?? ($s['type'] === 'contado' ? 'pagada' : 'pendiente');
        $deliveryType = $s['delivery_type'] ?? 'mostrador';
        
        // Generar etiqueta de estado de venta
        if ($saleType === 'contado') {
            $statusLabel = 'Contado';
            $statusClass = 'success';
        } else {
            if ($saleStatus === 'pagada') {
                $statusLabel = 'Crédito Pagado';
                $statusClass = 'success';
            } else {
                $statusLabel = 'Crédito Pendiente';
                $statusClass = 'warning';
            }
        }
        
        // Etiqueta de entrega (click para marcar como entregado)
        if ($deliveryType === 'pendiente') {
            $deliveryLabel = '<a href="?page=sales&action=deliver&id=' . $s['id'] . '" class="badge bg-warning text-decoration-none" title="Clic para marcar como entregado">Pendiente <i class="bi bi-hand-index"></i></a>';
            $deliveryAction = '';
        } else if ($deliveryType === 'delivery') {
            $deliveryLabel = '<span class="badge bg-info">Delivery</span>';
            $deliveryAction = '';
        } else {
            $deliveryLabel = '<span class="badge bg-success">Entregado</span>';
            $deliveryAction = '';
        }
        
        $content .= '<tr>
            <td>' . $s['id'] . '</td>
            <td>' . Format::date($s['created_at']) . '</td>
            <td>' . ($s['client_name'] ?? 'Mostrador') . '</td>
            <td>' . $s['user_name'] . '</td>
            <td>' . ucfirst($s['type']) . '</td>
            <td>' . ucfirst($s['payment_method']) . '</td>
            <td>' . Format::money($s['total']) . '</td>
            <td><span class="badge bg-' . $statusClass . '">' . $statusLabel . '</span></td>
            <td>' . $deliveryLabel . '</td>
            <td>
                <a href="?page=sales&action=edit&id=' . $s['id'] . '" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                <a href="?page=sales&action=print&id=' . $s['id'] . '" class="btn btn-sm btn-secondary" target="_blank" title="Imprimir"><i class="bi bi-printer"></i></a>
                ' . $deliveryAction . '
            </td>
        </tr>';
    }
}
$content .= '</tbody>
        </table>
    </div>
</div>';
