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

$stmtDetails = db()->prepare("SELECT sd.*, p.name as product_name, p.code as product_code, p.iva as product_iva 
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

$companyName = $settings['company_name'] ?? 'Ferretería';
$companyPhone = $settings['company_phone'] ?? '';
$companyEmail = $settings['company_email'] ?? '';
$companyDocument = $settings['company_document'] ?? '';
$invoicePrefix = $settings['invoice_prefix'] ?? '001-001-';
$invoicePos = $settings['invoice_pos'] ?? '001';

$saleTypeLabel = $sale['type'] === 'contado' ? 'X' : '';
$saleTypeCredito = $sale['type'] === 'credito' ? 'X' : '';
$paymentMethodLabel = ucfirst($sale['payment_method']);
$deliveryTypeLabel = $sale['delivery_type'] === 'mostrador' ? 'Mostrador' : ($sale['delivery_type'] === 'delivery' ? 'Delivery' : 'Pendiente');

$invoiceNumber = $invoicePrefix . str_pad($sale_id, 7, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?php echo $invoiceNumber; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        
        .invoice { width: 180mm; margin: 0 auto; padding: 5mm; }
        
        .header { text-align: center; margin-bottom: 10px; }
        .company-name { font-size: 14px; font-weight: bold; }
        .company-activity { font-size: 9px; margin-top: 2px; }
        .company-address { font-size: 9px; margin-top: 2px; }
        .company-contact { font-size: 9px; margin-top: 2px; }
        
        .invoice-box { border: 2px solid #333; padding: 3mm; }
        .invoice-title { 
            font-size: 16px; 
            font-weight: bold; 
            text-align: center;
            margin-bottom: 5px;
        }
        .timbrado { 
            font-size: 9px; 
            text-align: right;
            margin-bottom: 5px;
        }
        
        .date-conditions { 
            display: table; 
            width: 100%; 
            border: 1px solid #333;
            margin-bottom: 5px;
            font-size: 10px;
        }
        .date-conditions td { padding: 3px; }
        
        .client-box { 
            border: 1px solid #333; 
            margin-bottom: 5px;
            font-size: 10px;
        }
        .client-box td { padding: 3px; }
        
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px;
            font-size: 10px;
        }
        .items-table th { 
            border: 1px solid #333; 
            padding: 3px; 
            text-align: center;
            background: #eee;
        }
        .items-table td { 
            border: 1px solid #333; 
            padding: 3px; 
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals { 
            display: table; 
            width: 100%; 
            border: 1px solid #333;
            font-size: 10px;
        }
        .totals td { padding: 3px 5px; }
        
        .iva-box { 
            border: 1px solid #333; 
            margin-top: 5px;
            font-size: 10px;
        }
        .iva-box td { padding: 3px; }
        
        .footer { 
            margin-top: 10px; 
            font-size: 9px; 
            text-align: center;
        }
        .footer-box { 
            border: 1px solid #333; 
            padding: 3px;
            font-size: 9px;
        }
        
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #333;
            margin-right: 3px;
            text-align: center;
            line-height: 12px;
            font-size: 10px;
        }
        
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
            .invoice { width: 100%; padding: 0; }
        }
        
        @page {
            size: A4;
            margin: 5mm;
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding: 10px; background: #f0f0f0; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Imprimir Factura</button>
        <a href="?page=sales" class="btn btn-secondary">Volver</a>
    </div>
    
    <div class="invoice">
        <div class="header">
            <div class="company-name"><?php echo htmlspecialchars($companyName); ?></div>
            <div class="company-activity">INSTALACIÓN DE CALEFACCIÓN Y AIRE ACONDICIONADO | MANTENIMIENTO Y REPARACIÓN DE EQUIPOS ELÉCTRICOS</div>
            <div class="company-address"><?php echo htmlspecialchars($settings['company_address'] ?? 'Asunción'); ?></div>
            <div class="company-contact">
                <?php if ($companyEmail): ?><?php echo htmlspecialchars($companyEmail); ?><?php endif; ?>
                <?php if ($companyPhone): ?> | Tel: <?php echo htmlspecialchars($companyPhone); ?><?php endif; ?>
            </div>
        </div>
        
        <div class="invoice-box">
            <div class="invoice-title">FACTURA</div>
            <div class="timbrado">
                TIMBRADO Nº: <?php echo htmlspecialchars($settings['timbrado_num'] ?? '18672488'); ?><br>
                Vigencia: <?php echo htmlspecialchars($settings['timbrado_inicio'] ?? '24/02/2026'); ?> al <?php echo htmlspecialchars($settings['timbrado_fin'] ?? '28/02/2027'); ?><br>
                RUC: <?php echo htmlspecialchars($companyDocument); ?><br>
                <?php echo $invoiceNumber; ?>
            </div>
            
            <table class="date-conditions">
                <tr>
                    <td width="50%">Asunción, <?php echo date('d', strtotime($sale['created_at'])); ?> de <?php echo date('m', strtotime($sale['created_at'])); ?> de <?php echo date('Y', strtotime($sale['created_at'])); ?></td>
                    <td width="50%">
                        Condición de Venta: 
                        <span class="checkbox"><?php echo $saleTypeLabel; ?></span> CONTADO
                        <span class="checkbox"><?php echo $saleTypeCredito; ?></span> CRÉDITO
                    </td>
                </tr>
            </table>
            
            <table class="client-box">
                <tr>
                    <td width="20%">C.I. Nº / R.U.C.:</td>
                    <td width="30%"><?php echo htmlspecialchars($sale['client_document'] ?? ''); ?></td>
                    <td width="20%">Teléfono:</td>
                    <td width="30%"><?php echo htmlspecialchars($sale['client_phone'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td>Nombre o Razón Social:</td>
                    <td colspan="3"><?php echo htmlspecialchars($sale['client_name'] ?? 'MOSTRADOR'); ?></td>
                </tr>
                <tr>
                    <td>Dirección:</td>
                    <td colspan="3"><?php echo htmlspecialchars($sale['client_address'] ?? ''); ?></td>
                </tr>
            </table>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="8%">Cantidad</th>
                        <th>DESCRIPCION</th>
                        <th width="12%">Precio</th>
                        <th colspan="3" width="30%">VALOR DE VENTA</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Unitario</th>
                        <th width="10%">EXENTAS</th>
                        <th width="10%">5%</th>
                        <th width="10%">10%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalExento = 0;
                    $totalIva5 = 0;
                    $totalIva10 = 0;
                    foreach ($saleDetails as $item): 
                        $iva = intval($item['product_iva'] ?? $item['iva'] ?? 10);
                        $subtotal = floatval($item['subtotal']);
                        
                        // Calcular base y IVA
                        if ($iva == 10) {
                            $base = $subtotal / 1.10;
                            $ivaAmount = $subtotal - $base;
                            $totalIva10 += $ivaAmount;
                            $baseFormatted = $base;
                        } elseif ($iva == 5) {
                            $base = $subtotal / 1.05;
                            $ivaAmount = $subtotal - $base;
                            $totalIva5 += $ivaAmount;
                            $baseFormatted = $base;
                        } else {
                            $baseFormatted = $subtotal;
                            $totalExento += $subtotal;
                        }
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td class="text-right"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                        <td class="text-right"><?php echo $iva == 0 ? number_format($baseFormatted, 0, ',', '.') : '0'; ?></td>
                        <td class="text-right"><?php echo $iva == 5 ? number_format($baseFormatted, 0, ',', '.') : '0'; ?></td>
                        <td class="text-right"><?php echo $iva == 10 ? number_format($baseFormatted, 0, ',', '.') : '0'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <table class="totals">
                <tr>
                    <td width="70%" class="text-right"><strong>SUB TOTAL:</strong></td>
                    <td width="30%" class="text-right"><?php echo number_format($sale['subtotal'], 0, ',', '.'); ?></td>
                </tr>
                <?php if ($sale['discount'] > 0): ?>
                <tr>
                    <td class="text-right">DESCUENTO:</td>
                    <td class="text-right">-<?php echo number_format($sale['discount'], 0, ',', '.'); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-right"><strong>TOTAL A PAGAR:</strong></td>
                    <td class="text-right"><strong><?php echo number_format($sale['total'], 0, ',', '.'); ?></strong></td>
                </tr>
            </table>
            
            <table class="iva-box">
                <tr>
                    <td colspan="2"><strong>LIQUIDACIÓN DEL IVA</strong></td>
                </tr>
                <tr>
                    <td width="33%">(5%): <?php echo number_format($totalIva5, 0, ',', '.'); ?></td>
                    <td width="33%">(10%): <?php echo number_format($totalIva10, 0, ',', '.'); ?></td>
                    <td width="34%">TOTAL IVA: <?php echo number_format($totalIva5 + $totalIva10, 0, ',', '.'); ?></td>
                </tr>
            </table>
            
            <div class="footer">
                <div class="footer-box">
                    <strong>Gracias por su preferencia</strong><br>
                    <?php echo htmlspecialchars($companyName); ?> - RUC: <?php echo htmlspecialchars($companyDocument); ?>
                </div>
                <div style="margin-top: 3px;">
                    ORIGINAL: Cliente | DUPLICADO: Archivo Tributario
                </div>
            </div>
        </div>
    </div>
</body>
</html>
