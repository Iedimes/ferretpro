<?php
$title = 'Factura';
$pageTitle = 'Imprimir Factura';

$sale_id = intval($_GET['id'] ?? 0);

if ($sale_id == 0) {
    die("ID de venta no válido");
}

$stmt = db()->prepare("SELECT s.*, u.name as user_name, c.name as client_name, c.document as client_document, c.phone as client_phone, c.address as client_address 
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.id 
    LEFT JOIN clients c ON s.client_id = c.id 
    WHERE s.id = ?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    die("Venta no encontrada");
}

$stmtDetails = db()->prepare("SELECT sd.*, p.name as product_name, p.code as product_code 
    FROM sale_details sd 
    LEFT JOIN products p ON sd.product_id = p.id 
    WHERE sd.sale_id = ?");
$stmtDetails->execute([$sale_id]);
$saleDetails = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

$settingsRows = db()->query("SELECT key, value FROM settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settingsRows as $row) {
    $settings[$row['key']] = $row['value'];
}

$invoiceType = $settings['invoice_type'] ?? 'letter';
$companyName = $settings['company_name'] ?? 'Ferretería';
$companyPhone = $settings['company_phone'] ?? '';
$companyEmail = $settings['company_email'] ?? '';
$companyDocument = $settings['company_document'] ?? '';
$invoicePrefix = $settings['invoice_prefix'] ?? '001-001-';
$invoicePos = $settings['invoice_pos'] ?? '001';

$saleTypeLabel = $sale['type'] === 'contado' ? 'Contado' : 'Crédito';
$paymentMethodLabel = ucfirst($sale['payment_method']);
$deliveryTypeLabel = $sale['delivery_type'] === 'mostrador' ? 'Mostrador' : ($sale['delivery_type'] === 'delivery' ? 'Delivery' : 'Pendiente');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #<?php echo $sale_id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        
        .invoice-container {
            max-width: <?php echo $invoiceType === 'thermal' ? '58mm' : '210mm'; ?>;
            margin: 0 auto;
            padding: <?php echo $invoiceType === 'thermal' ? '5mm' : '20mm'; ?>;
        }
        
        .header { text-align: center; margin-bottom: 15px; }
        .company-name { font-size: <?php echo $invoiceType === 'thermal' ? '14px' : '18px'; ?>; font-weight: bold; }
        .company-info { font-size: 10px; margin-top: 5px; }
        
        .invoice-title { 
            font-size: <?php echo $invoiceType === 'thermal' ? '14px' : '24px'; ?>; 
            font-weight: bold; 
            margin: 15px 0;
            text-align: center;
            border: 2px solid #333;
            padding: 5px;
        }
        
        .invoice-info { margin-bottom: 15px; }
        .invoice-info-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        
        .client-info { 
            border: 1px solid #ccc; 
            padding: 10px; 
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { 
            border-bottom: 2px solid #333; 
            padding: 5px; 
            text-align: left;
            font-size: 10px;
        }
        .items-table td { 
            border-bottom: 1px solid #ccc; 
            padding: 5px; 
        }
        .text-right { text-align: right; }
        
        .totals { margin-top: 15px; }
        .totals-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
        }
        .totals-row.total { 
            font-size: 14px; 
            font-weight: bold; 
            border-bottom: 2px solid #333;
            border-top: 2px solid #333;
        }
        
        .footer { text-align: center; margin-top: 20px; font-size: 10px; }
        
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding: 10px; background: #f0f0f0; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Imprimir Factura</button>
        <a href="?page=sales" class="btn btn-secondary">Volver</a>
    </div>
    
    <div class="invoice-container">
        <div class="header">
            <div class="company-name"><?php echo htmlspecialchars($companyName); ?></div>
            <div class="company-info">
                <?php if ($companyPhone): ?><?php echo htmlspecialchars($companyPhone); ?><?php endif; ?>
                <?php if ($companyEmail): ?> | <?php echo htmlspecialchars($companyEmail); ?><?php endif; ?>
                <?php if ($companyDocument): ?> | RUC: <?php echo htmlspecialchars($companyDocument); ?><?php endif; ?>
            </div>
        </div>
        
        <div class="invoice-title">
            <?php echo $invoicePrefix; ?><?php echo str_pad($sale_id, 7, '0', STR_PAD_LEFT); ?>
        </div>
        
        <div class="invoice-info">
            <div class="invoice-info-row">
                <span><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?></span>
                <span><strong>Vendedor:</strong> <?php echo htmlspecialchars($sale['user_name']); ?></span>
            </div>
            <div class="invoice-info-row">
                <span><strong>Tipo:</strong> <?php echo $saleTypeLabel; ?></span>
                <span><strong>Pago:</strong> <?php echo $paymentMethodLabel; ?></span>
            </div>
            <div class="invoice-info-row">
                <span><strong>Entrega:</strong> <?php echo $deliveryTypeLabel; ?></span>
            </div>
        </div>
        
        <?php if ($sale['client_id']): ?>
        <div class="client-info">
            <strong>Cliente:</strong> <?php echo htmlspecialchars($sale['client_name']); ?><br>
            <?php if ($sale['client_document']): ?><strong>RUC/CI:</strong> <?php echo htmlspecialchars($sale['client_document']); ?><br><?php endif; ?>
            <?php if ($sale['client_phone']): ?><strong>Teléfono:</strong> <?php echo htmlspecialchars($sale['client_phone']); ?><?php endif; ?>
        </div>
        <?php else: ?>
        <div class="client-info">
            <strong>Cliente:</strong> Mostrador
        </div>
        <?php endif; ?>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Código</th>
                    <th>Producto</th>
                    <th class="text-right" style="width: 50px;">Cant</th>
                    <th class="text-right" style="width: 70px;">P.Unit</th>
                    <th class="text-right" style="width: 80px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($saleDetails as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_code']); ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="text-right"><?php echo $item['quantity']; ?></td>
                    <td class="text-right"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span><?php echo number_format($sale['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <?php if ($sale['discount'] > 0): ?>
            <div class="totals-row">
                <span>Descuento:</span>
                <span>-<?php echo number_format($sale['discount'], 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            <div class="totals-row total">
                <span>TOTAL:</span>
                <span><?php echo number_format($sale['total'], 0, ',', '.'); ?></span>
            </div>
        </div>
        
        <div class="footer">
            <p>Gracias por su preferencia</p>
            <p> <?php echo $companyName; ?></p>
        </div>
    </div>
</body>
</html>
