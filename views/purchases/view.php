<?php 
$title = 'Compra #' . $purchase['id'];
$pageTitle = 'Compra #' . $purchase['id'];

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=purchases" class="btn btn-secondary">← Volver</a>
    </div>
    <div class="col-md-6 text-end">
        ' . ($purchase['status'] === 'pending' ? '<a href="?page=purchases&action=receive&id=' . $purchase['id'] . '" class="btn btn-success" onclick="return confirm(\'Recibir esta compra? Se actualizará el stock.\')">Recibir</a>' : '') . '
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Proveedor: ' . htmlspecialchars($purchase['provider_name']) . '</h5>
                <p>N° Factura: ' . htmlspecialchars($purchase['invoice_number'] ?? '-') . '</p>
            </div>
            <div class="col-md-6 text-end">
                <h4>COMPRA</h4>
                <p>N°: ' . str_pad($purchase['id'], 7, '0', STR_PAD_LEFT) . '</p>
                <p>Fecha: ' . Format::date($purchase['created_at']) . '</p>
            </div>
        </div>
        
        <hr>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Forma de pago:</strong> ' . ($purchase['payment_method'] === 'contado' ? 'Contado' : 'Crédito') . '
            </div>
            <div class="col-md-6 text-end">
                <strong>Estado:</strong> 
                ' . ($purchase['status'] === 'pending' ? '<span class="badge bg-warning">Pendiente</span>' : ($purchase['status'] === 'received' ? '<span class="badge bg-success">Recibido</span>' : '<span class="badge bg-secondary">' . htmlspecialchars($purchase['status']) . '</span>')) . '
            </div>
        </div>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Costo Unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                ';
foreach ($details as $item) {
    $content .= '<tr>
        <td>' . htmlspecialchars($item['code']) . '</td>
        <td>' . htmlspecialchars($item['name']) . '</td>
        <td class="text-center">' . $item['quantity'] . '</td>
        <td class="text-end">' . Format::money($item['unit_cost']) . '</td>
        <td class="text-end">' . Format::money($item['subtotal']) . '</td>
    </tr>';
}

$content .= '
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                    <td class="text-end"><strong>' . Format::money($purchase['subtotal']) . '</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="mt-4 text-center">
            <p class="text-muted">Usuario: ' . htmlspecialchars($purchase['user_name']) . '</p>
        </div>
    </div>
</div>';