<?php
declare(strict_types=1);

namespace App;

use PDO;

/**
 * Class WebPush
 * 
 * Standart Web Push (RFC 8291 / RFC 8292 VAPID) Yerel Bildirim Motoru.
 * Herhangi bir harici servis (OneSignal, Firebase vb.) gerektirmeden doğrudan
 * Google FCM (Android/Chrome) ve Apple Push Service (iOS PWA/Safari) sunucularına
 * şifreli bildirim iletir.
 */
class WebPush
{
    private static ?array $vapidKeys = null;

    /**
     * VAPID anahtarlarını yükler veya ilk defa gerekiyorsa config/vapid.json dosyasından okur.
     */
    public static function getVapidKeys(): array
    {
        if (self::$vapidKeys !== null) {
            return self::$vapidKeys;
        }

        $vapidFile = __DIR__ . '/../config/vapid.json';
        if (file_exists($vapidFile)) {
            $data = json_decode((string)file_get_contents($vapidFile), true);
            if (!empty($data['publicKey']) && !empty($data['privateKey'])) {
                self::$vapidKeys = $data;
                return self::$vapidKeys;
            }
        }

        // Sabit tanımlı VAPID anahtarları (Varsayılan)
        self::$vapidKeys = [
            'publicKey' => 'BCQxMAHWAzPWv9bqsPrRVB68wEpSW__hLYnJQPOFgqDN3xHGpIXQSGKAD20Al_cze8HfA-GOTdTjYEQr-lxq6F0',
            'privateKey' => 'Vdg-lxKAnSd1NTq9X_W3V8vHWsbQYVoM1U9OlHKw9-o',
            'subject' => 'mailto:info@takvimtakip.com.tr'
        ];

        return self::$vapidKeys;
    }

    /**
     * Tarayıcı için VAPID Public Key döner.
     */
    public static function getPublicKey(): string
    {
        return self::getVapidKeys()['publicKey'];
    }

