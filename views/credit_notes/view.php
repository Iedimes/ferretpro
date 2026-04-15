<?php 
$title = 'Nota de Crédito #' . $cn['id'];
$pageTitle = 'Nota de Crédito #' . $cn['id'];

$content = '
<div class="row mb-3">
    <div class="col-md-6">
        <a href="?page=credit_notes" class="btn btn-secondary">← Volver</a>
    </div>
    <div class="col-md-6 text-end">
        ' . ($cn['status'] === 'pending' ? '<a href="?page=credit_notes&action=apply&id=' . $cn['id'] . '" class="btn btn-success" onclick="return confirm(\'Aplicar esta nota de crédito? Se reintegrará el stock.\')">Aplicar</a>
            <a href="?page=credit_notes&action=cancel&id=' . $cn['id'] . '" class="btn btn-danger" onclick="return confirm(\'Anular esta nota de crédito?\')">Anular</a>' : '') . '
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
                <h4>NOTA DE CRÉDITO</h4>
                <p>N°: ' . str_pad($cn['id'], 7, '0', STR_PAD_LEFT) . '</p>
                <p>Fecha: ' . Format::date($cn['created_at']) . '</p>
            </div>
        </div>
        
        <hr>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Venta relacionada:</strong> #' . $cn['sale_num'] . '<br>
                <strong>Cliente:</strong> ' . htmlspecialchars($cn['client_name'] ?? 'Mostrador') . '
            </div>
            <div class="col-md-6 text-end">
                <strong>Estado:</strong> 
                ' . ($cn['status'] === 'pending' ? '<span class="badge bg-warning">Pendiente</span>' : ($cn['status'] === 'applied' ? '<span class="badge bg-success">Aplicada</span>' : '<span class="badge bg-danger">Anulada</span>')) . '
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-12">
                <strong>Motivo:</strong> ' . htmlspecialchars($cn['reason']) . '
            </div>
        </div>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Precio</th>
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
                    <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                    <td class="text-end"><strong>' . Format::money($cn['total']) . '</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="mt-4 text-center">
            <p class="text-muted">Vendedor: ' . htmlspecialchars($cn['user_name']) . '</p>
        </div>
    </div>
</div>';