<?php 
$title = 'Cotización #' . $quote['id'];
$pageTitle = 'Cotización #' . $quote['id'];

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=quotes" class="btn btn-secondary">← Volver</a>
    </div>
    <div class="col-md-6 text-end">
        <a href="?page=quotes&action=print&id=' . $quote['id'] . '" class="btn btn-primary" target="_blank">Imprimir</a>
        ' . ($quote['status'] === 'pending' ? '<a href="?page=quotes&action=convert&id=' . $quote['id'] . '" class="btn btn-success">Convertir a Venta</a>' : '') . '
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Ferretería Pro</h5>
                <p>RUC: 80025255-5</p>
            </div>
            <div class="col-md-6 text-end">
                <h4>COTIZACIÓN</h4>
                <p>N°: ' . str_pad($quote['id'], 7, '0', STR_PAD_LEFT) . '</p>
                <p>Fecha: ' . Format::date($quote['created_at']) . '</p>
            </div>
        </div>
        
        <hr>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Cliente:</strong>
                <p>' . htmlspecialchars($quote['client_name'] ?? 'Sin cliente') . '</p>
                ' . ($quote['client_document'] ? '<p>Documento: ' . htmlspecialchars($quote['client_document']) . '</p>' : '') . '
            </div>
            <div class="col-md-6 text-end">
                <strong>Válida hasta:</strong>
                <p>' . Format::date($quote['expires_at'] ?? date('Y-m-d', strtotime('+' . $quote['validity_days'] . ' days'))) . ' (' . $quote['validity_days'] . ' días)</p>
            </div>
        </div>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Precio Unit.</th>
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
        <td class="text-end">' . Format::money($item['unit_price']) . '</td>
        <td class="text-end">' . Format::money($item['subtotal']) . '</td>
    </tr>';
}

$content .= '
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">Subtotal:</td>
                    <td class="text-end">' . Format::money($quote['subtotal']) . '</td>
                </tr>
                ' . ($quote['discount'] > 0 ? '<tr>
                    <td colspan="4" class="text-end">Descuento:</td>
                    <td class="text-end">-' . Format::money($quote['discount']) . '</td>
                </tr>' : '') . '
                <tr>
                    <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                    <td class="text-end"><strong>' . Format::money($quote['total']) . '</strong></td>
                </tr>
            </tfoot>
        </table>
        
        ' . ($quote['notes'] ? '<div class="mt-3">
            <strong>Notas:</strong>
            <p>' . nl2br(htmlspecialchars($quote['notes'])) . '</p>
        </div>' : '') . '
        
        <div class="mt-4 text-center">
            <p class="text-muted">Vendedor: ' . htmlspecialchars($quote['user_name']) . '</p>
        </div>
    </div>
</div>';