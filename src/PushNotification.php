<?php
declare(strict_types=1);

namespace App;

/**
 * Class PushNotification
 * 
 * OneSignal REST API Entegrasyonu ile Mobil (Android / iOS) ve Web Push Bildirimleri Servisi.
 */
class PushNotification
{
    private const ONESIGNAL_API_URL = 'https://api.onesignal.com/notifications';

    /**
     * Belirli bir kullanıcıya (user_id / external_id) push bildirimi gönderir.
     *
     * @param int|string $userId Kullanıcı ID (external_id)
     * @param string $title Bildirim Başlığı
     * @param string $message Bildirim Mesajı
     * @param string|null $url Bildirime tıklandığında açılacak sayfa URL'si
     * @param array $data Ekstra veri parametreleri
     * @return array Gönderim sonucu ['success' => bool, 'response' => array|string, 'error' => string|null]
     */
    public static function sendToUser(int|string $userId, string $title, string $message, ?string $url = null, array $data = []): array
    {
        $appId = defined('ONESIGNAL_APP_ID') ? ONESIGNAL_APP_ID : '';
        $restKey = defined('ONESIGNAL_REST_API_KEY') ? ONESIGNAL_REST_API_KEY : '';

        if (empty($appId) || empty($restKey) || str_contains($appId, 'BURAYA') || str_contains($restKey, 'BURAYA')) {
            return [
                'success' => false,
                'error' => 'OneSignal App ID veya REST API Key config/app.php dosyasında tanımlanmamış.',
                'response' => null
            ];
        }

        $targetUrl = $url ?: (defined('APP_URL') ? APP_URL : 'https://takvimtakip.com.tr') . '/dashboard';

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
            'url' => $targetUrl,
            'web_url' => $targetUrl,
            'app_url' => $targetUrl,
            'data' => array_merge(['user_id' => (string)$userId], $data),
            'priority' => 10,
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1
        ];

        return self::executeRequest($payload, $restKey);
    }

    /**
     * Ertesi gün çıkış yapacak misafir için ev sahibine OneSignal push bildirimi gönderir.
     *
     * @param array $details Çıkış ve rezervasyon detayları
     * @return array Gönderim sonucu
     */
    public static function sendCheckoutReminder(array $details): array
    {
        $userId = $details['user_id'] ?? null;
        if (empty($userId)) {
            return [
                'success' => false,
                'error' => 'Kullanıcı (Ev Sahibi) ID bulunamadı.',
                'response' => null
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

        $data = [
            'type' => 'checkout_reminder',
            'reservation_id' => $details['id'] ?? null,
            'villa_name' => $villaName,
            'end_date' => $details['end_date'] ?? null
        ];

        return self::sendToUser($userId, $title, $message, $url, $data);
    }

    /**
     * Tüm kullanıcılara genel duyuru push bildirimi gönderir.
     *
     * @param string $title
     * @param string $message
     * @param string|null $url
     * @return array
     */
    public static function sendToAll(string $title, string $message, ?string $url = null): array
    {
        $appId = defined('ONESIGNAL_APP_ID') ? ONESIGNAL_APP_ID : '';
        $restKey = defined('ONESIGNAL_REST_API_KEY') ? ONESIGNAL_REST_API_KEY : '';

        if (empty($appId) || empty($restKey)) {
            return [
                'success' => false,
                'error' => 'OneSignal API anahtarları tanımlanmamış.',
                'response' => null
            ];
        }

        $targetUrl = $url ?: (defined('APP_URL') ? APP_URL : 'https://takvimtakip.com.tr') . '/dashboard';

        $payload = [
            'app_id' => $appId,
            'included_segments' => ['Total Subscriptions'],
            'headings' => [
                'tr' => $title,
                'en' => $title
            ],
            'contents' => [
                'tr' => $message,
                'en' => $message
            ],
            'url' => $targetUrl,
            'web_url' => $targetUrl,
            'priority' => 10
        ];

        return self::executeRequest($payload, $restKey);
    }

    /**
     * OneSignal REST API cURL isteğini yürütür.
     */
    private static function executeRequest(array $payload, string $restKey): array
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ONESIGNAL_API_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Key ' . $restKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!empty($curlError)) {
            return [
                'success' => false,
                'error' => 'cURL Hatası: ' . $curlError,
                'response' => null
            ];
        }

        $decodedResponse = json_decode((string)$response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'http_code' => $httpCode,
                'response' => $decodedResponse,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'http_code' => $httpCode,
            'error' => $decodedResponse['errors'][0] ?? 'OneSignal API hata döndürdü (' . $httpCode . ')',
            'response' => $decodedResponse
        ];
    }
}
