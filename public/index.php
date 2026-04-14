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
        $recentSales = db()->query("SELECT s.*, u.name as user_name, c.name as client_name FROM sales s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN clients c ON s.client_id = c.id ORDER BY s.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        
        // Cuentas por cobrar vencidas
        $overdueReceivables = db()->query("
            SELECT ar.*, c.name as client_name, s.id as sale_id
            FROM accounts_receivable ar
            JOIN clients c ON ar.client_id = c.id
            LEFT JOIN sales s ON ar.sale_id = s.id
            WHERE ar.status = 'pendiente' AND DATE(ar.due_date) < DATE('now')
            ORDER BY ar.due_date ASC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        view('dashboard', compact('salesToday', 'productsLow', 'clientsDebt', 'salesMonth', 'recentSales', 'overdueReceivables'));
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
