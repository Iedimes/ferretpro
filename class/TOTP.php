<?php
/**
 * TOTP (Time-based One-Time Password) Implementation
 * Simple 2FA system using RFC 6238
 */
class TOTP {
    private static $timeStep = 30; // seconds
    private static $digits = 6; // digit length
    private static $algorithm = 'sha1';
    
    /**
     * Generate a random secret for TOTP
     */
    public static function generateSecret($length = 32) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }
    
    /**
     * Generate QR code URL for authenticator apps
     */
    public static function getQRCodeURL($secret, $email, $issuer = 'FerrePro') {
        $label = urlencode($issuer . ':' . $email);
        $params = [
            'secret' => urlencode($secret),
            'issuer' => urlencode($issuer),
            'algorithm' => self::$algorithm,
            'digits' => self::$digits,
            'period' => self::$timeStep
        ];
        
        $query = http_build_query($params);
        return "otpauth://totp/{$label}?{$query}";
    }
    
    /**
     * Verify TOTP code
     */
    public static function verify($secret, $code, $discrepancy = 1) {
        if (strlen($code) !== self::$digits) {
            return false;
        }
        
        // Check current and adjacent time windows
        $time = floor(time() / self::$timeStep);
        
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            if (self::generateCode($secret, $time + $i) === $code) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate TOTP code for a specific time
     */
    private static function generateCode($secret, $time) {
        // Decode base32 secret
        $key = self::base32Decode($secret);
        
        // Create message (time in big-endian)
        $message = pack('N*', 0) . pack('N', $time);
        
        // Generate HMAC
        $hash = hash_hmac(self::$algorithm, $message, $key, true);
        
        // Extract dynamic binary code
        $offset = ord($hash[strlen($hash) - 1]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, self::$digits);
        
        return str_pad($code, self::$digits, '0', STR_PAD_LEFT);
    }
    
    /**
     * Decode base32 string
     */
    private static function base32Decode($input) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        
        $output = '';
        $v = 0;
        $vbits = 0;
        
        for ($i = 0; $i < strlen($input); $i++) {
            $c = strpos($alphabet, $input[$i]);
            if ($c === false) continue;
            
            $v = ($v << 5) | $c;
            $vbits += 5;
            
            if ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr(($v >> $vbits) & 0xff);
            }
        }
        
        return $output;
    }
    
    /**
     * Get current TOTP code (for testing)
     */
    public static function getCurrentCode($secret) {
        $time = floor(time() / self::$timeStep);
        return self::generateCode($secret, $time);
    }
}
