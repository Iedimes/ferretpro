<?php
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/'));

require_once __DIR__ . '/actions/helpers.php';

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = BASE_URL;

if ($basePath && strpos($requestUri, $basePath) === 0) {
    $uri = substr($requestUri, strlen($basePath));
} else {
    $uri = $requestUri;
}

if (empty($uri) || $uri === '/index.php') {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

// Debug
error_log("URI: $uri, BaseURL: $basePath");

if ($uri === '/' || $uri === '/index.php') {
    if (auth()) {
        redirect('/dashboard');
    }
    require __DIR__ . '/views/auth/login.php';
    exit;
}

if ($uri === '/logout') {
    session_destroy();
    redirect('/');
}

if ($uri === '/login' && $method === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        flash('success', 'Bienvenido ' . $user['name']);
        redirect('/dashboard');
    }
    
    flash('error', 'Credenciales inválidas');
    redirect('/');
}

if ($uri === '/pos/process' && $method === 'POST') {
    $items = json_decode($_POST['items'] ?? '[]', true);
    $client_id = $_POST['client_id'] ?? null;
    $sale_type = $_POST['sale_type'] ?? 'contado';
    
    // DEBUG: Guardar en log qué viene del formulario
    error_log("DEBUG pos_process: sale_type POST=" . ($_POST['sale_type'] ?? 'NO DEFINIDO') . " client_id=" . $client_id);
    
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
        
        // DEBUG EXTRA
        error_log("=== GUARDANDO VENTA ===");
        error_log("sale_type a guardar: " . $sale_type);
        error_log("status calculado: " . ($sale_type === 'credito' ? 'pendiente' : 'pagada'));
        
        // Get default branch and POS terminal
        $branchPOS = getDefaultBranchAndPOS();
        
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

if (!auth()) {
    redirect('/');
}

if ($uri === '/dashboard') {
    $salesToday = db()->query("SELECT COUNT(*), COALESCE(SUM(total), 0) FROM sales WHERE DATE(created_at) = DATE('now')")->fetch(PDO::FETCH_ASSOC);
    $productsLow = db()->query("SELECT COUNT(*) FROM products WHERE stock <= min_stock AND active = 1")->fetch(PDO::FETCH_ASSOC);
    $clientsDebt = db()->query("SELECT COUNT(*), COALESCE(SUM(balance), 0) FROM clients WHERE balance > 0")->fetch(PDO::FETCH_ASSOC);
    $totalReceivable = db()->query("SELECT COUNT(*) as cnt, COALESCE(SUM(amount - COALESCE(paid_amount, 0)), 0) as total FROM accounts_receivable WHERE status = 'pendiente'")->fetch(PDO::FETCH_ASSOC);
    $salesMonth = db()->query("SELECT COUNT(*), COALESCE(SUM(total), 0) FROM sales WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')")->fetch(PDO::FETCH_ASSOC);
    $recentSales = db()->query("SELECT s.*, u.name as user_name, c.name as client_name FROM sales s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN clients c ON s.client_id = c.id ORDER BY s.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $topProducts = db()->query("SELECT p.name, SUM(sd.quantity) as quantity_sold, SUM(sd.subtotal) as total_vendido FROM sale_details sd JOIN products p ON sd.product_id = p.id JOIN sales s ON sd.sale_id = s.id WHERE s.created_at >= date('now', '-30 days') GROUP BY p.id ORDER BY total_vendido DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $salesByUser = db()->query("SELECT u.name, COUNT(*) as ventas, COALESCE(SUM(s.total), 0) as total FROM users u LEFT JOIN sales s ON u.id = s.user_id AND s.created_at >= date('now', '-30 days') GROUP BY u.id ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
    $expensesMonth = db()->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')")->fetch(PDO::FETCH_ASSOC);
    $overdueReceivables = db()->query("SELECT * FROM accounts_receivable WHERE status = 'pendiente'")->fetchAll(PDO::FETCH_ASSOC);
    $overduePayables = db()->query("SELECT * FROM accounts_payable WHERE status = 'pendiente'")->fetchAll(PDO::FETCH_ASSOC);
    $totalPayable = db()->query("SELECT COALESCE(SUM(amount - COALESCE(paid_amount, 0)), 0) as total, COUNT(*) as cnt FROM accounts_payable WHERE status = 'pendiente'")->fetch(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/views/dashboard.php';
    exit;
}

if (strpos($uri, '/products') === 0) {
    require __DIR__ . '/views/products/index.php';
    exit;
}

if (strpos($uri, '/categories') === 0) {
    require __DIR__ . '/views/categories/index.php';
    exit;
}

if (strpos($uri, '/clients') === 0) {
    require __DIR__ . '/views/clients/index.php';
    exit;
}

if (strpos($uri, '/providers') === 0) {
    require __DIR__ . '/views/providers/index.php';
    exit;
}

if (strpos($uri, '/sales') === 0) {
    require __DIR__ . '/views/sales/index.php';
    exit;
}

if (strpos($uri, '/pos') === 0) {
    require __DIR__ . '/views/pos/index.php';
    exit;
}

if (strpos($uri, '/receivable') === 0) {
    require __DIR__ . '/views/receivable/index.php';
    exit;
}

if (strpos($uri, '/reports') === 0) {
    require __DIR__ . '/views/reports/index.php';
    exit;
}

if (strpos($uri, '/settings') === 0) {
    require __DIR__ . '/views/settings/index.php';
    exit;
}

if (strpos($uri, '/expenses') === 0) {
    require __DIR__ . '/views/expenses/index.php';
    exit;
}

if (strpos($uri, '/quotes') === 0) {
    require __DIR__ . '/views/quotes/index.php';
    exit;
}

if (strpos($uri, '/credit_notes') === 0) {
    require __DIR__ . '/views/credit_notes/index.php';
    exit;
}

if (strpos($uri, '/purchases') === 0) {
    require __DIR__ . '/views/purchases/index.php';
    exit;
}

if (strpos($uri, '/payable') === 0) {
    require __DIR__ . '/views/payable/index.php';
    exit;
}

if (strpos($uri, '/cash') === 0) {
    require __DIR__ . '/views/cash/index.php';
    exit;
}

if (strpos($uri, '/backup') === 0) {
    require __DIR__ . '/views/backup/index.php';
    exit;
}

if (strpos($uri, '/users') === 0) {
    require __DIR__ . '/views/users/index.php';
    exit;
}

echo "404 - Página no encontrada";
