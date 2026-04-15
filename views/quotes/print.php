<!DOCTYPE html>
<html>
<head>
    <title>Cotización #<?= str_pad($quote['id'], 7, '0', STR_PAD_LEFT) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .company { font-size: 18px; font-weight: bold; }
        .document { font-size: 10px; color: #666; }
        .title { font-size: 24px; text-align: center; margin: 20px 0; }
        .info { margin-bottom: 20px; }
        .info td { padding: 5px 10px 5px 0; vertical-align: top; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; font-size: 14px; }
        .footer { margin-top: 40px; text-align: center; color: #666; font-size: 10px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px;">Imprimir</button>
        <button onclick="window.close()" style="padding: 10px 20px;">Cerrar</button>
    </div>
    
    <div class="header">
        <div>
            <div class="company">Ferretería Pro</div>
            <div class="document">RUC: 80025255-5</div>
            <div class="document">Teléfono: (021) 123-456</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 24px; font-weight: bold;">COTIZACIÓN</div>
            <div>N°: <?= str_pad($quote['id'], 7, '0', STR_PAD_LEFT) ?></div>
            <div>Fecha: <?= Format::date($quote['created_at']) ?></div>
        </div>
    </div>
    
    <table class="info">
        <tr>
            <td style="width: 50%;">
                <strong>Señor(es):</strong> <?= htmlspecialchars($quote['client_name'] ?? 'Sin cliente') ?><br>
                <?php if ($quote['client_document']): ?>
                <strong>RUC/CI:</strong> <?= htmlspecialchars($quote['client_document']) ?><br>
                <?php endif; ?>
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Válida hasta:</strong> <?= Format::date($quote['expires_at'] ?? date('Y-m-d', strtotime('+' . $quote['validity_days'] . ' days'))) ?><br>
                <strong>Vendedor:</strong> <?= htmlspecialchars($quote['user_name']) ?>
            </td>
        </tr>
    </table>
    
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">Código</th>
                <th>Descripción</th>
                <th class="text-center" style="width: 60px;">Cantidad</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $item): ?>
            <tr>
                <td class="text-center"><?= htmlspecialchars($item['code']) ?></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td class="text-center"><?= $item['quantity'] ?></td>
                <td class="text-right"><?= Format::money($item['unit_price']) ?></td>
                <td class="text-right"><?= Format::money($item['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Subtotal:</td>
                <td class="text-right"><?= Format::money($quote['subtotal']) ?></td>
            </tr>
            <?php if ($quote['discount'] > 0): ?>
            <tr>
                <td colspan="4" class="text-right">Descuento:</td>
                <td class="text-right">-<?= Format::money($quote['discount']) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-right"><?= Format::money($quote['total']) ?></td>
            </tr>
        </tfoot>
    </table>
    
    <?php if ($quote['notes']): ?>
    <div style="margin-top: 20px;">
        <strong>Notas:</strong><br>
        <?= nl2br(htmlspecialchars($quote['notes'])) ?>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Esta cotización tiene validez de <?= $quote['validity_days'] ?> días.</p>
        <p>Ferretería Pro -感谢您的光临</p>
    </div>
</body>
</html>