<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dbDir = dirname(DB_PATH);
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            $isNew = !file_exists(DB_PATH);

            try {
                self::$instance = new PDO('sqlite:' . DB_PATH);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec('PRAGMA foreign_keys = ON;');

                if ($isNew || filesize(DB_PATH) === 0) {
                    self::initDatabase();
                } else {
                    self::runMigrations();
                }
            } catch (PDOException $e) {
                die('Database Connection Error: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    private static function initDatabase(): void {
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            self::$instance->exec($sql);
            self::seedDefaultAdmin();
        }
    }

    private static function runMigrations(): void {
        try {
            self::$instance->exec("ALTER TABLE reservations ADD COLUMN down_payment REAL;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE reservations ADD COLUMN remaining_payment REAL;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE reservations ADD COLUMN agency_name TEXT;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE reservations ADD COLUMN booking_date TEXT;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE price_periods ADD COLUMN weekly_price REAL;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE villas ADD COLUMN property_type TEXT DEFAULT 'Villa';");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE villas ADD COLUMN deposit_amount TEXT;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE villas ADD COLUMN google_drive_link TEXT;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE users ADD COLUMN license_expires_at TEXT;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE villas ADD COLUMN cleaning_days INTEGER DEFAULT 0;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE villas ADD COLUMN cleaning_fee TEXT;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE villas ADD COLUMN pool_heating_fee TEXT;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email TEXT NOT NULL,
                    token TEXT NOT NULL UNIQUE,
                    expires_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS rate_limits (
                    key_id TEXT PRIMARY KEY,
                    hits INTEGER DEFAULT 1,
                    expires_at INTEGER NOT NULL
                );
            ");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS user_tokens (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    token TEXT NOT NULL UNIQUE,
                    expires_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
            ");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("ALTER TABLE reservations ADD COLUMN checkout_push_sent_at DATETIME NULL;");
        } catch (\PDOException $e) {}

        try {
            self::$instance->exec("
                CREATE TABLE IF NOT EXISTS user_push_subscriptions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    endpoint TEXT NOT NULL UNIQUE,
                    p256dh TEXT NOT NULL,
                    auth TEXT NOT NULL,
                    user_agent TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
            ");
        } catch (\PDOException $e) {}

        try {
            $allVillas = self::$instance->query("SELECT id, name, slug FROM villas")->fetchAll();
            foreach ($allVillas as $v) {
                $cleanSlug = Helpers::slugify($v['name']);
                if (empty($cleanSlug)) continue;

                $counter = 1;
                $testSlug = $cleanSlug;
                while (true) {
                    $chk = self::$instance->prepare("SELECT COUNT(*) FROM villas WHERE slug = :slug AND id != :id");
                    $chk->execute(['slug' => $testSlug, 'id' => $v['id']]);
                    if ($chk->fetchColumn() == 0) break;
                    $testSlug = $cleanSlug . '-' . $counter;
                    $counter++;
                }
                $upd = self::$instance->prepare("UPDATE villas SET slug = :slug WHERE id = :id");
                $upd->execute(['slug' => $testSlug, 'id' => $v['id']]);
            }
        } catch (\Throwable $e) {}
    }

    private static function seedDefaultAdmin(): void {
        $stmt = self::$instance->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $adminPass = password_hash('170227', PASSWORD_BCRYPT);
            $stmt = self::$instance->prepare("
                INSERT INTO users (full_name, email, phone, password_hash, role, status, subscription_plan, max_villas, license_expires_at)
                VALUES ('Sistem Yöneticisi', 'admin@takvimtakip.com', '05555555555', :pass, 'admin', 'active', 'enterprise', 999, '2099-12-31')
            ");
            $stmt->execute(['pass' => $adminPass]);
        }
    }
}
