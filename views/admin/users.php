<?php
use App\Auth;
use App\Helpers;
$title = "Kullanıcı & Lisans Yönetimi - Admin Paneli";
require __DIR__ . '/../layouts/header.php';

$totalUsers = count($users);
$activeCount = 0;
$expiredCount = 0;
$warningCount = 0;

foreach ($users as $u) {
    if ($u['role'] === 'admin') continue;
    if ($u['license_status'] === 'active') $activeCount++;
    elseif ($u['license_status'] === 'warning') $warningCount++;
    elseif ($u['license_status'] === 'expired') $expiredCount++;
}
?>

<div class="space-y-6">
    <!-- Header Title & KPI Cards -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-user-shield text-indigo-600"></i>
                Kullanıcı & Lisans Yönetimi
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Sisteme kayıtlı üyeleri görüntüleyin, durumlarını değiştirin ve lisans sürelerini uzatın.</p>
        </div>
        <div>
            <button onclick="openAddUserModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center space-x-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>Yeni Kullanıcı Ekle</span>
            </button>
        </div>
    </div>

    <!-- Summary KPI Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Toplam Üye</div>
                <div class="text-xl font-bold text-slate-800"><?= $totalUsers ?></div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i class="fa-solid fa-circle-check text-xl"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Aktif Lisanslı</div>
                <div class="text-xl font-bold text-emerald-600"><?= $activeCount ?></div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
            <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Lisansı Az Kalan (≤5 gün)</div>
                <div class="text-xl font-bold text-rose-600"><?= $warningCount ?></div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
            <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i class="fa-solid fa-circle-xmark text-xl"></i>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500">Süresi Dolmuş</div>
                <div class="text-xl font-bold text-rose-600"><?= $expiredCount ?></div>
            </div>
        </div>
    </div>

    <!-- Search Bar & User Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="relative max-w-xs w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="userSearchInput" onkeyup="filterUsersTable()" placeholder="Kullanıcı adı, e-posta veya telefon ara..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
            </div>
            <div class="text-xs text-slate-500 font-medium">
                Gösterilen: <span id="visibleUserCount" class="font-bold text-slate-700"><?= count($users) ?></span> üye
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="usersTable">
                <thead>
                    <tr class="bg-slate-100/70 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Kullanıcı</th>
                        <th class="py-3 px-4">İletişim</th>
                        <th class="py-3 px-4 text-center">Rol / Konut</th>
                        <th class="py-3 px-4 text-center">Kayıt Tarihi</th>
                        <th class="py-3 px-4 text-center">Lisans Bitiş</th>
                        <th class="py-3 px-4 text-center">Kalan Süre</th>
                        <th class="py-3 px-4 text-center">Durum</th>
                        <th class="py-3 px-4 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php foreach ($users as $u): ?>
                        <tr class="user-row hover:bg-slate-50/80 transition" data-search="<?= strtolower(Helpers::sanitize($u['full_name'] . ' ' . $u['email'] . ' ' . $u['phone'])) ?>">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800"><?= Helpers::sanitize($u['full_name']) ?></div>
                                <div class="text-[10px] text-slate-400">ID: #<?= $u['id'] ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-700 font-medium"><?= Helpers::sanitize($u['email']) ?></div>
                                <div class="text-slate-400 text-[11px]"><?= Helpers::sanitize($u['phone']) ?></div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-700">Yönetici</span>
                                <?php elseif ($u['role'] === 'demo'): ?>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700">Demo</span>
                                <?php else: ?>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">Ev Sahibi</span>
                                <?php endif; ?>
                                <div class="text-[10px] text-slate-400 mt-0.5"><?= $u['villa_count'] ?> Konut</div>
                            </td>
                            <td class="py-3 px-4 text-center text-slate-600 font-medium">
                                <?= date('d.m.Y', strtotime($u['created_at'])) ?>
                            </td>
                            <td class="py-3 px-4 text-center font-bold">
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="text-indigo-600">Sınırsız</span>
                                <?php elseif (!empty($u['license_expires_at'])): ?>
                                    <span class="<?= $u['license_status'] === 'expired' ? 'text-rose-600' : ($u['license_status'] === 'warning' ? 'text-amber-600' : 'text-slate-700') ?>">
                                        <?= date('d.m.Y', strtotime($u['license_expires_at'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-600">Sınırsız</span>
                                <?php elseif ($u['remaining_days'] === 'Sınırsız'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-600">Sınırsız</span>
                                <?php elseif ($u['remaining_days'] < 0): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Süresi Doldu</span>
                                <?php elseif ($u['remaining_days'] == 0): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Son Gün!</span>
                                <?php elseif ($u['remaining_days'] <= 5): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 animate-pulse border border-rose-300">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i><?= $u['remaining_days'] ?> Gün (Kritik)
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700"><?= $u['remaining_days'] ?> Gün Kaldı</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Pasif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                                    <button type="button" onclick='openEditUserModal(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-[11px] transition flex items-center space-x-1" title="Kullanıcıyı Düzenle">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Düzenle</span>
                                    </button>

                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <button type="button" onclick="openLicenseModal(<?= $u['id'] ?>, '<?= Helpers::sanitize($u['full_name']) ?>', '<?= $u['license_expires_at'] ?? '' ?>')" class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-[11px] transition flex items-center space-x-1" title="Lisans Süresini Uzat">
                                            <i class="fa-solid fa-calendar-plus text-xs"></i>
                                            <span>Süre Uzat</span>
                                        </button>
                                        
                                        <form action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/toggle-status" method="POST" class="inline" onsubmit="return confirm('Kullanıcı durumunu değiştirmek istediğinize emin misiniz?');">
                                            <?= Helpers::csrfField() ?>
                                            <button type="submit" class="px-2.5 py-1 rounded-lg <?= $u['status'] === 'active' ? 'bg-slate-100 hover:bg-slate-200 text-slate-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' ?> font-semibold text-[11px] transition">
                                                <?= $u['status'] === 'active' ? 'Pasife Al' : 'Aktif Et' ?>
                                            </button>
                                        </form>

                                        <?php if ($u['id'] !== Auth::id()): ?>
                                            <form action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirm('<?= Helpers::sanitize($u['full_name']) ?> kullanıcısını ve tüm verilerini silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                                                <?= Helpers::csrfField() ?>
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-[11px] transition flex items-center space-x-1" title="Kullanıcıyı Sil">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                    <span>Sil</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- LİSANS UZATMA MODALI -->
<div id="licenseModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-indigo-600"></i>
                Lisans Süresi Uzat
            </h3>
            <button onclick="closeLicenseModal()" class="text-slate-400 hover:text-slate-600 text-base">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="licenseForm" action="" method="POST" class="p-6 space-y-4">
            <?= Helpers::csrfField() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kullanıcı</label>
                <input type="text" id="modalUserName" disabled class="w-full px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mevcut Bitiş Tarihi</label>
                <input type="text" id="modalCurrentExp" disabled class="w-full px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs font-medium text-slate-600">
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-700">Hızlı Gün İlave Et</label>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setAddDays(10)" class="py-2 px-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs transition border border-indigo-200">+10 Gün</button>
                    <button type="button" onclick="setAddDays(30)" class="py-2 px-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs transition border border-indigo-200">+30 Gün</button>
                    <button type="button" onclick="setAddDays(90)" class="py-2 px-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs transition border border-indigo-200">+90 Gün</button>
                    <button type="button" onclick="setAddDays(365)" class="py-2 px-1 text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs transition border border-indigo-200">+1 Yıl</button>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Eklenecek Gün Sayısı</label>
                    <input type="number" name="days" id="modalDaysInput" min="1" max="3650" value="30" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="relative text-center py-1">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                    <span class="relative px-2 bg-white text-[10px] text-slate-400 font-semibold uppercase">veya tam tarih seçin</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Belirli Bitiş Tarihi Seç</label>
                    <input type="date" name="custom_date" id="modalCustomDateInput" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeLicenseModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">İptal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md transition flex items-center space-x-1">
                    <i class="fa-solid fa-check"></i>
                    <span>Kaydet & Lisansı Uzat</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- YENİ KULLANICI EKLE MODALI -->
<div id="addUserModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-indigo-600"></i>
                Yeni Kullanıcı Ekle
            </h3>
            <button onclick="closeAddUserModal()" class="text-slate-400 hover:text-slate-600 text-base">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= APP_URL ?>/admin/users/create" method="POST" class="p-6 space-y-4">
            <?= Helpers::csrfField() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ad Soyad <span class="text-rose-500">*</span></label>
                <input type="text" name="full_name" required placeholder="Örn: Ahmet Yılmaz" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">E-posta Adresi <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="ahmet@example.com" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Telefon Numarası</label>
                    <input type="tel" name="phone" placeholder="0532 000 00 00" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Giriş Şifresi <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required minlength="6" placeholder="En az 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lisans Süresi</label>
                    <select name="license_days" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="7" selected>7 Gün (Varsayılan)</option>
                        <option value="14">14 Gün</option>
                        <option value="30">30 Gün (1 Ay)</option>
                        <option value="90">90 Gün (3 Ay)</option>
                        <option value="365">365 Gün (1 Yıl)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kullanıcı Rolü</label>
                    <select name="role" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="owner" selected>Ev Sahibi</option>
                        <option value="demo">Demo</option>
                        <option value="admin">Yönetici (Admin)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">İptal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md transition flex items-center space-x-1">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Kullanıcıyı Oluştur</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KULLANICI DÜZENLE MODALI -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-amber-600"></i>
                Kullanıcı Düzenle
            </h3>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 text-base">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editUserForm" action="" method="POST" class="p-6 space-y-4">
            <?= Helpers::csrfField() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ad Soyad <span class="text-rose-500">*</span></label>
                <input type="text" name="full_name" id="editFullName" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">E-posta Adresi <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="editEmail" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Telefon Numarası</label>
                    <input type="tel" name="phone" id="editPhone" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Yeni Şifre <span class="text-slate-400 font-normal">(Boş bırakırsanız değişmez)</span></label>
                <input type="password" name="password" minlength="6" placeholder="Değiştirmek istemiyorsanız boş bırakın" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lisans Bitiş Tarihi</label>
                    <input type="date" name="license_expires_at" id="editLicenseExp" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kullanıcı Rolü</label>
                    <select name="role" id="editRole" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="owner">Ev Sahibi</option>
                        <option value="demo">Demo</option>
                        <option value="admin">Yönetici (Admin)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">İptal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-md transition flex items-center space-x-1">
                    <i class="fa-solid fa-check"></i>
                    <span>Güncelle & Kaydet</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
}

function openEditUserModal(user) {
    document.getElementById('editUserForm').action = `<?= APP_URL ?>/admin/users/${user.id}/update`;
    document.getElementById('editFullName').value = user.full_name || '';
    document.getElementById('editEmail').value = user.email || '';
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editLicenseExp').value = user.license_expires_at || '';
    document.getElementById('editRole').value = user.role || 'owner';
    document.getElementById('editUserModal').classList.remove('hidden');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.add('hidden');
}

function openLicenseModal(userId, userName, currentExp) {
    document.getElementById('licenseForm').action = `<?= APP_URL ?>/admin/users/${userId}/extend-license`;
    document.getElementById('modalUserName').value = userName;
    document.getElementById('modalCurrentExp').value = currentExp ? new Date(currentExp).toLocaleDateString('tr-TR') : 'Tanımsız';
    document.getElementById('modalDaysInput').value = '30';
    document.getElementById('modalCustomDateInput').value = '';
    document.getElementById('licenseModal').classList.remove('hidden');
}

function closeLicenseModal() {
    document.getElementById('licenseModal').classList.add('hidden');
}

function setAddDays(days) {
    document.getElementById('modalDaysInput').value = days;
    document.getElementById('modalCustomDateInput').value = '';
}

function filterUsersTable() {
    const input = document.getElementById('userSearchInput');
    const filter = input.value.trim().toLowerCase();
    const rows = document.querySelectorAll('#usersTable .user-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (text.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('visibleUserCount').innerText = visibleCount;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
