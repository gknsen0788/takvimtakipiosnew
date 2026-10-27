<?php
declare(strict_types=1);

namespace App\Models;

use App\Database;
use App\Helpers;
use PDO;

class Villa {
    public static function create(array $data, int $userId): ?int {
        $db = Database::getConnection();

        // 1. Subscription Check (Disabled temporarily)
        /*
        $userStmt = $db->prepare("SELECT subscription_plan, max_villas FROM users WHERE id = :id");
        $userStmt->execute(['id' => $userId]);
        $user = $userStmt->fetch();
        
        $countStmt = $db->prepare("SELECT COUNT(*) FROM villas WHERE user_id = :id");
        $countStmt->execute(['id' => $userId]);
        $currentCount = (int)$countStmt->fetchColumn();

        if ($currentCount >= (int)$user['max_villas']) {
            Helpers::setFlash('error', "Mevcut abonelik planınız ({$user['subscription_plan']}) maksimum {$user['max_villas']} villa eklemenize izin veriyor. Lütfen planınızı yükseltin.");
            return null;
        }
        */

        // 2. Generate Unique Slug
        $baseSlug = Helpers::slugify($data['name']);
        $slug = self::generateUniqueSlug($baseSlug);

        // 3. Generate Unique iCal Export Token
        $icalToken = Helpers::generateToken(32);

        $stmt = $db->prepare("
            INSERT INTO villas (
                user_id, name, slug, short_description, detailed_description,
                city, district, address, google_maps_url, deposit_amount, pool_heating_fee, cleaning_days, cleaning_fee, google_drive_link, max_guests,
                bedrooms, bathrooms, features, min_stay_nights, checkin_time, checkout_time,
                property_type, status, ical_export_token
            ) VALUES (
                :user_id, :name, :slug, :short_desc, :detailed_desc,
                :city, :district, :address, :google_maps, :deposit_amount, :pool_heating_fee, :cleaning_days, :cleaning_fee, :google_drive_link, :max_guests,
                :bedrooms, :bathrooms, :features, :min_stay, :checkin, :checkout,
                :property_type, 'active', :ical_token
            )
        ");

        $success = $stmt->execute([
            'user_id' => $userId,
            'name' => $data['name'],
            'slug' => $slug,
            'short_desc' => $data['short_description'] ?? '',
            'detailed_desc' => $data['detailed_description'] ?? '',
            'city' => $data['city'],
            'district' => $data['district'],
            'address' => $data['address'] ?? '',
            'google_maps' => $data['google_maps_url'] ?? '',
            'deposit_amount' => $data['deposit_amount'] ?? '',
            'pool_heating_fee' => $data['pool_heating_fee'] ?? '',
            'cleaning_days' => (int)($data['cleaning_days'] ?? 0),
            'cleaning_fee' => $data['cleaning_fee'] ?? '',
            'google_drive_link' => $data['google_drive_link'] ?? '',
            'max_guests' => (int)($data['max_guests'] ?? 4),
            'bedrooms' => (int)($data['bedrooms'] ?? 2),
            'bathrooms' => (int)($data['bathrooms'] ?? 1),
            'features' => json_encode($data['features'] ?? [], JSON_UNESCAPED_UNICODE),
            'min_stay' => (int)($data['min_stay_nights'] ?? 1),
            'checkin' => $data['checkin_time'] ?? '',
            'checkout' => $data['checkout_time'] ?? '',
            'property_type' => $data['property_type'] ?? 'Villa',
            'ical_token' => $icalToken
        ]);

        if ($success) {
            $villaId = (int)$db->lastInsertId();
            Helpers::auditLog($userId, 'VILLA_CREATED', 'villas', $villaId, ['name' => $data['name'], 'slug' => $slug]);
            return $villaId;
        }

        return null;
    }

    public static function update(int $id, array $data, int $userId, bool $isAdmin = false): bool {
        $db = Database::getConnection();

        // Security / Data Isolation Check
        $villa = self::find($id, $userId, $isAdmin);
        if (!$villa) return false;

        $newSlug = $villa['slug'];
        if (empty($villa['slug']) || str_starts_with($villa['slug'], 'draft-') || trim($villa['name']) !== trim($data['name'])) {
            $baseSlug = Helpers::slugify($data['name']);
            $newSlug = self::generateUniqueSlugForUpdate($baseSlug, $id);
        }

        $stmt = $db->prepare("
            UPDATE villas SET
                name = :name,
                slug = :slug,
                short_description = :short_desc,
                detailed_description = :detailed_desc,
                city = :city,
                district = :district,
                address = :address,
                google_maps_url = :google_maps,
                deposit_amount = :deposit_amount,
                pool_heating_fee = :pool_heating_fee,
                cleaning_days = :cleaning_days,
                cleaning_fee = :cleaning_fee,
                google_drive_link = :google_drive_link,
                max_guests = :max_guests,
                bedrooms = :bedrooms,
                bathrooms = :bathrooms,
                features = :features,
                min_stay_nights = :min_stay,
                checkin_time = :checkin,
                checkout_time = :checkout,
                property_type = :property_type,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $success = $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $newSlug,
            'short_desc' => $data['short_description'] ?? '',
            'detailed_desc' => $data['detailed_description'] ?? '',
            'city' => $data['city'],
            'district' => $data['district'],
            'address' => $data['address'] ?? '',
            'google_maps' => $data['google_maps_url'] ?? '',
            'deposit_amount' => $data['deposit_amount'] ?? '',
            'pool_heating_fee' => $data['pool_heating_fee'] ?? '',
            'cleaning_days' => (int)($data['cleaning_days'] ?? 0),
            'cleaning_fee' => $data['cleaning_fee'] ?? '',
            'google_drive_link' => $data['google_drive_link'] ?? '',
            'max_guests' => (int)($data['max_guests'] ?? 4),
            'bedrooms' => (int)($data['bedrooms'] ?? 2),
            'bathrooms' => (int)($data['bathrooms'] ?? 1),
            'features' => json_encode($data['features'] ?? [], JSON_UNESCAPED_UNICODE),
            'min_stay' => (int)($data['min_stay_nights'] ?? 1),
            'checkin' => $data['checkin_time'] ?? '16:00',
            'checkout' => $data['checkout_time'] ?? '10:00',
            'property_type' => $data['property_type'] ?? 'Villa',
            'status' => $data['status'] ?? 'active'
        ]);

        if ($success) {
            Helpers::auditLog($userId, 'VILLA_UPDATED', 'villas', $id);
        }

        return $success;
    }

