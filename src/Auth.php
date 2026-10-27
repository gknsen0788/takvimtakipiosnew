<?php
declare(strict_types=1);

namespace App;

use PDO;

class Auth {
    public static function validatePasswordComplexity(string $password): bool {
        return (strlen($password) >= 6);
    }

    public static function login(string $emailOrPhone, string $password, bool $rememberMe = false): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $rateKey = 'login_ip_' . $ip;

        if (RateLimiter::isTooManyAttempts($rateKey, 5, 900)) {
            $remaining = RateLimiter::getRemainingSeconds($rateKey, 900);
            $mins = ceil($remaining / 60);
            Helpers::setFlash('error', "Çok fazla hatalı giriş denemesi yaptınız. Lütfen {$mins} dakika sonra tekrar deneyin.");
            return false;
        }

        $db = Database::getConnection();
        $input = trim($emailOrPhone);
        $email = strtolower($input);

        // Normalize phone digits
        $digitsOnly = preg_replace('/[^0-9]/', '', $input);
        $cleanPhone10 = '';
        if (strlen($digitsOnly) >= 10) {
            $cleanPhone10 = substr($digitsOnly, -10);
        }

        $stmt = $db->prepare("
            SELECT * FROM users 
            WHERE email = :email 
               OR phone = :input_raw
               OR (length(:phone_10) = 10 AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE :phone_like)
            LIMIT 1
        ");
        $stmt->execute([
            'email' => $email,
            'input_raw' => $input,
            'phone_10' => $cleanPhone10,
            'phone_like' => $cleanPhone10 ? '%' . $cleanPhone10 : 'NOMATCH'
        ]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'passive') {
                Helpers::setFlash('error', 'Hesabınız pasife alınmıştır. Lütfen yönetici ile iletişime geçin.');
                return false;
            }

            // License expiration check (for non-admin users)
            if ($user['role'] !== 'admin' && !empty($user['license_expires_at'])) {
                $today = date('Y-m-d');
                if ($user['license_expires_at'] < $today) {
                    $expDateFormatted = date('d.m.Y', strtotime($user['license_expires_at']));
                    Helpers::setFlash('error', "Lisans süreniz dolmuştur (Son Tarih: {$expDateFormatted}). Lütfen sistem yöneticinizle iletişime geçin.");
                    return false;
                }
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }

            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'subscription_plan' => $user['subscription_plan'],
                'max_villas' => (int)$user['max_villas'],
                'license_expires_at' => $user['license_expires_at'] ?? null
            ];

            if ($rememberMe) {
                self::createRememberToken((int)$user['id']);
            } else {
                self::clearRememberToken();
            }