    /**
     * Veritabanı tablosunu otomatik oluşturur (yoksa).
     */
    public static function initTable(): void
    {
        try {
            $db = Database::getConnection();
            $db->exec("
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
        } catch (\Throwable $e) {}
    }

    /**
     * Kullanıcının tarayıcı / cihaz aboneliğini veritabanına kaydeder veya günceller.
     */
    public static function saveSubscription(int $userId, string $endpoint, string $p256dh, string $auth, ?string $userAgent = null): bool
    {
        self::initTable();
        $db = Database::getConnection();

        $checkStmt = $db->prepare("SELECT id FROM user_push_subscriptions WHERE endpoint = :endpoint LIMIT 1");
        $checkStmt->execute(['endpoint' => $endpoint]);
        $existingId = $checkStmt->fetchColumn();

        if ($existingId) {
            $stmt = $db->prepare("
                UPDATE user_push_subscriptions 
                SET user_id = :user_id,
                    p256dh = :p256dh,
                    auth = :auth,
                    user_agent = :user_agent,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            return $stmt->execute([
                'user_id' => $userId,
                'p256dh' => $p256dh,
                'auth' => $auth,
                'user_agent' => substr((string)$userAgent, 0, 255),
                'id' => $existingId
            ]);
        }

        $stmt = $db->prepare("
            INSERT INTO user_push_subscriptions (user_id, endpoint, p256dh, auth, user_agent, created_at, updated_at)
            VALUES (:user_id, :endpoint, :p256dh, :auth, :user_agent, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");

        return $stmt->execute([
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'p256dh' => $p256dh,
            'auth' => $auth,
            'user_agent' => substr((string)$userAgent, 0, 255)
        ]);
    }

    /**
     * Geçersiz veya aboneliği sonlanmış bir endpoint'i siler.
     */
    public static function deleteSubscription(string $endpoint): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM user_push_subscriptions WHERE endpoint = :endpoint");
            return $stmt->execute(['endpoint' => $endpoint]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Belirli bir kullanıcıya (user_id) ait kayıtlı tüm cihazlara Web Push bildirimi gönderir.
     */
    public static function sendToUser(int $userId, string $title, string $message, ?string $url = null, array $extra = []): array
    {
        self::initTable();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM user_push_subscriptions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($subscriptions)) {
            return [
                'success' => false,
                'sent_count' => 0,
                'error' => "Kullanıcıya (#{$userId}) ait kayıtlı bir bildirim aboneliği (cihaz) bulunamadı. Kullanıcının panelden bildirimlere izin vermesi gerekmektedir.",
                'details' => []
            ];
        }

        $payload = json_encode(array_merge([
            'title' => $title,
            'body' => $message,
            'url' => $url ?: ((defined('APP_URL') ? APP_URL : 'https://takvimtakip.com.tr') . '/dashboard'),
            'icon' => (defined('APP_URL') ? APP_URL : 'https://takvimtakip.com.tr') . '/css/icon-192.png?v=7',
            'badge' => (defined('APP_URL') ? APP_URL : 'https://takvimtakip.com.tr') . '/css/icon-192.png?v=7'
        ], $extra), JSON_UNESCAPED_UNICODE);

        $sentCount = 0;
        $details = [];

        foreach ($subscriptions as $sub) {
            $res = self::sendNotification($sub['endpoint'], $sub['p256dh'], $sub['auth'], $payload);
            $details[] = [
                'subscription_id' => $sub['id'],
                'endpoint' => substr($sub['endpoint'], 0, 60) . '...',
                'status_code' => $res['status_code'],
                'success' => $res['success'],
                'error' => $res['error'] ?? null
            ];

            if ($res['success']) {
                $sentCount++;
            } elseif ($res['status_code'] === 404 || $res['status_code'] === 410) {
                // Abonelik süresi dolmuş veya iptal edilmiş -> DB'den temizle
                self::deleteSubscription($sub['endpoint']);
            }
        }

        return [
            'success' => ($sentCount > 0),
            'sent_count' => $sentCount,
            'total_targets' => count($subscriptions),
            'details' => $details
        ];
    }

    /**
     * Ertesi gün çıkış yapacak misafir için ev sahibine Web Push bildirimi gönderir.
     */
    public static function sendCheckoutReminder(array $details): array
    {
        $userId = (int)($details['user_id'] ?? 0);
        if ($userId <= 0) {
            return [
                'success' => false,
                'error' => 'Kullanıcı (Ev Sahibi) ID bulunamadı.'
            ];
        }

        $ownerName = $details['owner_name'] ?? 'Ev Sahibi';
        $villaName = $details['villa_name'] ?? 'Konutunuz';
        $customerName = !empty($details['customer_name']) && $details['customer_name'] !== 'Belirtilmedi' ? $details['customer_name'] : 'Misafiriniz';
        $checkoutTime = $details['checkout_time'] ?: '10:00';
        $remainingPayment = $details['remaining_payment_tr'] ?? '';

        $title = "🗓️ Yarın Çıkış Var: {$villaName}";
        $message = "Sayın {$ownerName}, yarın saat {$checkoutTime}'da {$customerName} konutunuzdan çıkış yapacaktır.";
        if (!empty($remainingPayment) && $remainingPayment !== '0 ₺') {
            $message .= " Kalan Tahsilat: {$remainingPayment}.";
        }

        $url = (defined('APP_URL') ? APP_URL : 'https://takvimtakip.com.tr') . '/dashboard';

        return self::sendToUser($userId, $title, $message, $url, [
            'type' => 'checkout_reminder',
            'villa_name' => $villaName,
            'end_date' => $details['end_date'] ?? null
        ]);
    }

    /**
     * RFC 8291 (aes128gcm) & RFC 8292 (VAPID) ile şifreli bildirim isteği gönderir.
     */
    public static function sendNotification(string $endpoint, string $userP256dh, string $userAuth, string $payload): array
    {
        try {
            $vapid = self::getVapidKeys();
            $origin = parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);

            // 1. JWT Oluştur (VAPID Authorization Header)
            $jwt = self::createVapidJwt($origin, $vapid['subject'], $vapid['privateKey'], $vapid['publicKey']);

            // 2. Mesajı RFC 8291 aes128gcm standardında şifrele
            $encrypted = self::encryptPayload($payload, $userP256dh, $userAuth);

            // 3. HTTP cURL isteği gönder
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encrypted);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/octet-stream',
                'Content-Encoding: aes128gcm',
                'TTL: 86400',
                'Urgency: high',
                'Authorization: vapid t=' . $jwt . ', k=' . $vapid['publicKey']
            ]);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!empty($curlError)) {
                return [
                    'success' => false,
                    'status_code' => 0,
                    'error' => 'cURL Hatası: ' . $curlError
                ];
            }

            $success = ($statusCode >= 200 && $statusCode < 300);

            return [
                'success' => $success,
                'status_code' => $statusCode,
                'response' => $response,
                'error' => $success ? null : "Sunucu yanıt kodu: {$statusCode} - {$response}"
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'error' => 'Şifreleme/Gönderim Hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * VAPID ES256 imzalı JWT üretir.
     */
    private static function createVapidJwt(string $audience, string $subject, string $privateKeyB64, string $publicKeyB64): string
    {
        $header = self::base64urlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = self::base64urlEncode(json_encode([
            'aud' => $audience,
            'exp' => time() + 86400,
            'sub' => $subject
        ]));

        $unsigned = $header . '.' . $payload;

        // PKCS#8 EC Private Key PEM oluştur
        $d = self::base64urlDecode($privateKeyB64);
        $rawPub = self::base64urlDecode($publicKeyB64);
        $x = substr($rawPub, 1, 32);
        $y = substr($rawPub, 33, 32);

        $pkcs8 = "\x30\x81\x87\x02\x01\x00\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x04\x6d\x30\x6b\x02\x01\x01\x04\x20" . $d . "\xa1\x44\x03\x42\x00\x04" . $x . $y;
        $pem = "-----BEGIN PRIVATE KEY-----\n" . chunk_split(base64_encode($pkcs8), 64, "\n") . "-----END PRIVATE KEY-----\n";

        $keyResource = openssl_pkey_get_private($pem);
        if (!$keyResource) {
            throw new \RuntimeException('VAPID Private Key PEM formatına dönüştürülemedi.');
        }

        openssl_sign($unsigned, $derSig, $keyResource, OPENSSL_ALGO_SHA256);
        $rawSig = self::derToRawSig($derSig);

        return $unsigned . '.' . self::base64urlEncode($rawSig);
    }

    /**
     * RFC 8291 aes128gcm şifreleme algoritması.
     */
    private static function encryptPayload(string $payload, string $userP256dhB64, string $userAuthB64): string
    {
        $userRawPub = self::base64urlDecode($userP256dhB64);
        $userAuth = self::base64urlDecode($userAuthB64);

        if (strlen($userRawPub) !== 65 || strlen($userAuth) < 16) {
            throw new \InvalidArgumentException('Geçersiz kullanıcı push anahtarları (p256dh veya auth).');
        }

        // Ephemeral Sunucu EC Anahtar Çifti Üret
        $config = ['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC];
        $candidatePaths = [
            'C:\\wamp64\\bin\\php\\php8.2.29\\extras\\ssl\\openssl.cnf',
            'C:\\wamp64\\bin\\apache\\apache2.4.62.1\\conf\\openssl.cnf',
            '/etc/ssl/openssl.cnf',
            '/etc/pki/tls/openssl.cnf',
            '/usr/local/ssl/openssl.cnf',
            '/usr/lib/ssl/openssl.cnf'
        ];
        foreach ($candidatePaths as $path) {
            if (file_exists($path)) {
                $config['config'] = $path;
                break;
            }
        }

        $localKeyRes = openssl_pkey_new($config);
        if (!$localKeyRes) {
            // Fallback: Yerel sistem varsayılan openssl konfigürasyonu
            $localKeyRes = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        }
        if (!$localKeyRes) {
            throw new \RuntimeException('Sunucu tarafında ephemeral EC anahtar çifti üretilemedi.');
        }

        $localDetails = openssl_pkey_get_details($localKeyRes);
        $localX = str_pad($localDetails['ec']['x'], 32, "\0", STR_PAD_LEFT);
        $localY = str_pad($localDetails['ec']['y'], 32, "\0", STR_PAD_LEFT);
        $localRawPub = "\x04" . $localX . $localY;

        // Kullanıcının Public Key'ini PEM formatına sarıp shared secret (ECDH) hesapla
        $userSpki = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\x00" . $userRawPub;
        $userPubPem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($userSpki), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $userPubKeyRes = openssl_pkey_get_public($userPubPem);

        $sharedSecret = openssl_pkey_derive($userPubKeyRes, $localKeyRes, 256);
        if (!$sharedSecret) {
            throw new \RuntimeException('ECDH Shared Secret anahtarı türetilemedi.');
        }

        // HKDF Anahtarları Türet (RFC 8291)
        $salt = random_bytes(16);
        $prk = hash_hkdf('sha256', $sharedSecret, 32, "WebPush: info\0" . $userRawPub . $localRawPub, $userAuth);
        $contentEncryptionKey = hash_hkdf('sha256', $prk, 16, "Content-Encoding: aes128gcm\0", $salt);
        $nonce = hash_hkdf('sha256', $prk, 12, "Content-Encoding: nonce\0", $salt);

        // Payload + \x02 Record Delimiter
        $payloadWithPadding = $payload . "\x02";

        // AES-128-GCM Şifrele
        $ciphertext = openssl_encrypt($payloadWithPadding, 'aes-128-gcm', $contentEncryptionKey, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($ciphertext === false) {
            throw new \RuntimeException('AES-128-GCM şifreleme işlemi başarısız oldu.');
        }

        // Gövde = Salt (16 bytes) + Record Size (4096 = 4 bytes) + Key Length (1 byte) + Local Public Key (65 bytes) + Ciphertext + Tag (16 bytes)
        return $salt . pack('N', 4096) . chr(65) . $localRawPub . $ciphertext . $tag;
    }

    /**
     * DER formatındaki ECDSA imzasını 64 byte ham (r + s) formatına çevirir.
     */
    private static function derToRawSig(string $der): string
    {
        $pos = 3;
        $lenR = ord($der[$pos]);
        $pos++;
        $r = substr($der, $pos, $lenR);
        $pos += $lenR + 1;
        $lenS = ord($der[$pos]);
        $pos++;
        $s = substr($der, $pos, $lenS);

        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
