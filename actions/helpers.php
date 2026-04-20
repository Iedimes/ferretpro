<?php
session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/ferre/ferrepro/public');
}

require_once dirname(__DIR__) . '/class/Database.php';

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

function base_url($path = '') {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function redirect($path) {
    header("Location: " . base_url($path));
    exit;
}

function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    $base = BASE_URL === '/' ? '' : BASE_URL;
    $path = str_replace($base, '', parse_url($referer, PHP_URL_PATH));
    redirect($path ?: '/');
}

function json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function old($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function csrf() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function config($key, $default = null) {
    $stmt = db()->prepare("SELECT value FROM settings WHERE key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['value'] : $default;
}

function formatMoney($amount) {
    return 'Gs. ' . number_format($amount, 0, ',', '.');
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function getDefaultBranchAndPOS() {
    $defaultBranchId = intval(config('default_branch_id', 1));
    $defaultPosTerminalId = intval(config('default_pos_terminal_id', 1));
    
    return [
        'branch_id' => $defaultBranchId,
        'pos_terminal_id' => $defaultPosTerminalId
    ];
}

function getCurrentUserBranchAndPOS() {
    // First try to get from session
    if (isset($_SESSION['branch_id']) && isset($_SESSION['pos_terminal_id'])) {
        return [
            'branch_id' => intval($_SESSION['branch_id']),
            'pos_terminal_id' => intval($_SESSION['pos_terminal_id'])
        ];
    }
    
    // Then try to get from database
    if (auth()) {
        $stmt = db()->prepare("SELECT branch_id, pos_terminal_id FROM users WHERE id = ?");
        $stmt->execute([auth()]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $user['branch_id'] && $user['pos_terminal_id']) {
            $_SESSION['branch_id'] = $user['branch_id'];
            $_SESSION['pos_terminal_id'] = $user['pos_terminal_id'];
            return [
                'branch_id' => intval($user['branch_id']),
                'pos_terminal_id' => intval($user['pos_terminal_id'])
            ];
        }
    }
    
    // Fallback to default
    return getDefaultBranchAndPOS();
}
