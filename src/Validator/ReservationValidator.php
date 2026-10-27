<?php
declare(strict_types=1);

namespace App\Validator;

use App\Database;
use App\Helpers;

class ReservationValidator {
    /**
     * Checks if a proposed reservation date range overlaps with any existing booked, blocked, or optional date.
     */
    public static function checkOverlap(int $villaId, string $startDate, string $endDate, ?int $excludeReservationId = null): array {
        if (strtotime($startDate) >= strtotime($endDate)) {
            return [
                'has_overlap' => true,
                'message' => 'Çıkış tarihi, giriş tarihinden sonraki bir gün olmalıdır.'
            ];
        }

        $db = Database::getConnection();

        $sql = "
            SELECT * FROM reservations 
            WHERE villa_id = :villa_id 
              AND status IN ('booked', 'blocked', 'optional')
              AND (:start_date < end_date AND :end_date > start_date)
        ";

        $params = [
            'villa_id' => $villaId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($excludeReservationId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeReservationId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $overlapping = $stmt->fetchAll();

        if (count($overlapping) > 0) {
            $conflict = $overlapping[0];
            $statusText = match ($conflict['status']) {
                'booked' => 'Rezerve edilmiş',
                'blocked' => 'Kapalı / Bloke edilmiş',
                'optional' => 'Opsiyonel tutulan',
                default => 'Dolu'
            };

            $startTr = Helpers::formatDateTR($conflict['start_date']);
            $endTr = Helpers::formatDateTR($conflict['end_date']);

            return [
                'has_overlap' => true,
                'message' => "Seçilen tarihler çakışıyor! {$startTr} - {$endTr} tarihleri arasında zaten ({$statusText}) bir kayıt bulunuyor.",
                'conflicting_reservation' => $conflict
            ];
        }

        return [
            'has_overlap' => false,
            'message' => ''
        ];
    }
}
