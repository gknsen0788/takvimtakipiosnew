<?php
declare(strict_types=1);

namespace App\Models;

use App\Database;
use App\Helpers;
use PDO;

class PricePeriod {
    public static function create(array $data, int $userId): bool {
        $db = Database::getConnection();

        $nightlyPrice = (float)($data['nightly_price'] ?? 0);
        $weeklyPrice = isset($data['weekly_price']) && $data['weekly_price'] !== '' ? (float)$data['weekly_price'] : ($nightlyPrice * 7);

        $stmt = $db->prepare("
            INSERT INTO price_periods (villa_id, start_date, end_date, nightly_price, weekly_price, min_stay_nights, title)
            VALUES (:villa_id, :start_date, :end_date, :price, :weekly_price, :min_stay, :title)
        ");
        $success = $stmt->execute([
            'villa_id' => $data['villa_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'price' => $nightlyPrice,
            'weekly_price' => $weeklyPrice,
            'min_stay' => !empty($data['min_stay_nights']) ? (int)$data['min_stay_nights'] : null,
            'title' => $data['title'] ?? null
        ]);

        if ($success) {
            Helpers::auditLog($userId, 'PRICE_PERIOD_CREATED', 'price_periods', (int)$db->lastInsertId());
        }

        return $success;
    }

    public static function delete(int $id, int $userId): bool {
        $db = Database::getConnection();
        $isAdmin = \App\Auth::isAdmin();

        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM price_periods WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } else {
            $stmt = $db->prepare("
                DELETE FROM price_periods 
                WHERE id = :id AND villa_id IN (SELECT id FROM villas WHERE user_id = :user_id)
            ");
            return $stmt->execute([
                'id' => $id,
                'user_id' => $userId
            ]);
        }
    }

    public static function getByVilla(int $villaId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM price_periods WHERE villa_id = :villa_id ORDER BY start_date ASC");
        $stmt->execute(['villa_id' => $villaId]);
        return $stmt->fetchAll();
    }

    public static function copyFromVilla(int $targetVillaId, int $sourceVillaId, int $userId, bool $isAdmin = false): bool {
        $db = Database::getConnection();

        $targetVilla = Villa::find($targetVillaId, $userId, $isAdmin);
        $sourceVilla = Villa::find($sourceVillaId, $userId, $isAdmin);

        if (!$targetVilla || !$sourceVilla) {
            Helpers::setFlash('error', 'Konut bulunamadı veya yetkiniz yok.');
            return false;
        }

        $stmt = $db->prepare("SELECT * FROM price_periods WHERE villa_id = :source_id ORDER BY start_date ASC");
        $stmt->execute(['source_id' => $sourceVillaId]);
        $sourcePrices = $stmt->fetchAll();

        if (empty($sourcePrices)) {
            Helpers::setFlash('error', "Seçilen kaynak konutta ({$sourceVilla['name']}) tanımlı dönemsel fiyat bulunamadı.");
            return false;
        }

        // Clear existing target price periods
        $delStmt = $db->prepare("DELETE FROM price_periods WHERE villa_id = :target_id");
        $delStmt->execute(['target_id' => $targetVillaId]);

        $insStmt = $db->prepare("
            INSERT INTO price_periods (villa_id, start_date, end_date, nightly_price, weekly_price, min_stay_nights, title)
            VALUES (:villa_id, :start_date, :end_date, :nightly_price, :weekly_price, :min_stay_nights, :title)
        ");

        $copiedCount = 0;
        foreach ($sourcePrices as $sp) {
            $success = $insStmt->execute([
                'villa_id' => $targetVillaId,
                'start_date' => $sp['start_date'],
                'end_date' => $sp['end_date'],
                'nightly_price' => $sp['nightly_price'],
                'weekly_price' => $sp['weekly_price'] ?? null,
                'min_stay_nights' => $sp['min_stay_nights'] ?? null,
                'title' => $sp['title'] ?? null
            ]);
            if ($success) $copiedCount++;
        }

        Helpers::auditLog($userId, 'PRICE_PERIODS_COPIED', 'price_periods', $targetVillaId, [
            'source_villa' => $sourceVilla['name'],
            'target_villa' => $targetVilla['name'],
            'copied_count' => $copiedCount
        ]);

        Helpers::setFlash('success', "{$sourceVilla['name']} konutundaki {$copiedCount} adet dönemsel fiyat tarifesi {$targetVilla['name']} konutuna başarıyla kopyalandı.");
        return true;
    }
}