            RateLimiter::clear($rateKey);
            Helpers::auditLog((int)$user['id'], 'USER_LOGIN', 'users', (int)$user['id']);
            return true;
        }

        RateLimiter::hit($rateKey, 900);
        Helpers::setFlash('error', 'E-posta adresi, telefon numarası veya şifre hatalı.');
        return false;
    }

    public static function createRememberToken(int $userId): void {
        $rawToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + (365 * 86400)); // 1 year

        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $stmt->execute([
            'user_id' => $userId,
            'token' => $hashedToken,
            'expires_at' => $expiresAt
        ]);

        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        setcookie('remember_token', $rawToken, [
            'expires' => time() + (365 * 86400),
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public static function clearRememberToken(): void {
        if (!empty($_COOKIE['remember_token'])) {
            $rawToken = $_COOKIE['remember_token'];
            $hashedToken = hash('sha256', $rawToken);
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM user_tokens WHERE token = :token");
            $stmt->execute(['token' => $hashedToken]);

            $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            unset($_COOKIE['remember_token']);
        }
    }

    public static function attemptAutoLogin(): bool {
        if (!empty($_SESSION['user']['id'])) {
            return true;
        }

        if (empty($_COOKIE['remember_token'])) {
            return false;
        }

        $rawToken = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $rawToken);

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT user_id, expires_at FROM user_tokens WHERE token = :token LIMIT 1");
        $stmt->execute(['token' => $hashedToken]);
        $tokenRow = $stmt->fetch();

        if (!$tokenRow) {
            self::clearRememberToken();
            return false;
        }

        if (strtotime($tokenRow['expires_at']) < time()) {
            self::clearRememberToken();
            return false;
        }

        $userStmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $userStmt->execute(['id' => $tokenRow['user_id']]);
        $user = $userStmt->fetch();

        if (!$user || $user['status'] === 'passive') {
            self::clearRememberToken();
            return false;
        }

        if ($user['role'] !== 'admin' && !empty($user['license_expires_at'])) {
            if ($user['license_expires_at'] < date('Y-m-d')) {
                self::clearRememberToken();
                return false;
            }
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'subscription_plan' => $user['subscription_plan'],
            'max_villas' => (int)$user['max_villas'],
            'license_expires_at' => $user['license_expires_at'] ?? null
        ];

        return true;
    }

    public static function logout(): void {
        if (self::check()) {
            Helpers::auditLog(self::user()['id'], 'USER_LOGOUT', 'users', self::user()['id']);
        }
        self::clearRememberToken();
        unset($_SESSION['user']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            session_destroy();
        }
    }

    public static function check(): bool {
        if (empty($_SESSION['user']['id'])) {
            self::attemptAutoLogin();
        }
        return !empty($_SESSION['user']['id']);
    }

    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function isAdmin(): bool {
        return self::check() && ($_SESSION['user']['role'] === 'admin');
    }

    public static function requireAuth(): void {
        if (!self::check()) {
            Helpers::setFlash('error', 'Lütfen önce giriş yapın.');
            Helpers::redirect('/login');
        }

        // Live license and status check during active session
        $u = self::user();
        if ($u && $u['role'] !== 'admin' && !empty($u['id'])) {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT license_expires_at, status FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $u['id']]);
            $dbUser = $stmt->fetch();
            if ($dbUser) {
                if ($dbUser['status'] === 'passive') {
                    self::logout();
                    Helpers::setFlash('error', 'Hesabınız pasife alınmıştır.');
                    Helpers::redirect('/login');
                }
                if (!empty($dbUser['license_expires_at']) && $dbUser['license_expires_at'] < date('Y-m-d')) {
                    $expDateFormatted = date('d.m.Y', strtotime($dbUser['license_expires_at']));
                    self::logout();
                    Helpers::setFlash('error', "Lisans süreniz dolmuştur (Son Tarih: {$expDateFormatted}). Lütfen sistem yöneticinizle iletişime geçin.");
                    Helpers::redirect('/login');
                }
            }
        }
    }

    public static function requireAdmin(): void {
        self::requireAuth();
        if (!self::isAdmin()) {
            Helpers::setFlash('error', 'Bu sayfaya erişim yetkiniz yok.');
            Helpers::redirect('/dashboard');
        }
    }

    public static function createPasswordResetToken(string $emailOrPhone): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $rateKey = 'reset_ip_' . $ip;

        if (RateLimiter::isTooManyAttempts($rateKey, 3, 900)) {
            return ['success' => false, 'message' => 'Çok fazla şifre sıfırlama isteği gönderdiniz. Lütfen 15 dakika sonra tekrar deneyin.'];
        }

        $db = Database::getConnection();
        $input = trim($emailOrPhone);
        $email = strtolower($input);

        $digitsOnly = preg_replace('/[^0-9]/', '', $input);
        $cleanPhone10 = strlen($digitsOnly) >= 10 ? substr($digitsOnly, -10) : '';

        $stmt = $db->prepare("
            SELECT * FROM users 
            WHERE email = :email 
               OR phone = :input_raw
               OR (length(:phone_10) = 10 AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE :phone_like)
            LIMIT 1
        ");
        $stmt->execute([
            'email' => $email,
            'input_raw' => $input,
            'phone_10' => $cleanPhone10,
            'phone_like' => $cleanPhone10 ? '%' . $cleanPhone10 : 'NOMATCH'
        ]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Bu e-posta adresi veya cep telefonuna ait kayıtlı kullanıcı bulunamadı.'];
        }

        RateLimiter::hit($rateKey, 900);

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $delStmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $delStmt->execute(['email' => $user['email']]);

        $insStmt = $db->prepare("
            INSERT INTO password_resets (email, token, expires_at)
            VALUES (:email, :token, :expires_at)
        ");
        $insStmt->execute([
            'email' => $user['email'],
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        $resetUrl = APP_URL . '/reset-password?token=' . $token;

        Helpers::auditLog((int)$user['id'], 'PASSWORD_RESET_REQUESTED', 'users', (int)$user['id']);

        return [
            'success' => true,
            'message' => 'Şifre sıfırlama bağlantısı oluşturuldu.',
            'token' => $token,
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'reset_url' => $resetUrl
        ];
    }

    public static function validatePasswordResetToken(string $token): ?array {
        if (empty($token)) return null;
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at >= CURRENT_TIMESTAMP LIMIT 1");
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function resetPasswordWithToken(string $token, string $newPassword): array {
        $resetRecord = self::validatePasswordResetToken($token);
        if (!$resetRecord) {
            return ['success' => false, 'message' => 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş. Lütfen tekrar istek gönderin.'];
        }

        if (!self::validatePasswordComplexity($newPassword)) {
            return ['success' => false, 'message' => 'Şifreniz en az 6 karakter uzunluğunda olmalıdır.'];
        }

        $db = Database::getConnection();
        $passHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $db->prepare("UPDATE users SET password_hash = :pass, updated_at = CURRENT_TIMESTAMP WHERE email = :email");
        $success = $stmt->execute(['pass' => $passHash, 'email' => $resetRecord['email']]);

        if ($success) {
            $delStmt = $db->prepare("DELETE FROM password_resets WHERE token = :token");
            $delStmt->execute(['token' => $token]);

            return ['success' => true, 'message' => 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.'];
        }

        return ['success' => false, 'message' => 'Şifre güncellenirken bir hata oluştu.'];
    }
}
