<?php
require_once __DIR__ . '/actions/helpers.php';

if ($_SERVER['REQUEST_URI'] === '/pos/process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = json_decode($_POST['items'] ?? '[]', true);
    $client_id = $_POST['client_id'] ?? null;
    $sale_type = $_POST['sale_type'] ?? 'contado';
    $payment_method = $_POST['payment_method'] ?? 'efectivo';
    $discount = floatval($_POST['discount'] ?? 0);
    $delivery_type = $_POST['delivery_type'] ?? 'mostrador';
    
    if (empty($items)) {
        flash('error', 'No hay productos en la venta');
        redirect('/pos');
    }
    
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    $discount_amount = $subtotal * ($discount / 100);
    $total = $subtotal - $discount_amount;
    
    try {
        db()->beginTransaction();
        
        // Get current user's branch and POS terminal
        $branchPOS = getCurrentUserBranchAndPOS();
        
        $stmt = db()->prepare("INSERT INTO sales (client_id, user_id, type, status, subtotal, discount, total, payment_method, delivery_type, branch_id, pos_terminal_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $status = $sale_type === 'credito' ? 'pendiente' : 'pagada';
        $stmt->execute([$client_id, auth(), $sale_type, $status, $subtotal, $discount_amount, $total, $payment_method, $delivery_type, $branchPOS['branch_id'], $branchPOS['pos_terminal_id']]);
        $sale_id = db()->lastInsertId();
        
        foreach ($items as $item) {
            $stmt = db()->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, unit_price, discount, subtotal) VALUES (?, ?, ?, ?, 0, ?)");
            $item_subtotal = $item['price'] * $item['qty'];
            $stmt->execute([$sale_id, $item['id'], $item['qty'], $item['price'], $item_subtotal]);
            
            $stmt = db()->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['qty'], $item['id']]);
            
            $stmt = db()->prepare("INSERT INTO stock_movements (product_id, type, quantity, reference_type, reference_id) VALUES (?, 'salida', ?, 'sale', ?)");
            $stmt->execute([$item['id'], $item['qty'], $sale_id]);
        }
        
        if ($sale_type === 'credito' && $client_id) {
            $stmt = db()->prepare("INSERT INTO accounts_receivable (client_id, sale_id, amount, status) VALUES (?, ?, ?, 'pendiente')");
            $stmt->execute([$client_id, $sale_id, $total]);
            
            $client = db()->prepare("UPDATE clients SET balance = balance + ? WHERE id = ?");
            $client->execute([$total, $client_id]);
        }
        
        db()->commit();
        
        flash('success', 'Venta #' . $sale_id . ' procesada correctamente');
        redirect('/sales/' . $sale_id);
        
    } catch (Exception $e) {
        db()->rollBack();
        flash('error', 'Error al procesar venta: ' . $e->getMessage());
        redirect('/pos');
    }
}
