<?php
declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

class User {
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM villas v WHERE v.user_id = u.id) as villa_count
            FROM users u 
            ORDER BY u.created_at DESC
        ");
        $users = $stmt->fetchAll();

        $today = date('Y-m-d');
        foreach ($users as &$u) {
            if ($u['role'] === 'admin') {
                $u['remaining_days'] = 'Sınırsız';
                $u['license_status'] = 'unlimited';
            } elseif (empty($u['license_expires_at'])) {
                $u['remaining_days'] = 0;
                $u['license_status'] = 'expired';
            } else {
                $exp = $u['license_expires_at'];
                $diff = (int)floor((strtotime($exp) - strtotime($today)) / 86400);
                $u['remaining_days'] = $diff;
                if ($diff < 0) {
                    $u['license_status'] = 'expired';
                } elseif ($diff <= 5) {
                    $u['license_status'] = 'warning';
                } else {
                    $u['license_status'] = 'active';
                }
            }
        }
        return $users;
    }

    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function extendLicense(int $userId, int $days): bool {
        $user = self::findById($userId);
        if (!$user) return false;

        $today = date('Y-m-d');
        $currentExp = $user['license_expires_at'] ?? null;

        // If license is expired or not set, calculate from TODAY
        if (empty($currentExp) || $currentExp < $today) {
            $newExp = date('Y-m-d', strtotime("+{$days} days"));
        } else {
            // Otherwise, extend from current expiration date
            $newExp = date('Y-m-d', strtotime("{$currentExp} +{$days} days"));
        }

        $remDays = (int)floor((strtotime($newExp) - strtotime($today)) / 86400);
        $plan = ($remDays >= 11) ? 'pro' : 'basic';

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET license_expires_at = :exp, subscription_plan = :plan, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['exp' => $newExp, 'plan' => $plan, 'id' => $userId]);
    }

    public static function setLicenseDate(int $userId, string $date): bool {
        $today = date('Y-m-d');
        $remDays = (int)floor((strtotime($date) - strtotime($today)) / 86400);
        $plan = ($remDays >= 11) ? 'pro' : 'basic';

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET license_expires_at = :exp, subscription_plan = :plan, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['exp' => $date, 'plan' => $plan, 'id' => $userId]);
    }

    public static function create(array $data): array {
        $db = Database::getConnection();

        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'owner';
        $licenseDays = (int)($data['license_days'] ?? 7);
        $customExpDate = trim($data['license_expires_at'] ?? '');

        if (empty($fullName) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Lütfen Ad Soyad, E-posta ve Şifre alanlarını doldurun.'];
        }

        if (!\App\Auth::validatePasswordComplexity($password)) {
            return ['success' => false, 'message' => 'Şifreniz en az 6 karakter uzunluğunda olmalıdır.'];
        }

        // Check duplicate email
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Bu e-posta adresi zaten sisteme kayıtlı.'];
        }

        // Check duplicate phone
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
        $phone10 = strlen($phoneDigits) >= 10 ? substr($phoneDigits, -10) : '';
        if (!empty($phoneDigits)) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM users 
                WHERE phone = :phone_raw
                   OR (length(:phone_10) = 10 AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE :phone_like)
            ");
            $stmt->execute([
                'phone_raw' => $phone,
                'phone_10' => $phone10,
                'phone_like' => $phone10 ? '%' . $phone10 : 'NOMATCH'
            ]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Bu cep telefonu numarası zaten sisteme kayıtlı.'];
            }
        }

        if (!empty($customExpDate)) {
            $licenseExp = $customExpDate;
        } else {
            $licenseExp = date('Y-m-d', strtotime("+{$licenseDays} days"));
        }

        $today = date('Y-m-d');
        $remDays = (int)floor((strtotime($licenseExp) - strtotime($today)) / 86400);
        $plan = ($remDays >= 11) ? 'pro' : 'basic';

        $passHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("
            INSERT INTO users (full_name, email, phone, password_hash, role, status, subscription_plan, max_villas, license_expires_at)
            VALUES (:name, :email, :phone, :pass, :role, 'active', :plan, 999, :license_exp)
        ");

        $success = $stmt->execute([
            'name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'pass' => $passHash,
            'role' => $role,
            'plan' => $plan,
            'license_exp' => $licenseExp
        ]);

        if ($success) {
            return ['success' => true, 'message' => "Yeni kullanıcı ({$fullName}) başarıyla oluşturuldu."];
        }

        return ['success' => false, 'message' => 'Kullanıcı oluşturulurken bir hata oluştu.'];
    }

    public static function toggleStatus(int $userId): bool {
        $user = self::findById($userId);
        if (!$user || $user['role'] === 'admin') return false;

        $newStatus = ($user['status'] === 'active') ? 'passive' : 'active';
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['status' => $newStatus, 'id' => $userId]);
    }

    public static function updateUser(int $id, array $data): array {
        $user = self::findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }

        $db = Database::getConnection();
        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? $user['role'];
        $licenseExp = trim($data['license_expires_at'] ?? '');

        if (empty($fullName) || empty($email)) {
            return ['success' => false, 'message' => 'Ad Soyad ve E-posta zorunludur.'];
        }

        // Check duplicate email for other users
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $email, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.'];
        }

        // Check duplicate phone for other users
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
        $phone10 = strlen($phoneDigits) >= 10 ? substr($phoneDigits, -10) : '';
        if (!empty($phoneDigits)) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM users 
                WHERE id != :id AND (
                    phone = :phone_raw
                    OR (length(:phone_10) = 10 AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE :phone_like)
                )
            ");
            $stmt->execute([
                'id' => $id,
                'phone_raw' => $phone,
                'phone_10' => $phone10,
                'phone_like' => $phone10 ? '%' . $phone10 : 'NOMATCH'
            ]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Bu cep telefonu numarası başka bir kullanıcı tarafından kullanılıyor.'];
            }
        }

        $sql = "UPDATE users SET full_name = :name, email = :email, phone = :phone, role = :role, license_expires_at = :license_exp, updated_at = CURRENT_TIMESTAMP";
        $params = [
            'id' => $id,
            'name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'license_exp' => !empty($licenseExp) ? $licenseExp : $user['license_expires_at']
        ];

        if (!empty($password)) {
            if (!\App\Auth::validatePasswordComplexity($password)) {
                return ['success' => false, 'message' => 'Şifreniz en az 6 karakter uzunluğunda olmalıdır.'];
            }
            $sql .= ", password_hash = :pass";
            $params['pass'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute($params);

        if ($success) {
            return ['success' => true, 'message' => 'Kullanıcı bilgileri güncellendi.'];
        }

        return ['success' => false, 'message' => 'Güncelleme sırasında bir hata oluştu.'];
    }

    public static function deleteUser(int $id, int $currentAdminId): array {
        if ($id === $currentAdminId) {
            return ['success' => false, 'message' => 'Kendi hesabınızı silemezsiniz.'];
        }

        $user = self::findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $success = $stmt->execute(['id' => $id]);

        if ($success) {
            return ['success' => true, 'message' => "Kullanıcı ({$user['full_name']}) başarıyla silindi."];
        }

        return ['success' => false, 'message' => 'Kullanıcı silinirken bir hata oluştu.'];
    }

    public static function updateProfile(int $id, array $data): array {
        $user = self::findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }

        $db = Database::getConnection();
        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? '');

        if (empty($fullName) || empty($email)) {
            return ['success' => false, 'message' => 'Ad Soyad ve E-posta zorunludur.'];
        }

        // Check duplicate email
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $email, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.'];
        }

        // Check duplicate phone
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
        $phone10 = strlen($phoneDigits) >= 10 ? substr($phoneDigits, -10) : '';
        if (!empty($phoneDigits)) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM users 
                WHERE id != :id AND (
                    phone = :phone_raw
                    OR (length(:phone_10) = 10 AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE :phone_like)
                )
            ");
            $stmt->execute([
                'id' => $id,
                'phone_raw' => $phone,
                'phone_10' => $phone10,
                'phone_like' => $phone10 ? '%' . $phone10 : 'NOMATCH'
            ]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Bu cep telefonu numarası başka bir kullanıcı tarafından kullanılıyor.'];
            }
        }

        $stmt = $db->prepare("UPDATE users SET full_name = :name, email = :email, phone = :phone, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $success = $stmt->execute([
            'id' => $id,
            'name' => $fullName,
            'email' => $email,
            'phone' => $phone
        ]);

        if ($success) {
            if (isset($_SESSION['user']) && $_SESSION['user']['id'] === $id) {
                $_SESSION['user']['full_name'] = $fullName;
                $_SESSION['user']['email'] = $email;
            }
            return ['success' => true, 'message' => 'Profil bilgileriniz başarıyla güncellendi.'];
        }

        return ['success' => false, 'message' => 'Güncelleme sırasında bir hata oluştu.'];
    }

    public static function updatePassword(int $id, string $currentPassword, string $newPassword, string $confirmPassword): array {
        $user = self::findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return ['success' => false, 'message' => 'Lütfen tüm şifre alanlarını doldurunuz.'];
        }

        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Mevcut şifreniz hatalı.'];
        }

        if (!\App\Auth::validatePasswordComplexity($newPassword)) {
            return ['success' => false, 'message' => 'Yeni şifreniz en az 6 karakter uzunluğunda olmalıdır.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Yeni şifre ve şifre tekrarı birbiriyle uyuşmuyor.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $success = $stmt->execute(['hash' => $newHash, 'id' => $id]);

        if ($success) {
            return ['success' => true, 'message' => 'Şifreniz başarıyla değiştirildi.'];
        }

        return ['success' => false, 'message' => 'Şifre değiştirilirken bir hata oluştu.'];
    }

    public static function deleteOwnAccount(int $id): array {
        $user = self::findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }

        if ($user['role'] === 'admin') {
            return ['success' => false, 'message' => 'Yönetici hesapları bu alandan silinemez.'];
        }

        $db = Database::getConnection();

        // Get user's villas and clean up child records
        $villasStmt = $db->prepare("SELECT id FROM villas WHERE user_id = :user_id");
        $villasStmt->execute(['user_id' => $id]);
        $villaIds = $villasStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($villaIds)) {
            $inClause = implode(',', array_map('intval', $villaIds));
            $db->exec("DELETE FROM villa_images WHERE villa_id IN ($inClause)");
            $db->exec("DELETE FROM reservations WHERE villa_id IN ($inClause)");
            $db->exec("DELETE FROM price_periods WHERE villa_id IN ($inClause)");
            $db->exec("DELETE FROM external_calendars WHERE villa_id IN ($inClause)");
            $db->exec("DELETE FROM villas WHERE user_id = {$id}");
        }

        // Delete user record
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $success = $stmt->execute(['id' => $id]);

        if ($success) {
            \App\Auth::logout();
            return ['success' => true, 'message' => 'Hesabınız ve hesabınıza ait tüm veriler kalıcı olarak silindi.'];
        }

        return ['success' => false, 'message' => 'Hesabınız silinirken bir hata oluştu.'];
    }
}
