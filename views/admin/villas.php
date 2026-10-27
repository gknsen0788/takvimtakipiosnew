<?php
use App\Helpers;
$title = 'Tüm Villalar - Admin';
require __DIR__ . '/../layouts/header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Admin - Tüm Villalar</h1>
        <p class="text-slate-500 text-sm">Sistemdeki tüm villa sahiplerine ait villalar.</p>
    </div>
    <div class="flex items-center space-x-2 text-xs">
        <a href="<?= APP_URL ?>/admin/users" class="px-3 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold rounded-lg">Kullanıcılar</a>
        <a href="<?= APP_URL ?>/admin/villas" class="px-3 py-2 bg-indigo-600 text-white font-semibold rounded-lg">Villalar</a>
        <a href="<?= APP_URL ?>/admin/audit-logs" class="px-3 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold rounded-lg">Audit Loglar</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700">
            <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4">Villa Adı</th>
                    <th class="py-3.5 px-4">Sahibi</th>
                    <th class="py-3.5 px-4">Konum</th>
                    <th class="py-3.5 px-4">Durum</th>
                    <th class="py-3.5 px-4 text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($villas as $v): ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-800"><?= Helpers::sanitize($v['name']) ?></td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-800"><?= Helpers::sanitize($v['owner_name']) ?></div>
                            <div class="text-[11px] text-slate-400 font-mono"><?= Helpers::sanitize($v['owner_email']) ?></div>
                        </td>
                        <td class="py-3.5 px-4"><?= Helpers::sanitize($v['district']) ?>, <?= Helpers::sanitize($v['city']) ?></td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $v['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                                <?= $v['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/calendar" class="text-indigo-600 font-semibold hover:underline">Takvime Git</a>
                            <a href="<?= APP_URL ?>/villa/<?= $v['slug'] ?>" target="_blank" class="text-slate-500 hover:underline">Public</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
