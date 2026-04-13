<?php
/**
 * Sistema de Notificaciones para FerrePro
 */

class Flash {
    
    public static function set($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
    
    public static function get($type) {
        return $_SESSION['flash'][$type] ?? null;
    }
    
    public static function has($type) {
        return isset($_SESSION['flash'][$type]);
    }
    
    public static function clear() {
        unset($_SESSION['flash']);
    }
    
    // Convenience methods
    public static function success($message) {
        self::set('success', $message);
    }
    
    public static function error($message) {
        self::set('error', $message);
    }
    
    public static function warning($message) {
        self::set('warning', $message);
    }
    
    public static function info($message) {
        self::set('info', $message);
    }
    
    public static function renderAll() {
        if (!isset($_SESSION['flash'])) {
            return '';
        }
        
        $html = '';
        foreach ($_SESSION['flash'] as $type => $message) {
            $class = match($type) {
                'success' => 'alert-success',
                'error', 'danger' => 'alert-danger',
                'warning' => 'alert-warning',
                'info' => 'alert-info',
                default => 'alert-secondary'
            };
            
            $html .= '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
        
        self::clear();
        return $html;
    }
}

?>
