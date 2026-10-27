<?php
declare(strict_types=1);

namespace App;

class Helpers {
    public static function sanitize(?string $value): string {
        if ($value === null) return '';
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function formatDateTR(?string $dateStr): string {
        if (empty($dateStr)) return '';
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return $dateStr;
        return date('d.m.Y', $timestamp);
    }

    public static function slugify(string $text): string {
        $find = ['İ', 'I', 'ı', 'Ş', 'ş', 'Ğ', 'ğ', 'Ü', 'ü', 'Ö', 'ö', 'Ç', 'ç'];
        $replace = ['i', 'i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $text = str_replace($find, $replace, $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    public static function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    public static function csrfInput(): string {
        return self::csrfField();
    }

    public static function verifyCsrf(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string {
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }

    public static function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $path): void {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    public static function auditLog(int $userId, string $action, string $entityType, ?int $entityId = null, array $details = []): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address)
                VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip)
            ");
            $stmt->execute([
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (\Exception $e) {
            // Silence log errors to not break execution
        }
    }

    public static function formatMoney(mixed $val): string {
        if ($val === null || trim((string)$val) === '') return '';
        $valStr = (string)$val;
        $clean = preg_replace('/[^\d]/', '', $valStr);
        if ($clean === '') {
            return self::sanitize($valStr);
        }
        $num = (float)$clean;
        if ($num <= 0) return '';
        return number_format($num, 0, ',', '.') . ' ₺';
    }

    public static function formatMoneyInput(mixed $val): string {
        if ($val === null || trim((string)$val) === '') return '';
        $valStr = (string)$val;
        $clean = preg_replace('/[^\d]/', '', $valStr);
        if ($clean === '') return '';
        $num = (float)$clean;
        if ($num <= 0) return '';
        return number_format($num, 0, ',', '.');
    }

    public static function formatMoneyTR(mixed $val): string {
        return self::formatMoney($val);
    }
}
