<?php
/**
 * Validaciones para FerrePro
 * Funciones para validar datos de entrada
 */

class Validator {
    private static $errors = [];
    
    public static function clear() {
        self::$errors = [];
    }
    
    public static function getErrors() {
        return self::$errors;
    }
    
    public static function hasErrors() {
        return count(self::$errors) > 0;
    }
    
    public static function required($field, $label = '') {
        $value = $_POST[$field] ?? null;
        if (empty($value)) {
            self::$errors[$field] = ($label ?: ucfirst($field)) . ' es requerido';
            return false;
        }
        return true;
    }
    
    public static function email($field, $label = 'Email') {
        if (!isset($_POST[$field])) return true;
        
        $value = $_POST[$field];
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[$field] = $label . ' debe ser un email válido';
            return false;
        }
        return true;
    }
    
    public static function numeric($field, $label = '') {
        if (!isset($_POST[$field])) return true;
        
        $value = $_POST[$field];
        if (!is_numeric($value)) {
            self::$errors[$field] = ($label ?: ucfirst($field)) . ' debe ser un número';
            return false;
        }
        return true;
    }
    
    public static function min($field, $min, $label = '') {
        if (!isset($_POST[$field])) return true;
        
        $value = $_POST[$field];
        if (strlen($value) < $min) {
            self::$errors[$field] = ($label ?: ucfirst($field)) . ' debe tener al menos ' . $min . ' caracteres';
            return false;
        }
        return true;
    }
    
    public static function max($field, $max, $label = '') {
        if (!isset($_POST[$field])) return true;
        
        $value = $_POST[$field];
        if (strlen($value) > $max) {
            self::$errors[$field] = ($label ?: ucfirst($field)) . ' no puede exceder ' . $max . ' caracteres';
            return false;
        }
        return true;
    }
    
    public static function unique($table, $field, $value = null) {
        if ($value === null) {
            $value = $_POST[$field] ?? null;
        }
        
        if (empty($value)) return true;
        
        $stmt = db()->prepare("SELECT COUNT(*) as count FROM $table WHERE $field = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            self::$errors[$field] = ucfirst($field) . ' ya existe';
            return false;
        }
        return true;
    }
    
    public static function phone($field, $label = 'Teléfono') {
        if (!isset($_POST[$field])) return true;
        
        $value = $_POST[$field];
        $phone = preg_replace('/[^0-9]/', '', $value);
        
        if (strlen($phone) < 10) {
            self::$errors[$field] = $label . ' debe tener al menos 10 dígitos';
            return false;
        }
        return true;
    }
    
    public static function document($field, $label = 'Documento') {
        if (!isset($_POST[$field])) return true;
        
        $value = $_POST[$field];
        
        if (empty($value)) return true;
        
        // Solo verifica que tenga dígitos
        if (!preg_match('/^[0-9]+$/', $value)) {
            self::$errors[$field] = $label . ' debe contener solo números';
            return false;
        }
        
        if (strlen($value) < 7) {
            self::$errors[$field] = $label . ' debe tener al menos 7 dígitos';
            return false;
        }
        
        return true;
    }
    
    public static function price($field, $label = 'Precio') {
        if (!isset($_POST[$field])) return true;
        
        $value = $_POST[$field];
        
        if (!is_numeric($value) || $value < 0) {
            self::$errors[$field] = $label . ' debe ser un número positivo';
            return false;
        }
        return true;
    }
    
    public static function quantity($field, $label = 'Cantidad') {
        if (!isset($_POST[$field])) return true;
        
        $value = (int)$_POST[$field];
        
        if ($value <= 0) {
            self::$errors[$field] = $label . ' debe ser mayor a 0';
            return false;
        }
        return true;
    }
}
