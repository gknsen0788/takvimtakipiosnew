<?php
declare(strict_types=1);

namespace App;

use App\Models\Reservation;
use App\Models\Villa;
use App\Database;

class IcalEngine {
    /**
     * Generates RFC 5545 compliant ICS text for a villa using its unique export token
     */
    public static function exportIcs(string $token): string {
        $villa = Villa::findByIcalToken($token);
        if (!$villa) {
            header('HTTP/1.0 404 Not Found');
            echo 'Calendar token not found or villa inactive.';
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM reservations 
            WHERE villa_id = :villa_id 
              AND status IN ('booked', 'blocked', 'optional')
            ORDER BY start_date ASC
        ");
        $stmt->execute(['villa_id' => $villa['id']]);
        $reservations = $stmt->fetchAll();

        $appName = APP_NAME;
        $villaName = addcslashes($villa['name'], ",;");

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//{$appName}//Villa Takvim Export 1.0//TR\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:{$villaName} - Müsaitlik Takvimi\r\n";

        foreach ($reservations as $res) {
            $dtStart = date('Ymd', strtotime($res['start_date']));
            $dtEnd = date('Ymd', strtotime($res['end_date']));
            $uid = "res-{$res['id']}@villatakvim.com";
            $dtStamp = date('Ymd\THis\Z');

            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:{$uid}\r\n";
            $ics .= "DTSTAMP:{$dtStamp}\r\n";
            $ics .= "DTSTART;VALUE=DATE:{$dtStart}\r\n";
            $ics .= "DTEND;VALUE=DATE:{$dtEnd}\r\n";
            $ics .= "SUMMARY:Dolu / Rezerve\r\n";
            $ics .= "STATUS:CONFIRMED\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";
        return $ics;
    }

    /**
     * Parses an external iCal/ICS URL and syncs event entries
     */
    public static function syncExternalCalendar(array $externalCal): bool {
        $db = Database::getConnection();
        $calId = (int)$externalCal['id'];
        $villaId = (int)$externalCal['villa_id'];
        $url = $externalCal['ical_url'];

        // Fetch URL content securely using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (VillaTakvim iCal Sync Engine)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $icsContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || empty($icsContent)) {
            $errorMsg = "HTTP Error $httpCode: " . ($curlError ?: 'İçerik boş döndü');
            $stmt = $db->prepare("UPDATE external_calendars SET sync_status = 'failed', last_error = :err, last_synced_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute(['err' => $errorMsg, 'id' => $calId]);
            return false;
        }

        // Parse VEVENT items
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $icsContent, $matches);
        if (empty($matches[1])) {
            $stmt = $db->prepare("UPDATE external_calendars SET sync_status = 'success', last_error = NULL, last_synced_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute(['id' => $calId]);
            return true;
        }

        // Clear existing external reservations for ONLY this external calendar (Manual ones are untouched!)
        $clearStmt = $db->prepare("DELETE FROM reservations WHERE villa_id = :villa_id AND external_calendar_id = :cal_id");
        $clearStmt->execute(['villa_id' => $villaId, 'cal_id' => $calId]);

        $sourceType = $externalCal['source_type'] ?? 'external_ical';

        foreach ($matches[1] as $eventBlock) {
            $dtStart = self::extractIcsValue('DTSTART', $eventBlock);
            $dtEnd = self::extractIcsValue('DTEND', $eventBlock);
            $summary = self::extractIcsValue('SUMMARY', $eventBlock) ?: 'Dış iCal Rezervasyonu';

            if ($dtStart && $dtEnd) {
                $startDate = self::parseIcsDate($dtStart);
                $endDate = self::parseIcsDate($dtEnd);

                if ($startDate && $endDate && strtotime($startDate) < strtotime($endDate)) {
                    // Check if overlaps with manual reservation. If overlap exists, we still log external reservation or skip if needed.
                    $insertStmt = $db->prepare("
                        INSERT INTO reservations (
                            villa_id, start_date, end_date, status, source,
                            external_calendar_id, agency_name, customer_name, note
                        ) VALUES (
                            :villa_id, :start_date, :end_date, 'booked', :source,
                            :cal_id, :agency_name, NULL, :note
                        )
                    ");
                    $insertStmt->execute([
                        'villa_id' => $villaId,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'source' => $sourceType,
                        'cal_id' => $calId,
                        'agency_name' => $externalCal['name'],
                        'note' => "iCal ile otomatik aktarıldı ({$summary})"
                    ]);
                }
            }
        }

        $stmt = $db->prepare("UPDATE external_calendars SET sync_status = 'success', last_error = NULL, last_synced_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute(['id' => $calId]);
        return true;
    }

    private static function extractIcsValue(string $key, string $block): ?string {
        if (preg_match('/' . $key . '(?:;[^:]+)?:([^\r\n]+)/i', $block, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private static function parseIcsDate(string $val): ?string {
        $val = preg_replace('/[^0-9]/', '', $val);
        if (strlen($val) >= 8) {
            $y = substr($val, 0, 4);
            $m = substr($val, 4, 2);
            $d = substr($val, 6, 2);
            return "{$y}-{$m}-{$d}";
        }
        return null;
    }
}
