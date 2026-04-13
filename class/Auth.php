<?php
class Auth {
    private static $max_failed_attempts = 5;
    private static $lockout_duration = 900; // 15 minutes in seconds
    private static $reset_token_expiry = 3600; // 1 hour in seconds
    
    /**
     * Log login attempt
     */
    public static function logLoginAttempt($userId, $status = 'success', $failedReason = null) {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = Database::getInstance()->prepare(
                "INSERT INTO login_history (user_id, ip_address, user_agent, status, failed_reason) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([$userId, $ipAddress, $userAgent, $status, $failedReason]);
            
            // Update user's last login if successful
            if ($status === 'success') {
                $stmt = Database::getInstance()->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$userId]);
            }
        } catch (Exception $e) {
            // Silently fail logging if there's an issue
        }
    }
    
    /**
     * Record failed login attempt and check for lockout
     */
    public static function recordFailedAttempt($email) {
        try {
            $stmt = Database::getInstance()->prepare("SELECT id, failed_login_attempts, locked_until FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) return false;
            
            $failedAttempts = ($user['failed_login_attempts'] ?? 0) + 1;
            $lockedUntil = null;
            
            if ($failedAttempts >= self::$max_failed_attempts) {
                $lockedUntil = date('Y-m-d H:i:s', time() + self::$lockout_duration);
            }
            
            $stmt = Database::getInstance()->prepare(
                "UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?"
            );
            $stmt->execute([$failedAttempts, $lockedUntil, $user['id']]);
            
            return $failedAttempts;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if user is locked out
     */
    public static function isUserLockedOut($user) {
        if (!isset($user['locked_until']) || $user['locked_until'] === null) {
            return false;
        }
        
        $lockedUntil = strtotime($user['locked_until']);
        if ($lockedUntil > time()) {
            return true;
        }
        
        // Unlock if lockout time has passed
        $stmt = Database::getInstance()->prepare(
            "UPDATE users SET locked_until = NULL, failed_login_attempts = 0 WHERE id = ?"
        );
        $stmt->execute([$user['id']]);
        
        return false;
    }
    
    /**
     * Reset failed login attempts on successful login
     */
    public static function resetFailedAttempts($userId) {
        try {
            $stmt = Database::getInstance()->prepare(
                "UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?"
            );
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Generate password reset token
     */
    public static function generatePasswordResetToken($email) {
        try {
            $stmt = Database::getInstance()->prepare("SELECT id FROM users WHERE email = ? AND active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return null;
            }
            
            // Invalidate previous tokens
            $stmt = Database::getInstance()->prepare(
                "UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0"
            );
            $stmt->execute([$user['id']]);
            
            // Generate new token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + self::$reset_token_expiry);
            
            $stmt = Database::getInstance()->prepare(
                "INSERT INTO password_resets (user_id, token, email, expires_at) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$user['id'], $token, $email, $expiresAt]);
            
            return $token;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Verify password reset token
     */
    public static function verifyPasswordResetToken($token) {
        try {
            $stmt = Database::getInstance()->prepare(
                "SELECT * FROM password_resets 
                 WHERE token = ? AND used = 0 AND expires_at > CURRENT_TIMESTAMP 
                 LIMIT 1"
            );
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Reset password using token
     */
    public static function resetPasswordWithToken($token, $newPassword) {
        try {
            $reset = self::verifyPasswordResetToken($token);
            if (!$reset) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = Database::getInstance()->prepare(
                "UPDATE users SET password = ?, last_password_change = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->execute([$hashedPassword, $reset['user_id']]);
            
            // Mark token as used
            $stmt = Database::getInstance()->prepare(
                "UPDATE password_resets SET used = 1 WHERE id = ?"
            );
            $stmt->execute([$reset['id']]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Change user password (requires current password)
     */
    public static function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $stmt = Database::getInstance()->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = Database::getInstance()->prepare(
                "UPDATE users SET password = ?, last_password_change = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->execute([$hashedPassword, $userId]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get login history for a user
     */
    public static function getLoginHistory($userId, $limit = 20) {
        try {
            $stmt = Database::getInstance()->prepare(
                "SELECT * FROM login_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?"
            );
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get recent login attempts for a user
     */
    public static function getRecentFailedAttempts($email, $minutes = 30) {
        try {
            $timeLimit = date('Y-m-d H:i:s', time() - ($minutes * 60));
            
            $stmt = Database::getInstance()->prepare(
                "SELECT COUNT(*) as count FROM login_history 
                 WHERE user_id = (SELECT id FROM users WHERE email = ?) 
                 AND status = 'failed' 
                 AND created_at > ?"
            );
            $stmt->execute([$email, $timeLimit]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Enable 2FA for a user
     */
    public static function enable2FA($userId) {
        try {
            require_once dirname(__DIR__) . '/class/TOTP.php';
            $secret = TOTP::generateSecret();
            
            $stmt = Database::getInstance()->prepare(
                "UPDATE users SET two_fa_enabled = 1, two_fa_secret = ? WHERE id = ?"
            );
            $stmt->execute([$secret, $userId]);
            
            return $secret;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Disable 2FA for a user
     */
    public static function disable2FA($userId) {
        try {
            $stmt = Database::getInstance()->prepare(
                "UPDATE users SET two_fa_enabled = 0, two_fa_secret = NULL WHERE id = ?"
            );
            $stmt->execute([$userId]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Verify 2FA code
     */
    public static function verify2FACode($userId, $code) {
        try {
            require_once dirname(__DIR__) . '/class/TOTP.php';
            
            $stmt = Database::getInstance()->prepare(
                "SELECT two_fa_secret FROM users WHERE id = ? AND two_fa_enabled = 1"
            );
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            return TOTP::verify($user['two_fa_secret'], $code);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if 2FA is enabled for a user
     */
    public static function is2FAEnabled($userId) {
        try {
            $stmt = Database::getInstance()->prepare(
                "SELECT two_fa_enabled FROM users WHERE id = ?"
            );
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($user['two_fa_enabled'] ?? 0) === 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Generate session recovery token (for accidental logout)
     */
    public static function generateSessionRecoveryToken($userId) {
        try {
            $recoveryToken = bin2hex(random_bytes(32));
            $sessionToken = bin2hex(random_bytes(16));
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            $stmt = Database::getInstance()->prepare(
                "INSERT INTO session_recovery (user_id, recovery_token, session_token, ip_address, expires_at) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $recoveryToken, $sessionToken, $ipAddress, $expiresAt]);
            
            return $recoveryToken;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Recover session using recovery token
     */
    public static function recoverSession($recoveryToken) {
        try {
            $stmt = Database::getInstance()->prepare(
                "SELECT * FROM session_recovery 
                 WHERE recovery_token = ? AND used = 0 AND expires_at > CURRENT_TIMESTAMP 
                 LIMIT 1"
            );
            $stmt->execute([$recoveryToken]);
            $recovery = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$recovery) {
                return null;
            }
            
            // Check if IP matches (basic security check)
            $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
            if ($recovery['ip_address'] !== $currentIP) {
                // Different IP - still allow but log it
            }
            
            // Mark as used
            $stmt = Database::getInstance()->prepare(
                "UPDATE session_recovery SET used = 1 WHERE id = ?"
            );
            $stmt->execute([$recovery['id']]);
            
            return $recovery;
        } catch (Exception $e) {
            return null;
        }
    }
}
