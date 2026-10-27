<?php
declare(strict_types=1);

namespace App;

class Mailer {
    public static function sendPasswordReset(string $toEmail, string $userName, string $resetUrl): bool {
        $subject = "Şifre Sıfırlama Talebi - " . APP_NAME;

        if (str_contains($resetUrl, 'localhost')) {
            $resetUrl = str_replace(['http://localhost/takvim', 'http://localhost'], 'https://takvimtakip.com.tr/calendar', $resetUrl);
        }

        $plainText = "Merhaba " . $userName . ",\n\n";
        $plainText .= "Hesabınız için şifre sıfırlama talebinde bulunuldu. Şifrenizi yenilemek için aşağıdaki bağlantıya tıklayabilirsiniz:\n\n";
        $plainText .= $resetUrl . "\n\n";
        $plainText .= "* Bu bağlantı 1 saat süreyle geçerlidir. Talebi siz yapmadıysanız bu e-postayı dikkate almayınız.\n\n";
        $plainText .= "--\n" . APP_NAME;

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Şifre Sıfırlama Talebi</title>
        </head>
        <body style="margin:0; padding:20px; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#334155;">
            <div style="max-width:540px; margin:0 auto; background-color:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:30px; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                <div style="text-align:center; margin-bottom:20px;">
                    <h2 style="color:#4f46e5; margin:0; font-size:24px;">📅 ' . APP_NAME . '</h2>
                </div>
                <h3 style="color:#1e293b; font-size:18px; margin-top:0;">Merhaba ' . htmlspecialchars($userName) . ',</h3>
                <p style="font-size:14px; line-height:1.6; color:#475569;">
                    Hesabınız için şifre sıfırlama talebinde bulunuldu. Şifrenizi yenilemek için aşağıdaki butona tıklayabilirsiniz:
                </p>
                <div style="text-align:center; margin:30px 0;">
                    <a href="' . $resetUrl . '" target="_blank" style="background-color:#4f46e5; color:#ffffff; font-weight:bold; font-size:15px; text-decoration:none; padding:13px 26px; border-radius:8px; display:inline-block;">Şifremi Sıfırla &rarr;</a>
                </div>
                <p style="font-size:13px; line-height:1.5; color:#64748b;">
                    Buton çalışmıyorsa aşağıdaki bağlantıyı tarayıcınıza kopyalayabilirsiniz:
                </p>
                <div style="background-color:#f8fafc; border:1px solid #e2e8f0; padding:10px; border-radius:6px; font-size:12px; word-break:break-all; color:#4f46e5; font-family:monospace; margin-bottom:20px;">
                    <a href="' . $resetUrl . '" style="color:#4f46e5; text-decoration:underline;">' . $resetUrl . '</a>
                </div>
                <p style="font-size:12px; color:#ef4444; margin-bottom:0;">
                    * Bu bağlantı <strong>1 saat</strong> süreyle geçerlidir. Talebi siz yapmadıysanız bu e-postayı dikkate almayınız.
                </p>
                <hr style="border:none; border-top:1px solid #f1f5f9; margin:25px 0 15px 0;">
                <div style="text-align:center; font-size:11px; color:#94a3b8;">
                    &copy; ' . date('Y') . ' ' . APP_NAME . ' - Otomatik Bildirim Sistemidir.
                </div>
            </div>
        </body>
        </html>';

        return self::sendMultipart($toEmail, $subject, $plainText, $html);
    }

