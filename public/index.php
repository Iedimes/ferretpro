<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_URL', '/ferretpro/public');

require_once dirname(__DIR__) . '/class/Database.php';
require_once dirname(__DIR__) . '/class/Auth.php';
require_once dirname(__DIR__) . '/class/Validator.php';
require_once dirname(__DIR__) . '/class/Flash.php';
require_once dirname(__DIR__) . '/class/Format.php';

session_start();

function db() {
    return Database::getInstance();
}

function auth() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function user() {
    if (!auth()) return null;
    $stmt = db()->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->execute([auth()]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function flash($type, $message) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type] = $message;
}

function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

function view($file, $data = []) {
    extract($data);
    $content = null;
    ob_start();
    include dirname(__DIR__) . '/views/' . $file . '.php';
    if ($content === null) {
        // If the view didn't set $content, capture output buffering
        $content = ob_get_clean();
    } else {
        // If the view set $content, just discard the output buffer
        ob_end_clean();
    }
    include dirname(__DIR__) . '/views/layouts/main.php';
    exit;
}

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'list';

if ($page === 'login_post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is locked out
    if ($user && Auth::isUserLockedOut($user)) {
        Auth::logLoginAttempt($user['id'], 'failed', 'Account locked due to failed attempts');
        header('Location: ?page=login&error=Cuenta bloqueada. Intente más tarde.');
        exit;
    }
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        Auth::resetFailedAttempts($user['id']);
        Auth::logLoginAttempt($user['id'], 'success');
        
        header('Location: ?page=dashboard');
        exit;
    }
    
    // Record failed attempt
    if ($user) {
        Auth::recordFailedAttempt($email);
        Auth::logLoginAttempt($user['id'], 'failed', 'Invalid password');
    } else {
        Auth::logLoginAttempt(0, 'failed', 'User not found');
    }
    
    header('Location: ?page=login&error=Credenciales inválidas');
    exit;
}

if ($page === 'logout') {
    session_destroy();
    header('Location: ?page=login');
    exit;
}

if ($page === 'login') {
    include dirname(__DIR__) . '/views/auth/login.php';
    exit;
}

// Handle password reset request (forgot password)
if ($page === 'forgot_password_post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ?page=forgot_password&error=Email inválido');
        exit;
    }
    
    // Generate reset token
    $token = Auth::generatePasswordResetToken($email);
    
    if ($token) {
        // In a real system, send this link via email
        // For now, we'll show a success message with a link for testing
        $resetLink = BASE_URL . '?page=reset_password&token=' . urlencode($token);
        header('Location: ?page=forgot_password&success=Link de reinicio generado. Link: ' . urlencode($resetLink));
    } else {
        // Don't reveal whether the email exists for security
        header('Location: ?page=forgot_password&success=Si el email existe en el sistema, recibirá un link para reiniciar su contraseña');
    }
    exit;
}

// Handle password reset page and actions
if ($page === 'forgot_password') {
    include dirname(__DIR__) . '/views/auth/forgot_password.php';
    exit;
}

if ($page === 'reset_password' && isset($_GET['token'])) {
    $resetData = Auth::verifyPasswordResetToken($_GET['token']);
    if (!$resetData) {
        header('Location: ?page=login&error=Token de reinicio inválido o expirado');
        exit;
    }
    include dirname(__DIR__) . '/views/auth/reset_password.php';
    exit;
}

if ($page === 'reset_password_post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['password_confirm'] ?? '';
    
    if ($newPassword !== $confirmPassword) {
        header('Location: ?page=reset_password&token=' . urlencode($token) . '&error=Las contraseñas no coinciden');
        exit;
    }
    
    if (Auth::resetPasswordWithToken($token, $newPassword)) {
        header('Location: ?page=login&success=Contraseña reiniciada. Intente ingresar nuevamente');
        exit;
    } else {
        header('Location: ?page=login&error=Error al reiniciar contraseña');
        exit;
    }
}

if ($page === 'test_simple') {
    include dirname(__DIR__) . '/views/test_simple.php';
    exit;
}

if ($page === 'test_modules') {
    include dirname(__DIR__) . '/views/test_modules.php';
    exit;
}

if ($page === 'test_data') {
    include dirname(__DIR__) . '/views/test_data.php';
    exit;
}

if ($page === 'test_caja') {
    include dirname(__DIR__) . '/views/test_caja.php';
    exit;
}

if (!auth()) {
    header('Location: ?page=login');
    exit;
}

// Check session timeout (15 minutes of inactivity)
$session_timeout = 15 * 60; // 15 minutes
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $session_timeout)) {
    session_destroy();
    header('Location: ?page=login&error=Sesión expirada por inactividad');
    exit;
}

// Update last activity time
$_SESSION['login_time'] = time();