    public static function delete(int $id, int $userId, bool $isAdmin = false): bool {
        $db = Database::getConnection();
        $villa = self::find($id, $userId, $isAdmin);
        if (!$villa) return false;

        $stmt = $db->prepare("DELETE FROM villas WHERE id = :id");
        $success = $stmt->execute(['id' => $id]);

        if ($success) {
            Helpers::auditLog($userId, 'VILLA_DELETED', 'villas', $id, ['name' => $villa['name']]);
        }

        return $success;
    }

    public static function find(int $id, int $userId, bool $isAdmin = false): ?array {
        $db = Database::getConnection();
        if ($isAdmin) {
            $stmt = $db->prepare("SELECT v.*, u.full_name as owner_name FROM villas v JOIN users u ON v.user_id = u.id WHERE v.id = :id");
            $stmt->execute(['id' => $id]);
        } else {
            $stmt = $db->prepare("SELECT * FROM villas WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
        }
        $villa = $stmt->fetch();
        if ($villa) {
            $villa['features_arr'] = !empty($villa['features']) ? json_decode($villa['features'], true) : [];
            $villa['images'] = self::getImages((int)$villa['id']);
        }
        return $villa ?: null;
    }

    public static function findBySlug(string $slug): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM villas WHERE slug = :slug AND status = 'active' LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $villa = $stmt->fetch();
        if ($villa) {
            $villa['features_arr'] = !empty($villa['features']) ? json_decode($villa['features'], true) : [];
            $villa['images'] = self::getImages((int)$villa['id']);
        }
        return $villa ?: null;
    }

