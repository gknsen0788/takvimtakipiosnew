<?php
use App\Helpers;
$title = 'Audit Loglar - Admin';
require __DIR__ . '/../layouts/header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Admin - Sistem İşlem Geçmişi (Audit Logs)</h1>
        <p class="text-slate-500 text-sm">Sistemde gerçekleştirilen kritik işlemlerin kayıtları.</p>
    </div>
    <div class="flex items-center space-x-2 text-xs">
        <a href="<?= APP_URL ?>/admin/users" class="px-3 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold rounded-lg">Kullanıcılar</a>
        <a href="<?= APP_URL ?>/admin/villas" class="px-3 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold rounded-lg">Villalar</a>
        <a href="<?= APP_URL ?>/admin/audit-logs" class="px-3 py-2 bg-indigo-600 text-white font-semibold rounded-lg">Audit Loglar</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4">Tarih</th>
                    <th class="py-3.5 px-4">Kullanıcı ID</th>
                    <th class="py-3.5 px-4">Aksiyon</th>
                    <th class="py-3.5 px-4">Entity</th>
                    <th class="py-3.5 px-4">Detaylar</th>
                    <th class="py-3.5 px-4">IP Adresi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-500"><?= $log['created_at'] ?></td>
                        <td class="py-3 px-4 font-semibold text-indigo-600">User #<?= $log['user_id'] ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800">
                                <?= Helpers::sanitize($log['action']) ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600"><?= Helpers::sanitize($log['entity_type']) ?> #<?= $log['entity_id'] ?></td>
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-600 max-w-xs truncate"><?= Helpers::sanitize($log['details']) ?></td>
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-400"><?= Helpers::sanitize($log['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
