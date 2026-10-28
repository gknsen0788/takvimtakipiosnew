<?php
declare(strict_types=1);

namespace App;

/**
 * Class PushNotification
 * 
 * OneSignal REST API Entegrasyonu ile Google Play Store (Android)
 * ve Apple App Store (iOS) yerel mobil push bildirimleri servisi.
 */
class PushNotification
{
    private const ONESIGNAL_API_URL = 'https://api.onesignal.com/notifications';

    /**
     * Belirli bir ev sahibine / kullanıcıya ait tüm mobil cihazlara OneSignal push bildirimi gönderir.
     */
    public static function sendToUser(int $userId, string $title, string $message, ?string $url = null, array $extra = []): array
    {
        $appId = defined('ONESIGNAL_APP_ID') ? ONESIGNAL_APP_ID : '';
        $restKey = defined('ONESIGNAL_REST_API_KEY') ? ONESIGNAL_REST_API_KEY : '';

        if (empty($appId) || empty($restKey)) {
            return [
                'success' => false,
                'error' => 'OneSignal App ID veya REST API Key config/app.php dosyasında tanımlanmamış.'
            ];
        }

        $payload = [
            'app_id' => $appId,
            'include_aliases' => [
                'external_id' => [(string)$userId]
            ],
            'target_channel' => 'push',
            'headings' => [
                'tr' => $title,
                'en' => $title
            ],
            'contents' => [
                'tr' => $message,
                'en' => $message
            ],
            'url' => $url ?: ((defined('APP_URL') ? APP_URL : 'https://takvimtakip.com.tr') . '/dashboard'),
            'data' => array_merge([
                'user_id' => $userId,
                'sent_at' => date('Y-m-d H:i:s')
            ], $extra),
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'android_accent_color' => 'FF4338CA',
            'small_icon' => 'app_icon'
        ];

        return self::executeRequest($payload, $restKey);
    }

    /**
     * Ertesi gün çıkış yapacak misafir için ev sahibine OneSignal push bildirimi gönderir.
     */
    public static function sendCheckoutReminder(array $details): array
    {
        $userId = (int)($details['user_id'] ?? 0);
        if ($userId <= 0) {
            return [
                'success' => false,
                'error' => 'Ev sahibi kullanıcı ID bulunamadı.'
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
     * OneSignal REST API cURL isteğini yürütür.
     */
    private static function executeRequest(array $payload, string $apiKey): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ONESIGNAL_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Key ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!empty($curlError)) {
            return [
                'success' => false,
                'http_code' => 0,
                'error' => 'cURL Hatası: ' . $curlError
            ];
        }

        $decoded = json_decode((string)$response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'http_code' => $httpCode,
                'response' => $decoded,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'http_code' => $httpCode,
            'error' => $decoded['errors'][0] ?? ('OneSignal API hata döndürdü (' . $httpCode . ')'),
            'response' => $decoded
        ];
    }
}
