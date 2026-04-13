<?php
/**
 * Clase de Utilidades para FerrePro
 * Formateo de moneda, fechas, números, etc.
 */

class Format {
    
    /**
     * Formatear moneda en Guaraní (Gs.) sin decimales
     * 
     * @param float $amount Monto a formatear
     * @return string Moneda formateada (ej: "Gs. 150,000")
     */
    public static function money($amount) {
        $amount = floatval($amount);
        return 'Gs. ' . number_format($amount, 0, ',', '.');
    }
    
    /**
     * Formatear moneda solo el valor numérico
     * 
     * @param float $amount Monto a formatear
     * @return string Solo números formateados (ej: "150,000")
     */
    public static function moneyValue($amount) {
        $amount = floatval($amount);
        return number_format($amount, 0, ',', '.');
    }
    
    /**
     * Parsear moneda desde string a número
     * 
     * @param string $moneyString String con formato de moneda
     * @return float Número limpio
     */
    public static function parseMoneyValue($moneyString) {
        // Remover "Gs. " si existe
        $cleaned = str_replace('Gs. ', '', $moneyString);
        // Remover puntos de separador de miles
        $cleaned = str_replace('.', '', $cleaned);
        // Reemplazar coma por punto para conversión a float
        $cleaned = str_replace(',', '.', $cleaned);
        return floatval($cleaned);
    }
    
    /**
     * Formatear fecha en formato dd/mm/yyyy
     * 
     * @param string|null $date Fecha en formato Y-m-d o DATETIME
     * @return string Fecha formateada (ej: "25/12/2024")
     */
    public static function date($date) {
        if (empty($date)) {
            return 'N/A';
        }
        try {
            $dateObj = new DateTime($date);
            return $dateObj->format('d/m/Y');
        } catch (Exception $e) {
            return 'Fecha inválida';
        }
    }
    
    /**
     * Formatear fecha y hora
     * 
     * @param string|null $datetime Fecha/hora
     * @return string Fecha y hora formateadas (ej: "25/12/2024 14:30")
     */
    public static function datetime($datetime) {
        if (empty($datetime)) {
            return 'N/A';
        }
        try {
            $dateObj = new DateTime($datetime);
            return $dateObj->format('d/m/Y H:i');
        } catch (Exception $e) {
            return 'Fecha inválida';
        }
    }
    
    /**
     * Formatear solo hora
     * 
     * @param string|null $datetime Fecha/hora
     * @return string Hora formateada (ej: "14:30")
     */
    public static function time($datetime) {
        if (empty($datetime)) {
            return 'N/A';
        }
        try {
            $dateObj = new DateTime($datetime);
            return $dateObj->format('H:i');
        } catch (Exception $e) {
            return 'Hora inválida';
        }
    }
    
    /**
     * Formatear porcentaje
     * 
     * @param float $value Valor del porcentaje
     * @param int $decimals Decimales a mostrar
     * @return string Porcentaje formateado (ej: "15.50%")
     */
    public static function percentage($value, $decimals = 2) {
        return number_format($value, $decimals, ',', '.') . '%';
    }
    
    /**
     * Formatear cantidad/stock
     * 
     * @param int|float $quantity Cantidad
     * @return string Cantidad formateada
     */
    public static function quantity($quantity) {
        return number_format(intval($quantity), 0, ',', '.');
    }
    
    /**
     * Truncar texto a cierta longitud
     * 
     * @param string $text Texto a truncar
     * @param int $length Longitud máxima
     * @param string $suffix Sufijo si se trunca
     * @return string Texto truncado
     */
    public static function truncate($text, $length = 50, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Formatear estado de venta
     * 
     * @param string $status Estado
     * @return string Estado en español
     */
    public static function saleStatus($status) {
        $statusMap = [
            'pagada' => 'Pagada',
            'pendiente' => 'Pendiente',
            'cancelada' => 'Cancelada',
            'devuelta' => 'Devuelta',
            'contado' => 'Contado',
            'credito' => 'Crédito'
        ];
        
        return $statusMap[strtolower($status)] ?? ucfirst($status);
    }
    
    /**
     * Formatear rol de usuario
     * 
     * @param string $role Rol
     * @return string Rol en español
     */
    public static function userRole($role) {
        $roleMap = [
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'vendedor' => 'Vendedor',
            'contador' => 'Contador'
        ];
        
        return $roleMap[strtolower($role)] ?? ucfirst($role);
    }
    
    /**
     * Calcular cambio porcentual
     * 
     * @param float $old Valor antiguo
     * @param float $new Valor nuevo
     * @return float Cambio porcentual
     */
    public static function percentageChange($old, $new) {
        if ($old == 0) return 0;
        return (($new - $old) / $old) * 100;
    }
    
    /**
     * Formatear cambio porcentual con color
     * 
     * @param float $old Valor antiguo
     * @param float $new Valor nuevo
     * @return string HTML con badge de color
     */
    public static function percentageChangeBadge($old, $new) {
        $change = self::percentageChange($old, $new);
        $color = $change >= 0 ? 'success' : 'danger';
        $symbol = $change >= 0 ? '↑' : '↓';
        
        return sprintf(
            '<span class="badge bg-%s">%s %s%%</span>',
            $color,
            $symbol,
            abs(round($change, 2))
        );
    }
}
