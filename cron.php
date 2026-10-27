<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

use App\Database;
use App\Models\ExternalCalendar;

// Initialize Database connection
Database::getConnection();

// Perform iCal sync
ExternalCalendar::syncAll();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Tüm iCal takvimleri başarıyla senkronize edildi.',
    'synced_at' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