switch ($page) {
    case 'home':
    case 'dashboard':
        $salesToday = db()->query("SELECT COUNT(*), COALESCE(SUM(total), 0) FROM sales WHERE DATE(created_at) = DATE('now')")->fetch(PDO::FETCH_ASSOC);
        $productsLow = db()->query("SELECT COUNT(*) FROM products WHERE stock <= min_stock AND active = 1")->fetch(PDO::FETCH_ASSOC);
        $clientsDebt = db()->query("SELECT COUNT(*), COALESCE(SUM(balance), 0) FROM clients WHERE balance > 0")->fetch(PDO::FETCH_ASSOC);
        $salesMonth = db()->query("SELECT COUNT(*), COALESCE(SUM(total), 0) FROM sales WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')")->fetch(PDO::FETCH_ASSOC);
        
        // Products más vendidos del mes
        $topProducts = db()->query("
            SELECT p.name, SUM(sd.quantity) as quantity_sold, SUM(sd.subtotal) as total_vendido
            FROM sale_details sd
            JOIN products p ON sd.product_id = p.id
            JOIN sales s ON sd.sale_id = s.id
            WHERE strftime('%Y-%m', s.created_at) = strftime('%Y-%m', 'now')
            GROUP BY p.id
            ORDER BY quantity_sold DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Ventas por vendedor
        $salesByUser = db()->query("
            SELECT u.name, COUNT(s.id) as ventas, COALESCE(SUM(s.total), 0) as total
            FROM sales s
            JOIN users u ON s.user_id = u.id
            WHERE strftime('%Y-%m', s.created_at) = strftime('%Y-%m', 'now')
            GROUP BY u.id
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Gastos del mes
        $expensesMonth = db()->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')")->fetch(PDO::FETCH_ASSOC);
        
        $recentSales = db()->query("SELECT s.*, u.name as user_name, c.name as client_name FROM sales s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN clients c ON s.client_id = c.id ORDER BY s.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        
        // Cuentas por cobrar pendientes
        $overdueReceivables = db()->query("
            SELECT ar.*, c.name as client_name, s.id as sale_id
            FROM accounts_receivable ar
            JOIN clients c ON ar.client_id = c.id
            LEFT JOIN sales s ON ar.sale_id = s.id
            WHERE ar.status = 'pendiente'
            ORDER BY ar.due_date ASC
            LIMIT 15
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Cuentas por pagar pendientes
        $overduePayables = db()->query("
            SELECT ap.*, p.name as provider_name
            FROM accounts_payable ap
            JOIN providers p ON ap.provider_id = p.id
            WHERE ap.status = 'pendiente'
            ORDER BY ap.due_date ASC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        view('dashboard', compact('salesToday', 'productsLow', 'clientsDebt', 'salesMonth', 'recentSales', 'overdueReceivables', 'overduePayables', 'topProducts', 'salesByUser', 'expensesMonth'));
        break;
        
    case 'pos':
        $categories = db()->query("SELECT * FROM categories WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $clients = db()->query("SELECT * FROM clients WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $products = db()->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.active = 1 AND p.stock > 0 ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);
        view('pos/index', compact('categories', 'clients', 'products'));
        break;
        
    case 'products':
        view('products/index');
        break;
        
    case 'clients':
        view('clients/index');
        break;
        
    case 'providers':
        view('providers/index');
        break;
        
    case 'categories':
        view('categories/index');
        break;
        
    case 'sales':
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $edit_sale_id = intval($_GET['id']);
            view('sales/edit', ['edit_sale_id' => $edit_sale_id]);
            break;
        }
        if (isset($_GET['action']) && $_GET['action'] === 'deliver' && isset($_GET['id'])) {
            $deliver_id = intval($_GET['id']);
            $stmt = db()->prepare("UPDATE sales SET delivery_type = 'mostrador' WHERE id = ?");
            $stmt->execute([$deliver_id]);
            flash('success', 'Venta marcada como entregada');
            header('Location: ?page=sales');
            exit;
        }
        if (isset($_GET['action']) && $_GET['action'] === 'print' && isset($_GET['id'])) {
            $print_sale_id = intval($_GET['id']);
            include dirname(__DIR__) . '/views/invoice/print.php';
            exit;
        }
        $sales = db()->query("SELECT s.*, u.name as user_name, c.name as client_name FROM sales s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN clients c ON s.client_id = c.id ORDER BY s.id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        view('sales/index', compact('sales'));
        break;
        
    case 'receivable':
        $accounts = db()->query("SELECT ar.*, c.name as client_name FROM accounts_receivable ar JOIN clients c ON ar.client_id = c.id WHERE ar.status != 'cancelada' ORDER BY ar.id DESC")->fetchAll(PDO::FETCH_ASSOC);
        view('receivable/index', compact('accounts'));
        break;
        
    case 'payable':
        if ($action === 'pay' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ap_id = intval($_GET['id']);
            $ap = db()->prepare("SELECT * FROM accounts_payable WHERE id = ?");
            $ap->execute([$ap_id]);
            $ap = $ap->fetch(PDO::FETCH_ASSOC);
            
            if (!$ap || $ap['status'] !== 'pendiente') {
                Flash::error('Esta cuenta ya está pagada');
                header('Location: ?page=payable');
                exit;
            }
            
            $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
            $cuenta = $_POST['cuenta'] ?? 'caja';
            $bancoOrigen = $_POST['banco_origen'] ?? '';
            $bancoDestino = $_POST['banco_destino'] ?? '';
            $cuentaDestino = $_POST['cuenta_destino'] ?? '';
            $referencia = $_POST['referencia'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            $notesFull = trim($bancoOrigen . ' -> ' . $bancoDestino . ' ' . $cuentaDestino . ' Ref: ' . $referencia . ' ' . $notes);
            
            $stmt = db()->prepare("UPDATE accounts_payable SET status = 'pagado', paid_amount = amount, notes = ? WHERE id = ?");
            $stmt->execute([$notesFull, $ap_id]);
            
            if ($ap['purchase_id']) {
                $stmtPurchase = db()->prepare("UPDATE purchases SET status = 'paid' WHERE id = ?");
                $stmtPurchase->execute([$ap['purchase_id']]);
            }
            
            $cashRegister = db()->query("SELECT id FROM cash_register WHERE status = 'open' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($cashRegister) {
                $stmtCash = db()->prepare("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia) VALUES (?, ?, 'out', ?, ?, ?, ?, ?)");
                $stmtCash->execute([$cashRegister['id'], auth(), $ap['amount'], 'Pago CxP #' . $ap_id, $paymentMethod, $cuenta, $referencia]);
            }
            
            Flash::success('Cuenta pagada');
            header('Location: ?page=payable');
            exit;
        }
        
        $accounts = db()->query("SELECT ap.*, p.name as provider_name FROM accounts_payable ap JOIN providers p ON ap.provider_id = p.id WHERE ap.status != 'cancelada' ORDER BY ap.due_date ASC")->fetchAll(PDO::FETCH_ASSOC);
        view('payable/index', compact('accounts'));
        break;
        
    case 'expenses':
        if ($action === 'new') {
            view('expenses/edit');
            break;
        }
        
        if ($action === 'delete' && isset($_GET['id'])) {
            $stmt = db()->prepare("DELETE FROM expenses WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            Flash::success('Gasto eliminado');
            header('Location: ?page=expenses');
            exit;
        }
        
        $expenses = db()->query("SELECT e.*, u.name as user_name FROM expenses e LEFT JOIN users u ON e.user_id = u.id ORDER BY e.id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        view('expenses/index', compact('expenses'));
        break;
        
    case 'expenses_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $category = $_POST['category'] ?? '';
                $description = $_POST['description'] ?? '';
                $amount = floatval($_POST['amount'] ?? 0);
                $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
                $cuenta = $_POST['cuenta'] ?? 'caja';
                $referencia = $_POST['referencia'] ?? '';
                $date = $_POST['date'] ?? date('Y-m-d');
                $userId = auth();
                
                if (!$category || !$amount) {
                    Flash::error('Debe completar categoría y monto');
                    header('Location: ?page=expenses&action=new');
                    exit;
                }
                
                $stmt = db()->prepare("INSERT INTO expenses (user_id, category, description, amount, payment_method, cuenta, referencia, date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))");
                $stmt->execute([$userId, $category, $description, $amount, $paymentMethod, $cuenta, $referencia, $date]);
                $expenseId = db()->lastInsertId();
                
                // Registrar automáticamente en caja
                $cashRegister = db()->query("SELECT id FROM cash_register WHERE status = 'open' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                if ($cashRegister) {
                    $stmtCash = db()->prepare("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia) VALUES (?, ?, 'out', ?, ?, ?, ?, ?)");
                    $stmtCash->execute([$cashRegister['id'], $userId, $amount, 'Gasto #' . $expenseId . ' - ' . $category, $paymentMethod, $cuenta, $referencia]);
                }
                
                Flash::success('Gasto registrado');
                header('Location: ?page=expenses');
                exit;
            } catch (Exception $e) {
                Flash::error('Error: ' . $e->getMessage());
                header('Location: ?page=expenses&action=new');
                exit;
            }
        }
        header('Location: ?page=expenses');
        exit;
        
    case 'reports':
        $type = $_GET['type'] ?? 'sales';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $salesData = db()->query("SELECT id as fecha, COUNT(*) as cantidad, SUM(total) as total FROM sales GROUP BY id ORDER BY fecha")->fetchAll(PDO::FETCH_ASSOC);
        $productsData = db()->query("SELECT p.name, SUM(sd.quantity) as cantidad_vendida, SUM(sd.subtotal) as total_vendido FROM sale_details sd JOIN products p ON sd.product_id = p.id GROUP BY p.id ORDER BY total_vendido DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        $lowStock = db()->query("SELECT * FROM products WHERE active = 1 AND stock <= min_stock ORDER BY stock")->fetchAll(PDO::FETCH_ASSOC);
        
        view('reports/index', compact('salesData', 'productsData', 'lowStock', 'type', 'dateFrom', 'dateTo'));
        break;
        
    case 'settings':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                $stmt = db()->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
            }
            Flash::success('Configuración guardada correctamente');
        }
        $settingsRows = db()->query("SELECT key, value FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        view('settings/index', compact('settings'));
        break;
        
    case 'profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_info') {
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $userId = auth();
                
                // Validate email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Flash::error('Email inválido');
                    header('Location: ?page=profile');
                    exit;
                }
                
                // Check if email is already used by another user
                $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                    Flash::error('Email ya está registrado');
                    header('Location: ?page=profile');
                    exit;
                }
                
                $stmt = db()->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $userId]);
                
                Flash::success('Información actualizada correctamente');
                header('Location: ?page=profile');
                exit;
            }
            
            if ($action === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (strlen($newPassword) < 8) {
                    Flash::error('La contraseña debe tener al menos 8 caracteres');
                    header('Location: ?page=profile');
                    exit;
                }
                
                if ($newPassword !== $confirmPassword) {
                    Flash::error('Las contraseñas no coinciden');
                    header('Location: ?page=profile');
                    exit;
                }
                
                if (Auth::changePassword(auth(), $currentPassword, $newPassword)) {
                    Flash::success('Contraseña cambiada correctamente');
                    header('Location: ?page=profile');
                    exit;
                } else {
                    Flash::error('Contraseña actual incorrecta');
                    header('Location: ?page=profile');
                    exit;
                }
            }
        }
        
        $userInfo = user();
        $loginHistory = Auth::getLoginHistory(auth(), 10);
        view('profile', compact('userInfo', 'loginHistory'));
        break;
        
    case 'quotes':
        if ($action === 'new' || $action === 'edit') {
            $clients = db()->query("SELECT * FROM clients WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
            $products = db()->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.active = 1 ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);
            
            $edit_quote = null;
            $quote_details = [];
            if ($action === 'edit' && isset($_GET['id'])) {
                $edit_quote_id = intval($_GET['id']);
                $edit_quote = db()->prepare("SELECT * FROM quotes WHERE id = ?");
                $edit_quote->execute([$edit_quote_id]);
                $edit_quote = $edit_quote->fetch(PDO::FETCH_ASSOC);
                
                $quote_details = db()->prepare("SELECT qd.*, p.name, p.code FROM quote_details qd JOIN products p ON qd.product_id = p.id WHERE qd.quote_id = ?");
                $quote_details->execute([$edit_quote_id]);
                $quote_details = $quote_details->fetchAll(PDO::FETCH_ASSOC);
            }
            
            view('quotes/edit', compact('clients', 'products', 'edit_quote', 'quote_details'));
            break;
        }
        
        if ($action === 'view' && isset($_GET['id'])) {
            $quote_id = intval($_GET['id']);
            $quote = db()->prepare("SELECT q.*, c.name as client_name, u.name as user_name FROM quotes q LEFT JOIN clients c ON q.client_id = c.id LEFT JOIN users u ON q.user_id = u.id WHERE q.id = ?");
            $quote->execute([$quote_id]);
            $quote = $quote->fetch(PDO::FETCH_ASSOC);
            
            $details = db()->prepare("SELECT qd.*, p.name, p.code FROM quote_details qd JOIN products p ON qd.product_id = p.id WHERE qd.quote_id = ?");
            $details->execute([$quote_id]);
            $details = $details->fetchAll(PDO::FETCH_ASSOC);
            
            view('quotes/view', compact('quote', 'details'));
            break;
        }
        
        if ($action === 'convert' && isset($_GET['id'])) {
            $quote_id = intval($_GET['id']);
            $quote = db()->prepare("SELECT * FROM quotes WHERE id = ?");
            $quote->execute([$quote_id]);
            $quote = $quote->fetch(PDO::FETCH_ASSOC);
            
            if (!$quote || $quote['status'] !== 'pending') {
                Flash::error('No se puede convertir esta cotización');
                header('Location: ?page=quotes');
                exit;
            }
            
            $details = db()->prepare("SELECT * FROM quote_details WHERE quote_id = ?");
            $details->execute([$quote_id]);
            $details = $details->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = db()->prepare("INSERT INTO sales (user_id, client_id, subtotal, discount, total, type, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, 'contado', 'efectivo', 'pagada', datetime('now'))");
            $stmt->execute([$quote['user_id'], $quote['client_id'], $quote['subtotal'], $quote['discount'], $quote['total']]);
            $saleId = db()->lastInsertId();
            
            foreach ($details as $item) {
                $stmtDetail = db()->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmtDetail->execute([$saleId, $item['product_id'], $item['quantity'], $item['unit_price'], $item['subtotal']]);
                
                $stmtStock = db()->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmtStock->execute([$item['quantity'], $item['product_id']]);
            }
            
            $stmtUpdate = db()->prepare("UPDATE quotes SET status = 'converted', converted_sale_id = ? WHERE id = ?");
            $stmtUpdate->execute([$saleId, $quote_id]);
            
            Flash::success('Cotización convertida a venta #' . $saleId);
            header('Location: ?page=sales&action=print&id=' . $saleId);
            exit;
        }
        
        if ($action === 'delete' && isset($_GET['id'])) {
            $quote_id = intval($_GET['id']);
            $stmt = db()->prepare("DELETE FROM quote_details WHERE quote_id = ?");
            $stmt->execute([$quote_id]);
            $stmt = db()->prepare("DELETE FROM quotes WHERE id = ?");
            $stmt->execute([$quote_id]);
            Flash::success('Cotización eliminada');
            header('Location: ?page=quotes');
            exit;
        }
        
        if ($action === 'print' && isset($_GET['id'])) {
            $quote_id = intval($_GET['id']);
            $quote = db()->prepare("SELECT q.*, c.name as client_name, c.document as client_document, u.name as user_name FROM quotes q LEFT JOIN clients c ON q.client_id = c.id LEFT JOIN users u ON q.user_id = u.id WHERE q.id = ?");
            $quote->execute([$quote_id]);
            $quote = $quote->fetch(PDO::FETCH_ASSOC);
            
            $details = db()->prepare("SELECT qd.*, p.name, p.code FROM quote_details qd JOIN products p ON qd.product_id = p.id WHERE qd.quote_id = ?");
            $details->execute([$quote_id]);
            $details = $details->fetchAll(PDO::FETCH_ASSOC);
            
            include dirname(__DIR__) . '/views/quotes/print.php';
            exit;
        }
        
        view('quotes/index');
        break;
        
case 'quotes_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $clientId = $_POST['client_id'] ?? null;
                $validityDays = intval($_POST['validity_days'] ?? 30);
                $discount = floatval($_POST['discount'] ?? 0);
                $notes = $_POST['notes'] ?? '';
                $items = $_POST['items'] ?? [];
                $userId = auth();
                
                if (empty($items)) {
                    Flash::error('Debe agregar al menos un producto');
                    header('Location: ?page=quotes&action=new');
                    exit;
                }
                
                $subtotal = 0;
                foreach ($items as $item) {
                    $qty = intval($item['qty'] ?? 1);
                    $price = floatval($item['price'] ?? 0);
                    $subtotal += $price * $qty;
                }
                
                $discountAmount = $subtotal * ($discount / 100);
                $total = $subtotal - $discountAmount;
                $expiresAt = date('Y-m-d', strtotime("+{$validityDays} days"));
                
                if (isset($_POST['quote_id'])) {
                    $quoteId = intval($_POST['quote_id']);
                    
                    $stmt = db()->prepare("UPDATE quotes SET client_id = ?, validity_days = ?, discount = ?, notes = ?, expires_at = ?, subtotal = ?, total = ? WHERE id = ?");
                    $stmt->execute([$clientId, $validityDays, $discountAmount, $notes, $expiresAt, $subtotal, $total, $quoteId]);
                    
                    $stmt = db()->prepare("DELETE FROM quote_details WHERE quote_id = ?");
                    $stmt->execute([$quoteId]);
                } else {
                    $clientName = '';
                    $clientDocument = '';
                    if ($clientId) {
                        $stmtClient = db()->prepare("SELECT name, document FROM clients WHERE id = ?");
                        $stmtClient->execute([$clientId]);
                        $clientData = $stmtClient->fetch(PDO::FETCH_ASSOC);
                        $clientName = $clientData['name'];
                        $clientDocument = $clientData['document'];
                    }
                    
                    $stmt = db()->prepare("INSERT INTO quotes (client_id, client_name, client_document, user_id, validity_days, discount, notes, expires_at, subtotal, total, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', datetime('now'))");
                    $stmt->execute([$clientId, $clientName, $clientDocument, $userId, $validityDays, $discountAmount, $notes, $expiresAt, $subtotal, $total]);
                    $quoteId = db()->lastInsertId();
                }
                
                foreach ($items as $productId => $item) {
                    $qty = intval($item['qty'] ?? 1);
                    $price = floatval($item['price'] ?? 0);
                    $itemSubtotal = $price * $qty;
                    
                    $stmtDetail = db()->prepare("INSERT INTO quote_details (quote_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmtDetail->execute([$quoteId, $productId, $qty, $price, $itemSubtotal]);
                }
                
                Flash::success('Cotización guardada correctamente');
                header('Location: ?page=quotes&action=view&id=' . $quoteId);
                exit;
            } catch (Exception $e) {
                Flash::error('Error al guardar: ' . $e->getMessage());
                header('Location: ?page=quotes&action=new');
                exit;
            }
        }
        header('Location: ?page=quotes');
        exit;
        
