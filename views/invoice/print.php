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

$companyName = $settings['company_name'] ?? 'Ferretería FerrePro';
$companyPhone = $settings['company_phone'] ?? '';
$companyEmail = $settings['company_email'] ?? '';
$companyDocument = $settings['company_document'] ?? '';
$companyAddress = $settings['company_address'] ?? '';
$companyActivity = $settings['company_activity'] ?? 'COMERCIO AL POR MENOR DE FERRETERÍA Y MATERIALES DE CONSTRUCCIÓN';

$establishment = str_pad($settings['invoice_establishment'] ?? '001', 3, '0', STR_PAD_LEFT);
$pos = str_pad($settings['invoice_pos'] ?? '001', 3, '0', STR_PAD_LEFT);
$invoiceNumber = $establishment . '-' . $pos . '-N° ' . str_pad($sale_id, 7, '0', STR_PAD_LEFT);

$saleTypeLabel = $sale['type'] === 'contado' ? 'X' : '';
$saleTypeCredito = $sale['type'] === 'credito' ? 'X' : '';
$paymentMethodLabel = ucfirst($sale['payment_method']);
$deliveryTypeLabel = $sale['delivery_type'] === 'mostrador' ? 'Mostrador' : ($sale['delivery_type'] === 'delivery' ? 'Delivery' : 'Pendiente');

$day = date('d', strtotime($sale['created_at']));
$month = date('m', strtotime($sale['created_at']));
$year = date('Y', strtotime($sale['created_at']));
$months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$monthName = $months[intval($month)];
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
        
.main-box { border: 2px solid #333; padding: 3mm; }
        
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .header-separator { width: 2%; }
        
.company-box { 
            border: 1px solid #333; 
            padding: 5px; 
            min-height: 80px;
            text-align: center;
            width: 100%;
        }
        
        .invoice-box { 
            border: 1px solid #333; 
            padding: 5px; 
            text-align: center;
            min-height: 80px;
            width: 100%;
        }
        
        .company-info { font-size: 13px; font-weight: bold; text-align: center; }
        .company-activity { font-size: 8px; margin-top: 2px; text-align: center; }
        .company-address { font-size: 9px; margin-top: 2px; text-align: center; }
        .company-contact { font-size: 9px; margin-top: 2px; text-align: center; }
        
        .invoice-title-box { 
            font-size: 14px; 
            font-weight: bold; 
            margin: 5px 0;
        }
        
        .invoice-number { 
            font-size: 12px; 
            font-weight: bold; 
            text-align: center;
        }
        
        .client-box { 
            border: 1px solid #333; 
            margin-bottom: 5px;
            font-size: 10px;
            width: 100%;
        }
        .client-box td { padding: 2px 5px; }
        .client-box .label-left { text-align: left; }
        .client-box .text-right { text-align: right; }
        
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
            width: 100%;
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
        
        .timbrado-box {
            border: 1px solid #333;
            padding: 3px;
            font-size: 8px;
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
        <div class="main-box">
            <table class="header-table">
                <tr>
                    <td class="header-left" width="60%">
                        <div class="company-box">
                            <div class="company-info"><?php echo htmlspecialchars($companyName); ?></div>
                            <div style="height: 15px;"></div>
                            <div class="company-activity"><?php echo htmlspecialchars($companyActivity); ?></div>
                            <div style="height: 10px;"></div>
                            <div class="company-address"><?php echo htmlspecialchars($companyAddress); ?></div>
                            <div style="height: 5px;"></div>
                            <div class="company-contact">
                                <?php if ($companyEmail): ?><?php echo htmlspecialchars($companyEmail); ?><?php endif; ?>
                                <?php if ($companyPhone): ?> | Tel: <?php echo htmlspecialchars($companyPhone); ?><?php endif; ?>
                            </div>
                            <div style="height: 20px;"></div>
                        </div>
                    </td>
                    <td class="header-separator" width="2%"></td>
                    <td class="header-right" width="38%">
                        <div class="invoice-box">
                            <div class="timbrado-line">TIMBRADO: <?php echo htmlspecialchars($settings['timbrado_num'] ?? '18672488'); ?></div>
                            <div class="timbrado-line">Inicio: <?php echo htmlspecialchars($settings['timbrado_inicio'] ?? '24/02/2026'); ?> | Vigencia: <?php echo htmlspecialchars($settings['timbrado_fin'] ?? '28/02/2027'); ?></div>
                            <div class="timbrado-line">RUC: <?php echo htmlspecialchars($companyDocument); ?></div>
                            <div class="invoice-title-box">FACTURA</div>
                            <div class="invoice-number"><?php echo $invoiceNumber; ?></div>
                        </div>
                    </td>
                </tr>
            </table>
            
            <div style="height: 8px;"></div>
            
            <table class="client-box">
                <tr>
                    <td width="50%">Asunción, <?php echo $day; ?> de <?php echo $monthName; ?> de <?php echo $year; ?></td>
                    <td width="50%" style="text-align: right;">
                        Condición de Venta: 
                        <span class="checkbox"><?php echo $saleTypeLabel; ?></span> CONTADO
                        <span class="checkbox"><?php echo $saleTypeCredito; ?></span> CRÉDITO
                    </td>
                </tr>
                <tr>
                    <td width="50%" class="label-left">C.I. Nº / R.U.C.: <?php echo htmlspecialchars($sale['client_document'] ?? ''); ?></td>
                    <td width="50%" class="text-right">Teléfono: <?php echo htmlspecialchars($sale['client_phone'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td width="50%" class="label-left">Nombre o Razón Social: <?php echo htmlspecialchars($sale['client_name'] ?? 'MOSTRADOR'); ?></td>
                    <td width="50%" class="text-right">Nota de Remisión Nº:</td>
                </tr>
                <tr>
                    <td width="50%" class="label-left">Dirección: <?php echo htmlspecialchars($sale['client_address'] ?? ''); ?></td>
                    <td width="50%" class="text-right"></td>
                </tr>
            </table>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="8%">Cantidad</th>
                        <th>DESCRIPCION</th>
                        <th width="12%">Precio Unitario</th>
                        <th colspan="3" width="30%">VALOR DE VENTA</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
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
                    <td width="33%"><strong>LIQUIDACIÓN DEL IVA (5%):</strong> <?php echo number_format($totalIva5, 0, ',', '.'); ?></td>
                    <td width="33%"><strong>(10%):</strong> <?php echo number_format($totalIva10, 0, ',', '.'); ?></td>
                    <td width="34%"><strong>TOTAL IVA:</strong> <?php echo number_format($totalIva5 + $totalIva10, 0, ',', '.'); ?></td>
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