    public static function findByIcalToken(string $token): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM villas WHERE ical_export_token = :token AND status = 'active' LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public static function allByUser(int $userId, bool $activeOnly = false): array {
        $db = Database::getConnection();
        $sql = "
            SELECT v.*, 
            (SELECT image_url FROM villa_images WHERE villa_id = v.id ORDER BY is_cover DESC, id ASC LIMIT 1) as cover_image
            FROM villas v 
            WHERE v.user_id = :user_id " . ($activeOnly ? "AND v.status = 'active' " : "") . "
            ORDER BY v.created_at DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function allForAdmin(bool $activeOnly = false): array {
        $db = Database::getConnection();
        $sql = "
            SELECT v.*, u.full_name as owner_name, u.email as owner_email,
            (SELECT image_url FROM villa_images WHERE villa_id = v.id ORDER BY is_cover DESC, id ASC LIMIT 1) as cover_image
            FROM villas v
            JOIN users u ON v.user_id = u.id
            " . ($activeOnly ? "WHERE v.status = 'active' " : "") . "
            ORDER BY v.created_at DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private static function generateUniqueSlug(string $baseSlug): string {
        $slug = Helpers::slugify($baseSlug);
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM villas WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }

        $counter = 1;
        while (true) {
            $testSlug = $slug . '-' . $counter;
            $stmt = $db->prepare("SELECT COUNT(*) FROM villas WHERE slug = :slug");
            $stmt->execute(['slug' => $testSlug]);
            if ($stmt->fetchColumn() == 0) {
                return $testSlug;
            }
            $counter++;
        }
    }

    private static function generateUniqueSlugForUpdate(string $baseSlug, int $currentVillaId): string {
        $slug = Helpers::slugify($baseSlug);
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM villas WHERE slug = :slug AND id != :id");
        $stmt->execute(['slug' => $slug, 'id' => $currentVillaId]);
        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }

        $counter = 1;
        while (true) {
            $testSlug = $slug . '-' . $counter;
            $stmt = $db->prepare("SELECT COUNT(*) FROM villas WHERE slug = :slug AND id != :id");
            $stmt->execute(['slug' => $testSlug, 'id' => $currentVillaId]);
            if ($stmt->fetchColumn() == 0) {
                return $testSlug;
            }
            $counter++;
        }
    }