case 'sale_products':
        $saleId = intval($_GET['sale_id'] ?? 0);
        $products = db()->prepare("SELECT sd.*, p.name, p.code FROM sale_details sd JOIN products p ON sd.product_id = p.id WHERE sd.sale_id = ?");
        $products->execute([$saleId]);
        $products = $products->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
        
    case 'credit_notes':
        if ($action === 'new') {
            $sales = db()->query("SELECT s.*, c.name as client_name FROM sales s LEFT JOIN clients c ON s.client_id = c.id ORDER BY s.id DESC")->fetchAll(PDO::FETCH_ASSOC);
            view('credit_notes/edit', compact('sales'));
            break;
        }
        
        if ($action === 'view' && isset($_GET['id'])) {
            $cn_id = intval($_GET['id']);
            $cn = db()->prepare("SELECT cn.*, c.name as client_name, u.name as user_name, s.id as sale_num FROM credit_notes cn LEFT JOIN clients c ON cn.client_id = c.id LEFT JOIN users u ON cn.user_id = u.id LEFT JOIN sales s ON cn.sale_id = s.id WHERE cn.id = ?");
            $cn->execute([$cn_id]);
            $cn = $cn->fetch(PDO::FETCH_ASSOC);
            
            $details = db()->prepare("SELECT cnd.*, p.name, p.code FROM credit_note_details cnd JOIN products p ON cnd.product_id = p.id WHERE cnd.credit_note_id = ?");
            $details->execute([$cn_id]);
            $details = $details->fetchAll(PDO::FETCH_ASSOC);
            
            view('credit_notes/view', compact('cn', 'details'));
            break;
        }
        
        if ($action === 'apply' && isset($_GET['id'])) {
            $cn_id = intval($_GET['id']);
            $cn = db()->prepare("SELECT * FROM credit_notes WHERE id = ?");
            $cn->execute([$cn_id]);
            $cn = $cn->fetch(PDO::FETCH_ASSOC);
            
            if (!$cn || $cn['status'] !== 'pending') {
                Flash::error('No se puede aplicar esta nota de crédito');
                header('Location: ?page=credit_notes');
                exit;
            }
            
            $details = db()->prepare("SELECT * FROM credit_note_details WHERE credit_note_id = ?");
            $details->execute([$cn_id]);
            $details = $details->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($details as $item) {
                $stmtStock = db()->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $stmtStock->execute([$item['quantity'], $item['product_id']]);
                
                $stmtMove = db()->prepare("INSERT INTO stock_movements (product_id, type, quantity, reference_type, reference_id, notes) VALUES (?, 'entrada', ?, 'credit_note', ?, 'Devolución por nota de crédito')");
                $stmtMove->execute([$item['product_id'], $item['quantity'], $cn_id]);
            }
            
            if ($cn['client_id']) {
                $stmtClient = db()->prepare("UPDATE clients SET balance = balance - ? WHERE id = ?");
                $stmtClient->execute([$cn['total'], $cn['client_id']]);
            }
            
            $stmt = db()->prepare("UPDATE credit_notes SET status = 'applied' WHERE id = ?");
            $stmt->execute([$cn_id]);
            
            Flash::success('Nota de crédito aplicada. Stock reintegrado.');
            header('Location: ?page=credit_notes&action=view&id=' . $cn_id);
            exit;
        }
        
        if ($action === 'cancel' && isset($_GET['id'])) {
            $cn_id = intval($_GET['id']);
            $stmt = db()->prepare("UPDATE credit_notes SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$cn_id]);
            Flash::success('Nota de crédito anulada');
            header('Location: ?page=credit_notes');
            exit;
        }
        
        view('credit_notes/index');
        break;
        
    case 'credit_notes_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $saleId = intval($_POST['sale_id'] ?? 0);
                $reason = $_POST['reason'] ?? '';
                $items = $_POST['items'] ?? [];
                $userId = auth();
                
                if (!$saleId || empty($reason)) {
                    Flash::error('Debe seleccionar una venta y especificar el motivo');
                    header('Location: ?page=credit_notes&action=new');
                    exit;
                }
                
                if (empty($items)) {
                    Flash::error('Debe seleccionar al menos un producto');
                    header('Location: ?page=credit_notes&action=new');
                    exit;
                }
                
                $stmtSale = db()->prepare("SELECT * FROM sales WHERE id = ?");
                $stmtSale->execute([$saleId]);
                $sale = $stmtSale->fetch(PDO::FETCH_ASSOC);
                
                $subtotal = 0;
                foreach ($items as $item) {
                    $qty = intval($item['qty'] ?? 1);
                    $price = floatval($item['price'] ?? 0);
                    $subtotal += $price * $qty;
                }
                
                $stmt = db()->prepare("INSERT INTO credit_notes (sale_id, user_id, client_id, reason, subtotal, total, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', datetime('now'))");
                $stmt->execute([$saleId, $userId, $sale['client_id'], $reason, $subtotal, $subtotal]);
                $cnId = db()->lastInsertId();
                
                foreach ($items as $productId => $item) {
                    $qty = intval($item['qty'] ?? 1);
                    $price = floatval($item['price'] ?? 0);
                    $itemSubtotal = $price * $qty;
                    
                    $stmtDetail = db()->prepare("INSERT INTO credit_note_details (credit_note_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmtDetail->execute([$cnId, $productId, $qty, $price, $itemSubtotal]);
                }
                
                Flash::success('Nota de crédito creada correctamente');
                header('Location: ?page=credit_notes&action=view&id=' . $cnId);
                exit;
            } catch (Exception $e) {
                Flash::error('Error al crear: ' . $e->getMessage());
                header('Location: ?page=credit_notes&action=new');
                exit;
            }
        }
        header('Location: ?page=credit_notes');
        exit;
        
    case 'purchases':
        if ($action === 'new' || $action === 'edit') {
            $providers = db()->query("SELECT * FROM providers WHERE active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
            $products = db()->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.active = 1 ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);
            
            $edit_purchase = null;
            $purchase_details = [];
            if ($action === 'edit' && isset($_GET['id'])) {
                $edit_purchase_id = intval($_GET['id']);
                $edit_purchase = db()->prepare("SELECT * FROM purchases WHERE id = ?");
                $edit_purchase->execute([$edit_purchase_id]);
                $edit_purchase = $edit_purchase->fetch(PDO::FETCH_ASSOC);
                
                $purchase_details = db()->prepare("SELECT pd.*, p.name, p.code FROM purchase_details pd JOIN products p ON pd.product_id = p.id WHERE pd.purchase_id = ?");
                $purchase_details->execute([$edit_purchase_id]);
                $purchase_details = $purchase_details->fetchAll(PDO::FETCH_ASSOC);
            }
            
            view('purchases/edit', compact('providers', 'products', 'edit_purchase', 'purchase_details'));
            break;
        }
        
        if ($action === 'view' && isset($_GET['id'])) {
            $purchase_id = intval($_GET['id']);
            $purchase = db()->prepare("SELECT p.*, pr.name as provider_name, u.name as user_name FROM purchases p LEFT JOIN providers pr ON p.provider_id = pr.id LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ?");
            $purchase->execute([$purchase_id]);
            $purchase = $purchase->fetch(PDO::FETCH_ASSOC);
            
            $details = db()->prepare("SELECT pd.*, p.name, p.code FROM purchase_details pd JOIN products p ON pd.product_id = p.id WHERE pd.purchase_id = ?");
            $details->execute([$purchase_id]);
            $details = $details->fetchAll(PDO::FETCH_ASSOC);
            
            view('purchases/view', compact('purchase', 'details'));
            break;
        }
        
        if ($action === 'delete' && isset($_GET['id'])) {
            $purchase_id = intval($_GET['id']);
            
            db()->prepare("DELETE FROM purchase_details WHERE purchase_id = ?")->execute([$purchase_id]);
            db()->prepare("DELETE FROM accounts_payable WHERE purchase_id = ?")->execute([$purchase_id]);
            db()->prepare("DELETE FROM purchases WHERE id = ?")->execute([$purchase_id]);
            
            Flash::success('Compra eliminada');
            header('Location: ?page=purchases');
            exit;
        }
        
        if ($action === 'receive' && isset($_GET['id'])) {
            $purchase_id = intval($_GET['id']);
            $purchase = db()->prepare("SELECT * FROM purchases WHERE id = ?");
            $purchase->execute([$purchase_id]);
            $purchase = $purchase->fetch(PDO::FETCH_ASSOC);
            
            if (!$purchase || $purchase['status'] !== 'pending') {
                Flash::error('No se puede recibir esta compra');
                header('Location: ?page=purchases');
                exit;
            }
            
            $details = db()->prepare("SELECT * FROM purchase_details WHERE purchase_id = ?");
            $details->execute([$purchase_id]);
            $details = $details->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($details as $item) {
                $stmtStock = db()->prepare("UPDATE products SET stock = stock + ?, cost_price = ? WHERE id = ?");
                $stmtStock->execute([$item['quantity'], $item['unit_cost'], $item['product_id']]);
                
                $stmtMove = db()->prepare("INSERT INTO stock_movements (product_id, type, quantity, reference_type, reference_id, notes) VALUES (?, 'entrada', ?, 'purchase', ?, 'Entrada por compra')");
                $stmtMove->execute([$item['product_id'], $item['quantity'], $purchase_id]);
            }
            
            $stmt = db()->prepare("UPDATE purchases SET status = 'received' WHERE id = ?");
            $stmt->execute([$purchase_id]);
            
            Flash::success('Compra recibida. Stock actualizado.');
            header('Location: ?page=purchases&action=view&id=' . $purchase_id);
            exit;
        }
        
        view('purchases/index');
        break;
        
    case 'purchases_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $providerId = intval($_POST['provider_id'] ?? 0);
                $invoiceNumber = $_POST['invoice_number'] ?? '';
                $paymentMethod = $_POST['payment_method'] ?? 'contado';
                $items = $_POST['items'] ?? [];
                $userId = auth();
                
                if (!$providerId) {
                    Flash::error('Debe seleccionar un proveedor');
                    header('Location: ?page=purchases&action=new');
                    exit;
                }
                
                if (empty($items)) {
                    Flash::error('Debe agregar al menos un producto');
                    header('Location: ?page=purchases&action=new');
                    exit;
                }
                
                $subtotal = 0;
                foreach ($items as $item) {
                    $qty = intval($item['qty'] ?? 1);
                    $cost = floatval($item['cost'] ?? 0);
                    $subtotal += $cost * $qty;
                }
                
                $stmt = db()->prepare("INSERT INTO purchases (provider_id, user_id, invoice_number, subtotal, discount, total, payment_method, status, created_at) VALUES (?, ?, ?, ?, 0, ?, ?, 'pending', datetime('now'))");
                $stmt->execute([$providerId, $userId, $invoiceNumber, $subtotal, $paymentMethod]);
                $purchaseId = db()->lastInsertId();
                
                if ($paymentMethod === 'credito') {
                    $dueDate = date('Y-m-d', strtotime('+30 days'));
                    $stmtCxP = db()->prepare("INSERT INTO accounts_payable (provider_id, purchase_id, amount, due_date, status, created_at) VALUES (?, ?, ?, ?, 'pendiente', datetime('now'))");
                    $stmtCxP->execute([$providerId, $purchaseId, $subtotal, $dueDate]);
                    
                    $stmtPurchase = db()->prepare("UPDATE purchases SET status = 'pending' WHERE id = ?");
                    $stmtPurchase->execute([$purchaseId]);
                }
                
                foreach ($items as $productId => $item) {
                    $qty = intval($item['qty'] ?? 1);
                    $cost = floatval($item['cost'] ?? 0);
                    $itemSubtotal = $cost * $qty;
                    
                    $stmtDetail = db()->prepare("INSERT INTO purchase_details (purchase_id, product_id, quantity, unit_cost, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmtDetail->execute([$purchaseId, $productId, $qty, $cost, $itemSubtotal]);
                }
                
                Flash::success('Compra registrada correctamente');
                header('Location: ?page=purchases&action=view&id=' . $purchaseId);
                exit;
            } catch (Exception $e) {
                Flash::error('Error al guardar: ' . $e->getMessage());
                header('Location: ?page=purchases&action=new');
                exit;
            }
        }
        header('Location: ?page=purchases');
        exit;
        
    case 'cash':
        if ($action === 'open' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $openingAmount = floatval($_POST['opening_amount'] ?? 0);
            $openingNotes = $_POST['opening_notes'] ?? '';
            $userId = auth();
            
            if ($openingAmount < 0) {
                Flash::error('El monto debe ser positivo');
                header('Location: ?page=cash');
                exit;
            }
            
            $stmt = db()->prepare("INSERT INTO cash_register (user_id, opening_amount, opening_notes, status, opened_at) VALUES (?, ?, ?, 'open', datetime('now'))");
            $stmt->execute([$userId, $openingAmount, $openingNotes]);
            
            Flash::success('Caja abierta con: ' . Format::money($openingAmount));
            header('Location: ?page=cash');
            exit;
        }
        
        if ($action === 'cancel' && !empty($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = db()->prepare("DELETE FROM cash_movements WHERE cash_register_id = ?");
            $stmt->execute([$id]);
            $stmt = db()->prepare("DELETE FROM cash_register WHERE id = ?");
            $stmt->execute([$id]);
            
            Flash::success('Caja cancelada');
            header('Location: ?page=cash');
            exit;
        }
        
        if ($action === 'edit_open' && !empty($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_GET['id']);
            $openingAmount = floatval($_POST['opening_amount'] ?? 0);
            $openingNotes = $_POST['opening_notes'] ?? '';
            
            $stmt = db()->prepare("UPDATE cash_register SET opening_amount = ?, opening_notes = ? WHERE id = ? AND status = 'open'");
            $stmt->execute([$openingAmount, $openingNotes, $id]);
            
            Flash::success('Apertura actualizada');
            header('Location: ?page=cash');
            exit;
        }
        
        if ($action === 'edit_open' && !empty($_GET['id'])) {
            $id = intval($_GET['id']);
            $cr = db()->query("SELECT * FROM cash_register WHERE id = $id AND status = 'open'")->fetch(PDO::FETCH_ASSOC);
            if ($cr) {
                view('cash/edit_open', compact('cr'));
                break;
            }
        }
        
        if ($action === 'movement' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? 'in';
            $amount = floatval($_POST['amount'] ?? 0);
            $description = $_POST['description'] ?? '';
            $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
            $cuenta = $_POST['cuenta'] ?? 'caja';
            $referencia = $_POST['referencia'] ?? '';
            $userId = auth();
            
            $cashRegister = db()->query("SELECT id FROM cash_register WHERE status = 'open' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            
            if (!$cashRegister) {
                Flash::error('No hay caja abierta');
                header('Location: ?page=cash');
                exit;
            }
            
            $stmt = db()->prepare("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cashRegister['id'], $userId, $type, $amount, $description, $paymentMethod, $cuenta, $referencia]);
            
            Flash::success('Movimiento registrado');
            header('Location: ?page=cash');
            exit;
        }
        
        if ($action === 'close' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $closingAmount = floatval($_POST['closing_amount'] ?? 0);
            $closingNotes = $_POST['closing_notes'] ?? '';
            $userId = auth();
            
            $cashRegister = db()->query("SELECT * FROM cash_register WHERE status = 'open' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            
            if (!$cashRegister) {
                Flash::error('No hay caja abierta');
                header('Location: ?page=cash');
                exit;
            }
            
            $movements = db()->query("SELECT SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) as total_in, SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as total_out FROM cash_movements WHERE cash_register_id = " . $cashRegister['id'])->fetch(PDO::FETCH_ASSOC);
            
            $expected = $cashRegister['opening_amount'] + ($movements['total_in'] ?? 0) - ($movements['total_out'] ?? 0);
            $difference = $closingAmount - $expected;
            
            $stmt = db()->prepare("UPDATE cash_register SET closing_amount = ?, closing_notes = ?, expected_amount = ?, difference = ?, status = 'closed', closed_at = datetime('now') WHERE id = ?");
            $stmt->execute([$closingAmount, $closingNotes, $expected, $difference, $cashRegister['id']]);
            
            Flash::success('Caja cerrada. Diferencia: ' . Format::money($difference));
            header('Location: ?page=cash');
            exit;
        }
        
        if ($action === 'view' && !empty($_GET['id'])) {
            view('cash/view');
            break;
        }
        
        view('cash/index');
        break;
        
    case 'backup':
        include dirname(__DIR__) . '/views/backup/index.php';
        break;
        
    
        
    case 'backup_create':
        try {
            $backupDir = dirname(__DIR__) . '/data/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            
            $backupFile = $backupDir . '/ferretpro_backup_' . date('Y-m-d_His') . '.sqlite';
            copy(dirname(__DIR__) . '/data/ferretpro.db', $backupFile);
            
            $stmt = db()->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
            $stmt->execute(['last_backup', date('Y-m-d H:i:s')]);
            
            header('Location: ?page=backup&success=Backup guardado: ' . basename($backupFile));
            exit;
        } catch (Exception $e) {
            header('Location: ?page=backup&error=Error: ' . $e->getMessage());
            exit;
        }
        break;
        
    case 'users':
        // Solo admin puede gestionar usuarios
        if (user()['role'] !== 'admin') {
            echo "Acceso denegado. Solo administradores pueden gestionar usuarios.";
            exit;
        }
        
        if ($action === 'toggle' && $_GET['id']) {
            $stmt = db()->prepare("UPDATE users SET active = NOT active WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header('Location: ?page=users');
            exit;
        }
        
        if ($action === 'new' || $action === 'edit') {
            view('users/edit', compact('action'));
        } else {
            $users = db()->query("SELECT id, name, email, role, active, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            view('users/index', compact('users'));
        }
        break;
        
    case 'pos_process':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $items = json_decode($_POST['items'] ?? '[]', true);
                $clientId = $_POST['client_id'] ?? null;
                $saleType = $_POST['sale_type'] ?? 'contado';
                $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
                $discount = floatval($_POST['discount'] ?? 0);
                $deliveryType = $_POST['delivery_type'] ?? 'mostrador';
                $userId = auth();
                
                if (!$userId) {
                    header('Location: ?page=pos&error=Debes estar autenticado');
                    exit;
                }
                
                if (empty($items)) {
                    header('Location: ?page=pos&error=Carrito vacío');
                    exit;
                }
                
                // Calcular total
                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += floatval($item['price']) * intval($item['qty']);
                }
                
                $discountAmount = $subtotal * ($discount / 100);
                $total = $subtotal - $discountAmount;
                
                // Determinar status según tipo de venta
                $saleStatus = ($saleType === 'contado') ? 'pagada' : 'pendiente';
                
                // Crear venta
                $stmt = db()->prepare("INSERT INTO sales (user_id, client_id, type, payment_method, subtotal, discount, total, delivery_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))");
                $stmt->execute([$userId, $clientId ?: null, $saleType, $paymentMethod, $subtotal, $discountAmount, $total, $deliveryType, $saleStatus]);
                $saleId = db()->lastInsertId();
                
                // Agregar items a la venta
                foreach ($items as $item) {
                    $productId = intval($item['id']);
                    $qty = intval($item['qty']);
                    $unitPrice = floatval($item['price']);
                    $itemSubtotal = $unitPrice * $qty;
                    
                    // Insertar detalle de venta
                    $stmtDetail = db()->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmtDetail->execute([$saleId, $productId, $qty, $unitPrice, $itemSubtotal]);
                    
                    // Actualizar stock del producto
                    $stmtStock = db()->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmtStock->execute([$qty, $productId]);
                }
                
                // Si es crédito, crear cuenta por cobrar y actualizar balance del cliente
                if ($saleType === 'credito' && $clientId) {
                    // Obtener días de crédito del cliente
                    $stmtClientCredit = db()->prepare("SELECT credit_days FROM clients WHERE id = ?");
                    $stmtClientCredit->execute([$clientId]);
                    $creditDays = $stmtClientCredit->fetchColumn() ?: 30;
                    
                    // Calcular fecha de vencimiento
                    $dueDate = date('Y-m-d', strtotime("+{$creditDays} days"));
                    
                    // Crear registro en cuentas por cobrar
                    $stmtReceivable = db()->prepare("INSERT INTO accounts_receivable (sale_id, client_id, amount, due_date, status, created_at) VALUES (?, ?, ?, ?, 'pendiente', datetime('now'))");
                    $stmtReceivable->execute([$saleId, $clientId, $total, $dueDate]);
                    
                    // Actualizar balance del cliente
                    $stmtClient = db()->prepare("UPDATE clients SET balance = balance + ? WHERE id = ?");
                    $stmtClient->execute([$total, $clientId]);
                }
                
                // Registrar en caja automáticamente si es venta al contado
                if ($saleType === 'contado') {
                    $cuenta = ($paymentMethod === 'efectivo') ? 'caja' : 'banco';
                    $reference = 'Venta #' . $saleId;
                    
                    $cashRegister = db()->query("SELECT id FROM cash_register WHERE status = 'open' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                    if ($cashRegister) {
                        $stmtCash = db()->prepare("INSERT INTO cash_movements (cash_register_id, user_id, type, amount, description, payment_method, cuenta, referencia) VALUES (?, ?, 'in', ?, ?, ?, ?, ?)");
                        $stmtCash->execute([$cashRegister['id'], $userId, $total, $reference, $paymentMethod, $cuenta, $reference]);
                    }
                }
                
                Flash::success('Venta registrada correctamente. ID: ' . $saleId);
                header('Location: ?page=pos');
                exit;
            } catch (Exception $e) {
                Flash::error('Error al procesar venta: ' . $e->getMessage());
                header('Location: ?page=pos');
                exit;
            }
        }
        header('Location: ?page=pos');
        exit;

}
?>