    public static function sendNewUserRegistrationNotification(string $fullName, string $userEmail, string $userPhone): bool {
        $toEmail = "gokhansen-1988@hotmail.com";
        $subject = "🎉 Yeni Üye Kaydı: " . $fullName . " - " . APP_NAME;

        $registerDate = date('d.m.Y H:i');

        $plainText = "Yeni Üye Kayıt Bildirimi - " . APP_NAME . "\n\n";
        $plainText .= "Sisteme yeni bir kullanıcı kayıt oldu. Detaylar aşağıdadır:\n\n";
        $plainText .= "Ad Soyad : " . $fullName . "\n";
        $plainText .= "E-posta  : " . $userEmail . "\n";
        $plainText .= "Telefon  : " . ($userPhone ?: 'Belirtilmedi') . "\n";
        $plainText .= "Tarih    : " . $registerDate . "\n\n";
        $plainText .= "--\n" . APP_NAME;

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Yeni Üye Kayıt Bildirimi</title>
        </head>
        <body style="margin:0; padding:20px; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#334155;">
            <div style="max-width:540px; margin:0 auto; background-color:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:30px; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                <div style="text-align:center; margin-bottom:20px;">
                    <h2 style="color:#4f46e5; margin:0; font-size:24px;">🎉 Yeni Kullanıcı Kaydı</h2>
                    <p style="color:#64748b; font-size:13px; margin-top:5px;">' . APP_NAME . ' sistemine yeni bir üye kayıt oldu.</p>
                </div>
                
                <div style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:20px; margin-bottom:25px;">
                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                        <tr>
                            <td style="padding:8px 0; color:#64748b; font-weight:bold; width:120px;">Ad Soyad:</td>
                            <td style="padding:8px 0; color:#0f172a; font-weight:bold;">' . htmlspecialchars($fullName) . '</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#64748b; font-weight:bold;">E-posta:</td>
                            <td style="padding:8px 0; color:#4f46e5; font-weight:bold;"><a href="mailto:' . htmlspecialchars($userEmail) . '" style="color:#4f46e5; text-decoration:none;">' . htmlspecialchars($userEmail) . '</a></td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#64748b; font-weight:bold;">Telefon:</td>
                            <td style="padding:8px 0; color:#0f172a; font-weight:bold;">' . htmlspecialchars($userPhone ?: 'Belirtilmedi') . '</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#64748b; font-weight:bold;">Kayıt Tarihi:</td>
                            <td style="padding:8px 0; color:#0f172a;">' . $registerDate . '</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0; color:#64748b; font-weight:bold;">Varsayılan Lisans:</td>
                            <td style="padding:8px 0; color:#10b981; font-weight:bold;">14 Gün (Ücretsiz Deneme)</td>
                        </tr>
                    </table>
                </div>

                <div style="text-align:center; margin:25px 0 15px 0;">
                    <a href="https://takvimtakip.com.tr/calendar/admin/users" target="_blank" style="background-color:#4f46e5; color:#ffffff; font-weight:bold; font-size:14px; text-decoration:none; padding:12px 24px; border-radius:8px; display:inline-block;">Admin Panelinde İncele &rarr;</a>
                </div>

                <hr style="border:none; border-top:1px solid #f1f5f9; margin:25px 0 15px 0;">
                <div style="text-align:center; font-size:11px; color:#94a3b8;">
                    Gönderen: destek@takvimtakip.com.tr | &copy; ' . date('Y') . ' ' . APP_NAME . ' Otomatik Bildirim Sistemi
                </div>
            </div>
        </body>
        </html>';

        return self::sendMultipart($toEmail, $subject, $plainText, $html);
    }

    public static function sendCheckoutReminderToOwner(array $details): bool {
        $toEmail = $details['owner_email'];
        $ownerName = $details['owner_name'] ?? 'Ev Sahibi';
        $villaName = $details['villa_name'] ?? 'Konutunuz';
        $endDate = $details['end_date_tr'] ?? '';

        $subject = $villaName . " - ÇIKIŞ BİLDİRİMİ";

        $plainText = "Sayın " . $ownerName . ",\n\n";
        $plainText .= "Yarın (" . $endDate . ") " . $villaName . " adlı konuttan misafiriniz çıkış yapacaktır.\n\n";
        $plainText .= "--\n" . APP_NAME;

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Çıkış Bildirimi</title>
        </head>
        <body style="margin:0; padding:20px; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#334155;">
            <div style="max-width:580px; margin:0 auto; background-color:#ffffff; border-radius:16px; border:1px solid #e2e8f0; padding:30px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.05);">
                
                <!-- Brand Header -->
                <div style="text-align:center; margin-bottom:25px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                    <h2 style="color:#1e293b; margin:0; font-size:22px; font-weight:800;">📅 ' . APP_NAME . '</h2>
                </div>

                <h3 style="color:#1e293b; font-size:16px; margin-top:0;">Sayın ' . htmlspecialchars($ownerName) . ',</h3>
                <p style="font-size:14px; line-height:1.6; color:#475569; margin-bottom:25px;">
                    Yarın (' . htmlspecialchars($endDate) . ') <strong>' . htmlspecialchars($villaName) . '</strong> adlı konuttan misafiriniz çıkış yapacaktır.
                </p>

                <!-- Call to Action -->
                <div style="text-align:center; margin:25px 0 15px 0;">
                    <a href="https://takvimtakip.com.tr/login" target="_blank" style="background-color:#4f46e5; color:#ffffff; font-weight:bold; font-size:14px; text-decoration:none; padding:13px 28px; border-radius:10px; display:inline-block; box-shadow:0 4px 12px rgba(79,70,229,0.3);">Takvim Paneline Git &rarr;</a>
                </div>
            </div>
        </body>
        </html>';

        return self::sendMultipart($toEmail, $subject, $plainText, $html);
    }

