<?php 
$title = 'Compras';
$pageTitle = 'Compras a Proveedores';

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=home" class="btn btn-secondary">← Volver</a>
        <a href="?page=purchases&action=new" class="btn btn-primary">Nueva Compra</a>
    </div>
</div>';

$purchases = db()->query("
    SELECT p.*, pr.name as provider_name, u.name as user_name
    FROM purchases p
    LEFT JOIN providers pr ON p.provider_id = pr.id
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$content .= '
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Compras</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Factura</th>
                    <th>Total</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

if (count($purchases) == 0) {
    $content .= '<tr><td colspan="7" class="text-center">No hay compras</td></tr>';
} else {
    foreach ($purchases as $p) {
        $rawStatus = $p['status'] ?? 'contado';
        $statusClass = $rawStatus === 'pending' ? 'warning' : ($rawStatus === 'paid' ? 'success' : ($rawStatus === 'received' ? 'info' : 'success'));
        $statusLabel = $rawStatus === 'pending' ? 'Pendiente' : ($rawStatus === 'paid' ? 'Pagado' : ($rawStatus === 'received' ? 'Recibido' : 'Contado'));
        $paymentLabel = $p['payment_method'] === 'contado' ? 'Contado' : 'Crédito';
        
        $content .= '<tr>
            <td>#' . $p['id'] . '</td>
            <td>' . Format::date($p['created_at']) . '</td>
            <td>' . htmlspecialchars($p['provider_name']) . '</td>
            <td>' . htmlspecialchars($p['invoice_number'] ?? '-') . '</td>
            <td>' . Format::money($p['total']) . '</td>
            <td>' . $paymentLabel . '</td>
            <td><span class="badge bg-' . $statusClass . '">' . $statusLabel . '</span></td>
            <td>
                <a href="?page=purchases&action=view&id=' . $p['id'] . '" class="btn btn-sm btn-primary">Ver</a>
                <a href="?page=purchases&action=edit&id=' . $p['id'] . '" class="btn btn-sm btn-warning">Editar</a>
            </td>
        </tr>';
    }
}

$content .= '</tbody>
        </table>
    </div>
</div>';