    public static function addImage(int $villaId, string $imageUrl, bool $isCover = false): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO villa_images (villa_id, image_url, is_cover) VALUES (:villa_id, :url, :is_cover)");
        return $stmt->execute([
            'villa_id' => $villaId,
            'url' => $imageUrl,
            'is_cover' => $isCover ? 1 : 0
        ]);
    }

    public static function getImages(int $villaId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM villa_images WHERE villa_id = :villa_id ORDER BY is_cover DESC, id ASC");
        $stmt->execute(['villa_id' => $villaId]);
        return $stmt->fetchAll();
    }

    public static function deleteImage(int $imageId, int $villaId, int $userId, bool $isAdmin = false): bool {
        $db = Database::getConnection();
        $villa = self::find($villaId, $userId, $isAdmin);
        if (!$villa) return false;

        $stmt = $db->prepare("SELECT * FROM villa_images WHERE id = :id AND villa_id = :villa_id");
        $stmt->execute(['id' => $imageId, 'villa_id' => $villaId]);
        $img = $stmt->fetch();

        if ($img) {
            if (!empty($img['image_url'])) {
                $parsed = parse_url($img['image_url'], PHP_URL_PATH);
                if ($parsed) {
                    $filename = basename($parsed);
                    $filePath = UPLOAD_DIR . $filename;
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }

            $delStmt = $db->prepare("DELETE FROM villa_images WHERE id = :id AND villa_id = :villa_id");
            $success = $delStmt->execute(['id' => $imageId, 'villa_id' => $villaId]);

            if ($img['is_cover']) {
                $db->exec("UPDATE villa_images SET is_cover = 1 WHERE id = (SELECT id FROM villa_images WHERE villa_id = {$villaId} ORDER BY id ASC LIMIT 1)");
            }

            return $success;
        }

        return false;
    }

    public static function setCoverImage(int $imageId, int $villaId, int $userId, bool $isAdmin = false): bool {
        $db = Database::getConnection();
        $villa = self::find($villaId, $userId, $isAdmin);
        if (!$villa) return false;

        $db->prepare("UPDATE villa_images SET is_cover = 0 WHERE villa_id = :villa_id")->execute(['villa_id' => $villaId]);
        $stmt = $db->prepare("UPDATE villa_images SET is_cover = 1 WHERE id = :id AND villa_id = :villa_id");
        return $stmt->execute(['id' => $imageId, 'villa_id' => $villaId]);
    }

    public static function duplicate(int $id, int $userId, bool $isAdmin = false): ?int {
        $db = Database::getConnection();
        $original = self::find($id, $userId, $isAdmin);
        if (!$original) {
            Helpers::setFlash('error', 'Kopyalanacak konut bulunamadı.');
            return null;
        }

        $ownerId = (int)$original['user_id'];

        $newName = $original['name'] . ' (Kopya)';
        $slug = 'draft-' . time() . '-' . rand(1000, 9999);
        $icalToken = Helpers::generateToken(32);

        $stmt = $db->prepare("
            INSERT INTO villas (
                user_id, name, slug, short_description, detailed_description,
                city, district, address, google_maps_url, deposit_amount, pool_heating_fee, cleaning_days, cleaning_fee, google_drive_link, max_guests,
                bedrooms, bathrooms, features, min_stay_nights, checkin_time, checkout_time,
                property_type, status, ical_export_token
            ) VALUES (
                :user_id, :name, :slug, :short_desc, :detailed_desc,
                :city, :district, :address, :google_maps, :deposit_amount, :pool_heating_fee, :cleaning_days, :cleaning_fee, :google_drive_link, :max_guests,
                :bedrooms, :bathrooms, :features, :min_stay, :checkin, :checkout,
                :property_type, 'active', :ical_token
            )
        ");

        $features = is_array($original['features'] ?? null) ? json_encode($original['features'], JSON_UNESCAPED_UNICODE) : ($original['features'] ?? '[]');

        $success = $stmt->execute([
            'user_id' => $ownerId,
            'name' => $newName,
            'slug' => $slug,
            'short_desc' => $original['short_description'] ?? '',
            'detailed_desc' => $original['detailed_description'] ?? '',
            'city' => $original['city'],
            'district' => $original['district'],
            'address' => $original['address'] ?? '',
            'google_maps' => $original['google_maps_url'] ?? '',
            'deposit_amount' => $original['deposit_amount'] ?? '',
            'pool_heating_fee' => $original['pool_heating_fee'] ?? '',
            'cleaning_days' => (int)($original['cleaning_days'] ?? 0),
            'cleaning_fee' => $original['cleaning_fee'] ?? '',
            'google_drive_link' => $original['google_drive_link'] ?? '',
            'max_guests' => (int)($original['max_guests'] ?? 4),
            'bedrooms' => (int)($original['bedrooms'] ?? 2),
            'bathrooms' => (int)($original['bathrooms'] ?? 1),
            'features' => $features,
            'min_stay' => (int)($original['min_stay_nights'] ?? 1),
            'checkin' => $original['checkin_time'] ?? '',
            'checkout' => $original['checkout_time'] ?? '',
            'property_type' => $original['property_type'] ?? 'Villa',
            'ical_token' => $icalToken
        ]);

        if ($success) {
            $newVillaId = (int)$db->lastInsertId();

            // Duplicate seasonal price periods
            $pricesStmt = $db->prepare("SELECT * FROM price_periods WHERE villa_id = :original_id");
            $pricesStmt->execute(['original_id' => $id]);
            $pricePeriods = $pricesStmt->fetchAll();

            if (!empty($pricePeriods)) {
                $insPrice = $db->prepare("
                    INSERT INTO price_periods (villa_id, start_date, end_date, nightly_price, weekly_price, min_stay_nights, title)
                    VALUES (:villa_id, :start_date, :end_date, :nightly_price, :weekly_price, :min_stay_nights, :title)
                ");
                foreach ($pricePeriods as $pp) {
                    $insPrice->execute([
                        'villa_id' => $newVillaId,
                        'start_date' => $pp['start_date'],
                        'end_date' => $pp['end_date'],
                        'nightly_price' => $pp['nightly_price'],
                        'weekly_price' => $pp['weekly_price'] ?? null,
                        'min_stay_nights' => $pp['min_stay_nights'] ?? null,
                        'title' => $pp['title'] ?? null
                    ]);
                }
            }

            Helpers::auditLog($userId, 'VILLA_DUPLICATED', 'villas', $newVillaId, ['name' => $newName, 'original_id' => $id]);
            Helpers::setFlash('success', "Konut bilgileri ve dönemsel fiyatları başarıyla kopyalandı ({$newName}).");
            return $newVillaId;
        }

        return null;
    }

    public static function toggleStatus(int $id, int $userId, bool $isAdmin = false): ?string {
        $db = Database::getConnection();
        $villa = self::find($id, $userId, $isAdmin);
        if (!$villa) return null;

        $newStatus = ($villa['status'] === 'active') ? 'passive' : 'active';
        $stmt = $db->prepare("UPDATE villas SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $success = $stmt->execute(['status' => $newStatus, 'id' => $id]);

        if ($success) {
            Helpers::auditLog($userId, 'VILLA_STATUS_TOGGLED', 'villas', $id, ['new_status' => $newStatus]);
            return $newStatus;
        }

        return null;
    }
}