    public static function sendTrialExpirationReminder(string $toEmail, string $userName, int $daysLeft = 3): bool {
        $subject = "⏳ TakvimTakip Ücretsiz Deneme Süreniz Dolmak Üzere!";
        $whatsappUrl = "https://wa.me/905437161546";

        $plainText = "Merhaba Sayın " . $userName . ",\n\n";
        $plainText .= "TakvimTakip platformundaki ücretsiz deneme süreniz " . ($daysLeft > 0 ? "son {$daysLeft} gün içinde doluyor." : "dolmak üzere.") . "\n\n";
        $plainText .= "Defter, Excel ve karmaşık tablolardan kurtularak sağladığınız bu düzeni korumak ve takvimlerinizi kesintisiz yönetmeye devam etmek için aboneliğinizi hemen başlatabilirsiniz.\n\n";
        $plainText .= "Yıllık 1.000 TL'den başlayan fiyatlarla aboneliğinizi hemen başlatmak için WhatsApp destek hattımız üzerinden bizimle iletişime geçebilirsiniz:\n";
        $plainText .= $whatsappUrl . "\n\n";
        $plainText .= "--\n" . APP_NAME . " Ekibi";

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Deneme Süresi Hatırlatması</title>
            <style>
                @media only screen and (max-width: 480px) {
                    .body-wrapper { padding: 8px 4px !important; }
                    .email-container { padding: 20px 14px !important; border-radius: 12px !important; }
                    .cta-button { display: block !important; width: 100% !important; padding: 15px 10px !important; box-sizing: border-box !important; font-size: 16px !important; }
                    .pricing-box { font-size: 14px !important; padding: 10px 12px !important; }
                    .feature-list { padding-left: 15px !important; }
                }
            </style>
        </head>
        <body class="body-wrapper" style="margin:0; padding:15px 8px; background-color:#f8fafc; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color:#334155;">
            <div class="email-container" style="max-width:680px; width:100%; margin:0 auto; background-color:#ffffff; border-radius:20px; border:1px solid #e2e8f0; padding:30px 24px; box-shadow:0 10px 25px -5px rgba(15,23,42,0.06); box-sizing:border-box;">
                
                <!-- Logo & Brand Header -->
                <div style="text-align:center; margin-bottom:24px; border-bottom:1px solid #f1f5f9; padding-bottom:16px;">
                    <div style="display:inline-block; background-color:#e0e7ff; color:#4f46e5; padding:10px 20px; border-radius:12px; font-size:20px; font-weight:800; tracking-letter:0.5px;">
                        📅 ' . APP_NAME . '
                    </div>
                </div>

                <!-- Alert Badge -->
                <div style="background-color:#fff7ed; border:1px solid #ffedd5; border-radius:12px; padding:12px 16px; margin-bottom:20px; text-align:center;">
                    <span style="color:#c2410c; font-weight:700; font-size:14px;">
                        ⏳ Deneme Süreniz ' . ($daysLeft > 0 ? "Son <strong>{$daysLeft} Gün</strong> İçinde Sona Eriyor" : "Dolmak Üzere!") . '
                    </span>
                </div>

                <h3 style="color:#0f172a; font-size:17px; font-weight:700; margin-top:0; margin-bottom:12px;">
                    Merhaba Sayın ' . htmlspecialchars($userName) . ',
                </h3>

                <p style="font-size:14px; line-height:1.7; color:#475569; margin-bottom:16px;">
                    <strong>TakvimTakip</strong> ile konut yönetimini pratikleştirdiğiniz için teşekkür ederiz! Ücretsiz deneme sürenizin sonuna yaklaşıyorsunuz.
                </p>

                <p style="font-size:14px; line-height:1.7; color:#475569; margin-bottom:22px;">
                    Defter, Excel ve karmaşık tablolardan kurtularak sağladığınız bu düzeni korumak ve takvimlerinizi <strong>kesintisiz yönetmeye devam etmek</strong> için aboneliğinizi hemen başlatabilirsiniz.
                </p>

                <!-- Feature Highlights Box -->
                <div style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:18px 16px; margin-bottom:24px;">
                    <h4 style="color:#1e293b; font-size:13px; font-weight:700; margin:0 0 12px 0; text-transform:uppercase; letter-spacing:0.5px;">
                        ⚡ TakvimTakip İle Neleri Kesintisiz Kullanabilirsiniz?
                    </h4>
                    
                    <ul class="feature-list" style="margin:0; padding-left:18px; color:#334155; font-size:13px; line-height:1.9;">
                        <li style="margin-bottom:6px;"><strong>Otomatik iCal Senkronizasyonu:</strong> Airbnb, Booking, Vrbo takvimlerinizi çifte rezervasyon riski olmadan otomatik eşleştirin.</li>
                        <li style="margin-bottom:6px;"><strong>Mobil & Web Uyumlu Panel:</strong> İster cepten ister bilgisayardan tüm takvimlerinize anında erişin.</li>
                        <li style="margin-bottom:0;"><strong>Müşteriye Özel Bilgilendirme:</strong> Tek tıkla WhatsApp veya SMS üzerinden konut detaylarını ve yol tarifini iletin.</li>
                    </ul>
                </div>

                <!-- Pricing Banner -->
                <div class="pricing-box" style="background-color:#f0fdf4; border:1.5px dashed #22c55e; border-radius:14px; padding:12px 16px; margin-bottom:22px; text-align:center;">
                    <span style="color:#15803d; font-weight:800; font-size:15px; display:inline-block;">
                        🏷️ Yıllık 1.000 TL\'den başlayan fiyatlarla!
                    </span>
                </div>

                <!-- Call to Action Button (WhatsApp Direct) -->
                <div style="text-align:center; margin:22px 0 10px 0;">
                    <a class="cta-button" href="' . $whatsappUrl . '" target="_blank" style="background-color:#25D366; color:#ffffff; font-weight:bold; font-size:16px; text-decoration:none; padding:15px 36px; border-radius:14px; display:inline-block; box-shadow:0 6px 18px rgba(37,211,102,0.35);">
                        💬 Abonelik Başlat &rarr;
                    </a>
                </div>
            </div>
        </body>
        </html>';

        return self::sendMultipart($toEmail, $subject, $plainText, $html);
    }

