<?php
declare(strict_types=1);

namespace App;

use PDO;

class RateLimiter {
    private static function initTable(): void {
        try {
            $db = Database::getConnection();
            $db->exec("
                CREATE TABLE IF NOT EXISTS rate_limits (
                    key_id TEXT PRIMARY KEY,
                    hits INTEGER DEFAULT 1,
                    expires_at INTEGER NOT NULL
                );
            ");
        } catch (\Exception $e) {
            // Table creation handle
        }
    }

    private static function purgeExpired(): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM rate_limits WHERE expires_at < :now");
            $stmt->execute(['now' => time()]);
        } catch (\Exception $e) {}
    }

    public static function isTooManyAttempts(string $key, int $maxAttempts): bool {
        self::initTable();
        self::purgeExpired();

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT hits FROM rate_limits WHERE key_id = :key AND expires_at >= :now LIMIT 1");
            $stmt->execute(['key' => $key, 'now' => time()]);
            $hits = (int)$stmt->fetchColumn();
            return $hits >= $maxAttempts;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function hit(string $key, int $decaySeconds = 900): int {
        self::initTable();
        self::purgeExpired();

        try {
            $db = Database::getConnection();
            $now = time();
            $expiresAt = $now + $decaySeconds;

            $stmt = $db->prepare("SELECT hits, expires_at FROM rate_limits WHERE key_id = :key LIMIT 1");
            $stmt->execute(['key' => $key]);
            $row = $stmt->fetch();

            if ($row) {
                $newHits = (int)$row['hits'] + 1;
                $upd = $db->prepare("UPDATE rate_limits SET hits = :hits WHERE key_id = :key");
                $upd->execute(['hits' => $newHits, 'key' => $key]);
                return $newHits;
            } else {
                $ins = $db->prepare("INSERT INTO rate_limits (key_id, hits, expires_at) VALUES (:key, 1, :exp)");
                $ins->execute(['key' => $key, 'exp' => $expiresAt]);
                return 1;
            }
        } catch (\Exception $e) {
            return 1;
        }
    }

    public static function clear(string $key): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM rate_limits WHERE key_id = :key");
            $stmt->execute(['key' => $key]);
        } catch (\Exception $e) {}
    }

    public static function getRemainingSeconds(string $key): int {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT expires_at FROM rate_limits WHERE key_id = :key AND expires_at > :now LIMIT 1");
            $stmt->execute(['key' => $key, 'now' => time()]);
            $exp = (int)$stmt->fetchColumn();
            if ($exp > 0) {
                return max(1, $exp - time());
            }
        } catch (\Exception $e) {}
        return 0;
    }
}
