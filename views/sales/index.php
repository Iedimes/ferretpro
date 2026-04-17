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
    WHERE DATE(s.created_at) >= '$dateFrom' AND DATE(s.created_at) <= '$dateTo'
    ORDER BY s.id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$totalSales = db()->query("SELECT COALESCE(SUM(total), 0) as total FROM sales WHERE DATE(created_at) >= '$dateFrom' AND DATE(created_at) <= '$dateTo'")->fetch(PDO::FETCH_ASSOC);

$content = '
<div class="mb-4">
    <a href="?page=dashboard" class="btn btn-nav-back me-2"><i class="bi bi-arrow-left"></i> Volver al Dashboard</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row">
            <input type="hidden" name="page" value="sales">
            <div class="col-md-2">
                <label class="form-label"><i class="bi bi-calendar"></i> Desde</label>
                <input type="date" name="date_from" class="form-control" value="' . $dateFrom . '">
            </div>
            <div class="col-md-2">
                <label class="form-label"><i class="bi bi-calendar"></i> Hasta</label>
                <input type="date" name="date_to" class="form-control" value="' . $dateTo . '">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="?page=pos" class="btn btn-success w-100"><i class="bi bi-plus-circle"></i> Nueva Venta</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), transparent);">
        <h6 class="text-muted mb-1">Total de Ventas</h6>
        <h3 class="mb-0" style="font-size: 2rem; font-weight: 700;">' . Format::money($totalSales['total']) . '</h3>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-receipt"></i> Listado de Ventas</h6>
    </div>
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
            <td><strong>#' . $s['id'] . '</strong></td>
            <td>' . Format::date($s['created_at']) . '</td>
            <td>' . ($s['client_name'] ?? 'Mostrador') . '</td>
            <td>' . $s['user_name'] . '</td>
            <td><span class="badge bg-info">' . ucfirst($s['type']) . '</span></td>
            <td><span class="badge bg-secondary">' . ucfirst($s['payment_method']) . '</span></td>
            <td><strong>' . Format::money($s['total']) . '</strong></td>
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