    public static function send(string $to, string $subject, string $htmlBody): bool {
        $plainText = strip_tags($htmlBody);
        return self::sendMultipart($to, $subject, $plainText, $htmlBody);
    }

    public static function sendMultipart(string $to, string $subject, string $plainText, string $htmlBody): bool {
        $fromUser = defined('SMTP_USER') ? SMTP_USER : '';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : APP_NAME;
        $host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
        $pass = defined('SMTP_PASS') ? SMTP_PASS : '';

        if (empty($fromUser) || empty($pass) || empty($host)) {
            return self::sendNativeMultipart($to, $subject, $plainText, $htmlBody, $fromName, $fromUser);
        }

        $sentViaSmtp = false;

        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $remote = ($port === 465 ? 'ssl://' : 'tcp://') . $host . ':' . $port;
            $socket = @stream_socket_client($remote, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);

            if (!$socket) {
                return self::sendNativeMultipart($to, $subject, $plainText, $htmlBody, $fromName, $fromUser);
            }

            self::readResponse($socket);

            fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
            self::readResponse($socket);

            if ($port === 587) {
                fwrite($socket, "STARTTLS\r\n");
                self::readResponse($socket);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
                self::readResponse($socket);
            }

            fwrite($socket, "AUTH LOGIN\r\n");
            self::readResponse($socket);

            fwrite($socket, base64_encode($fromUser) . "\r\n");
            self::readResponse($socket);

            fwrite($socket, base64_encode($pass) . "\r\n");
            $authResp = self::readResponse($socket);
            if (!str_starts_with(trim($authResp), '235')) {
                fclose($socket);
                return self::sendNativeMultipart($to, $subject, $plainText, $htmlBody, $fromName, $fromUser);
            }

            fwrite($socket, "MAIL FROM: <{$fromUser}>\r\n");
            self::readResponse($socket);

            fwrite($socket, "RCPT TO: <{$to}>\r\n");
            self::readResponse($socket);

            fwrite($socket, "DATA\r\n");
            self::readResponse($socket);

            $boundary = "----=_Part_" . md5((string)time()) . "_" . rand(1000, 9999);
            $msgHost = str_contains($fromUser, '@') ? explode('@', $fromUser)[1] : 'takvimtakip.com.tr';
            $messageId = "<" . time() . "." . bin2hex(random_bytes(8)) . "@" . $msgHost . ">";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromUser}>\r\n";
            $headers .= "Reply-To: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromUser}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $headers .= "Message-ID: {$messageId}\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "X-Mailer: TakvimTakip Mailer 1.0\r\n";

            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $plainText . "\r\n\r\n";

            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $htmlBody . "\r\n\r\n";

            $body .= "--{$boundary}--\r\n";

