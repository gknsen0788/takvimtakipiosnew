<?php
declare(strict_types=1);

namespace App\Models;

use App\Database;
use App\Helpers;
use App\Validator\ReservationValidator;
use PDO;

class Reservation {
    public static function create(array $data, int $userId): array {
        $villaId = (int)$data['villa_id'];
        $reservationId = !empty($data['reservation_id']) ? (int)$data['reservation_id'] : null;

        // Validate overlap (excluding current reservation if editing)
        $validation = ReservationValidator::checkOverlap($villaId, $data['start_date'], $data['end_date'], $reservationId);
        if ($validation['has_overlap']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        $db = Database::getConnection();
        $status = $data['status'] ?? 'booked';
        $source = $data['source'] ?? 'manual';
        $bookingDate = !empty($data['booking_date']) ? $data['booking_date'] : date('Y-m-d');

        if (($data['status'] ?? '') === 'agency' || !empty($data['agency_name'])) {
            $status = 'booked';
            $source = 'acente';
        }

        if ($reservationId) {
            $stmt = $db->prepare("
                UPDATE reservations SET
                    start_date = :start_date,
                    end_date = :end_date,
                    status = :status,
                    source = :source,
                    booking_date = :booking_date,
                    customer_name = :customer_name,
                    customer_phone = :customer_phone,
                    agency_name = :agency_name,
                    price = :price,
                    down_payment = :down_payment,
                    remaining_payment = :remaining_payment,
                    note = :note,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND villa_id = :villa_id
            ");
            $success = $stmt->execute([
                'id' => $reservationId,
                'villa_id' => $villaId,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $status,
                'source' => $source,
                'booking_date' => $bookingDate,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'agency_name' => $data['agency_name'] ?? null,
                'price' => isset($data['price']) && $data['price'] !== '' ? (float)$data['price'] : null,
                'down_payment' => isset($data['down_payment']) && $data['down_payment'] !== '' ? (float)$data['down_payment'] : null,
                'remaining_payment' => isset($data['remaining_payment']) && $data['remaining_payment'] !== '' ? (float)$data['remaining_payment'] : null,
                'note' => $data['note'] ?? null
            ]);
            if ($success) {
                Helpers::auditLog($userId, 'RESERVATION_UPDATED', 'reservations', $reservationId);
                return ['success' => true, 'id' => $reservationId, 'message' => 'Rezervasyon güncellendi.'];
            }
        } else {
            $stmt = $db->prepare("
                INSERT INTO reservations (
                    villa_id, start_date, end_date, status, source, booking_date,
                    external_calendar_id, customer_name, customer_phone, agency_name, price, down_payment, remaining_payment, note
                ) VALUES (
                    :villa_id, :start_date, :end_date, :status, :source, :booking_date,
                    :external_calendar_id, :customer_name, :customer_phone, :agency_name, :price, :down_payment, :remaining_payment, :note
                )
            ");

            $success = $stmt->execute([
                'villa_id' => $villaId,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $status,
                'source' => $source,
                'booking_date' => $bookingDate,
                'external_calendar_id' => $data['external_calendar_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'agency_name' => $data['agency_name'] ?? null,
                'price' => isset($data['price']) && $data['price'] !== '' ? (float)$data['price'] : null,
                'down_payment' => isset($data['down_payment']) && $data['down_payment'] !== '' ? (float)$data['down_payment'] : null,
                'remaining_payment' => isset($data['remaining_payment']) && $data['remaining_payment'] !== '' ? (float)$data['remaining_payment'] : null,
                'note' => $data['note'] ?? null
            ]);

            if ($success) {
                $id = (int)$db->lastInsertId();
                Helpers::auditLog($userId, 'RESERVATION_CREATED', 'reservations', $id);
                return ['success' => true, 'id' => $id, 'message' => 'Rezervasyon eklendi.'];
            }
        }

        return ['success' => false, 'message' => 'Veritabanı kaydı sırasında hata oluştu.'];
    }

    public static function delete(int $id, int $userId): bool {
        $db = Database::getConnection();
        $isAdmin = \App\Auth::isAdmin();

        if ($isAdmin) {
            $stmt = $db->prepare("SELECT id FROM reservations WHERE id = :id");
            $stmt->execute(['id' => $id]);
        } else {
            $stmt = $db->prepare("
                SELECT r.id FROM reservations r 
                JOIN villas v ON r.villa_id = v.id 
                WHERE r.id = :id AND v.user_id = :user_id
            ");
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
        }

        $res = $stmt->fetch();
        if (!$res) return false;

        $delStmt = $db->prepare("DELETE FROM reservations WHERE id = :id");
        $success = $delStmt->execute(['id' => $id]);

        if ($success) {
            Helpers::auditLog($userId, 'RESERVATION_DELETED', 'reservations', $id);
        }

        return $success;
    }

    public static function getAllReservations(int $villaId, ?string $year = null): array {
        $db = Database::getConnection();
        if ($year === null) {
            $year = date('Y');
        }

        if ($year === 'all') {
            $stmt = $db->prepare("
                SELECT * FROM reservations 
                WHERE villa_id = :villa_id 
                ORDER BY updated_at DESC, created_at DESC, id DESC
            ");
            $stmt->execute(['villa_id' => $villaId]);
        } else {
            $stmt = $db->prepare("
                SELECT * FROM reservations 
                WHERE villa_id = :villa_id 
                  AND (start_date LIKE :year_prefix OR end_date LIKE :year_prefix)
                ORDER BY updated_at DESC, created_at DESC, id DESC
            ");
            $stmt->execute([
                'villa_id' => $villaId,
                'year_prefix' => $year . '%'
            ]);
        }

        return $stmt->fetchAll();
    }

    public static function getCalendarEvents(int $villaId, string $start, string $end): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM reservations 
            WHERE villa_id = :villa_id 
              AND end_date >= :start 
              AND start_date <= :end
            ORDER BY start_date ASC
        ");
        $stmt->execute([
            'villa_id' => $villaId,
            'start' => $start,
            'end' => $end
        ]);
        return $stmt->fetchAll();
    }

    public static function getPublicCalendarEvents(int $villaId, string $start, string $end): array {
        $events = self::getCalendarEvents($villaId, $start, $end);
        // Strip out private customer information for public view
        $publicEvents = [];
        foreach ($events as $e) {
            $publicEvents[] = [
                'id' => $e['id'],
                'start' => $e['start_date'],
                'end' => $e['end_date'],
                'status' => $e['status'],
                'title' => match($e['status']) {
                    'booked' => 'Rezerve / Dolu',
                    'blocked' => 'Kapalı / Müsait Değil',
                    'optional' => 'Opsiyonlu',
                    default => 'Dolu'
                }
            ];
        }
        return $publicEvents;
    }
}