            $fullPayload = $headers . "\r\n" . $body . "\r\n.\r\n";
            $fullPayload = preg_replace("/\r\n|\r|\n/", "\r\n", $fullPayload);
            
            fwrite($socket, $fullPayload);
            $sentViaSmtp = true;

            @self::readResponse($socket);
            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);

            return true;
        } catch (\Throwable $e) {
            if ($sentViaSmtp) {
                return true;
            }
            return self::sendNativeMultipart($to, $subject, $plainText, $htmlBody, $fromName, $fromUser);
        }
    }

    private static function sendNativeMultipart(string $to, string $subject, string $plainText, string $htmlBody, string $fromName, string $fromUser): bool {
        $fromEmail = !empty($fromUser) ? $fromUser : 'destek@' . ($_SERVER['HTTP_HOST'] ?? 'takvimtakip.com.tr');
        $boundary = "----=_Part_" . md5((string)time()) . "_" . rand(1000, 9999);

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
        $headers .= "Reply-To: <{$fromEmail}>\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $plainText . "\r\n\r\n";

        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";

        $body .= "--{$boundary}--\r\n";

        return @mail($to, $subject, $body, $headers, "-f " . $fromEmail);
    }

    private static function readResponse($socket): string {
        $response = "";
        while ($str = fgets($socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    }

    public static function debugSend(string $to, string $subject, string $htmlBody): array {
        $plainText = strip_tags($htmlBody);
        $logs = [];
        $fromUser = defined('SMTP_USER') ? SMTP_USER : '';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : APP_NAME;
        $host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
        $pass = defined('SMTP_PASS') ? SMTP_PASS : '';

        $logs[] = "Sunucu: $host:$port ($fromUser)";

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $remote = ($port === 465 ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);

        if (!$socket) {
            $logs[] = "❌ SMTP Bağlantı Hatası ($errno): $errstr";
            return ['success' => false, 'logs' => $logs];
        }

        $logs[] = "CONNECT: " . trim(self::readResponse($socket));

        fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        $logs[] = "EHLO: " . trim(self::readResponse($socket));

        if ($port === 587) {
            fwrite($socket, "STARTTLS\r\n");
            $logs[] = "STARTTLS: " . trim(self::readResponse($socket));
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
            $logs[] = "EHLO (TLS): " . trim(self::readResponse($socket));
        }

        fwrite($socket, "AUTH LOGIN\r\n");
        $logs[] = "AUTH LOGIN: " . trim(self::readResponse($socket));

        fwrite($socket, base64_encode($fromUser) . "\r\n");
        $logs[] = "USER SENT: " . trim(self::readResponse($socket));

        fwrite($socket, base64_encode($pass) . "\r\n");
        $authResp = self::readResponse($socket);
        $logs[] = "PASS SENT: " . trim($authResp);

        if (!str_starts_with(trim($authResp), '235')) {
            $logs[] = "❌ SMTP Kimlik Doğrulama Başarısız!";
            fclose($socket);
            return ['success' => false, 'logs' => $logs];
        }

        fwrite($socket, "MAIL FROM: <{$fromUser}>\r\n");
        $logs[] = "MAIL FROM: " . trim(self::readResponse($socket));

        fwrite($socket, "RCPT TO: <{$to}>\r\n");
        $rcptResp = self::readResponse($socket);
        $logs[] = "RCPT TO: " . trim($rcptResp);

        fwrite($socket, "DATA\r\n");
        $logs[] = "DATA: " . trim(self::readResponse($socket));

        $boundary = "----=_Part_" . md5((string)time()) . "_" . rand(1000, 9999);
        $msgHost = str_contains($fromUser, '@') ? explode('@', $fromUser)[1] : 'takvimtakip.com.tr';
        $messageId = "<" . time() . "." . bin2hex(random_bytes(8)) . "@" . $msgHost . ">";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromUser}>\r\n";
        $headers .= "Reply-To: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromUser}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Message-ID: {$messageId}\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "X-Mailer: TakvimTakip Mailer 1.0\r\n";

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $plainText . "\r\n\r\n";

        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";

        $body .= "--{$boundary}--\r\n";

        $fullPayload = $headers . "\r\n" . $body . "\r\n.\r\n";
        $fullPayload = preg_replace("/\r\n|\r|\n/", "\r\n", $fullPayload);
        fwrite($socket, $fullPayload);
        $dataResp = self::readResponse($socket);
        $logs[] = "BODY SENT: " . trim($dataResp);

        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        $isOk = str_starts_with(trim($dataResp), '250');
        return ['success' => $isOk, 'logs' => $logs];
    }
}
