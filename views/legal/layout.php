<?php
declare(strict_types=1);
/**
 * views/legal/layout.php
 * Standalone clean layout for Legal & Institutional pages (Gizlilik, Kullanım, KVKK, İletişim)
 */
$currentKey = $legalPageKey ?? 'privacy';
$appUrl = defined('APP_URL') ? APP_URL : '';
?>
<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle ?? 'Yasal Bilgilendirme') ?> - TakvimTakip</title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= $appUrl ?>/css/icon-192.png?v=7">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $appUrl ?>/css/apple-touch-icon.png?v=7">
    <meta name="theme-color" content="#4338ca">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?= $appUrl ?>/" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-600/30 group-hover:scale-105 transition-transform overflow-hidden">
                    <img src="<?= $appUrl ?>/uploads/logo-icon.jpg" alt="TakvimTakip" class="w-full h-full object-cover" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';" />
                    <i class="fa-solid fa-calendar-check text-lg" style="display:none;"></i>
                </div>
                <div>
                    <span class="font-extrabold text-lg text-slate-900 tracking-tight block leading-tight">TakvimTakip</span>
                    <span class="text-[10px] font-semibold text-slate-500 block -mt-0.5">Kurumsal & Yasal</span>
                </div>
            </a>

            <div class="flex items-center space-x-2 sm:space-x-3">
                <a href="<?= $appUrl ?>/" class="hidden sm:inline-flex items-center px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition">
                    <i class="fa-solid fa-house mr-1.5 text-slate-400"></i>Ana Sayfa
                </a>
                <a href="<?= $appUrl ?>/login" class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-95 rounded-xl shadow-md shadow-indigo-600/20 transition">
                    <i class="fa-solid fa-right-to-bracket mr-1.5"></i>Giriş Yap
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-grow py-8 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumbs -->
            <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
                <a href="<?= $appUrl ?>/" class="hover:text-indigo-600 transition flex items-center">
                    <i class="fa-solid fa-house mr-1"></i>Ana Sayfa
                </a>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
                <span class="text-slate-400">Kurumsal & Yasal</span>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
                <span class="font-semibold text-slate-800"><?= htmlspecialchars($pageTitle ?? 'Yasal Bilgilendirme') ?></span>
            </nav>

            <!-- Legal Tabs Bar -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-2 mb-8 border-b border-slate-200 scrollbar-none">
                <a href="<?= $appUrl ?>/gizlilik" class="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 <?= $currentKey === 'privacy' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Gizlilik Sözleşmesi</span>
                </a>
                <a href="<?= $appUrl ?>/kullanim-kosullari" class="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 <?= $currentKey === 'terms' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    <i class="fa-solid fa-file-contract"></i>
                    <span>Kullanım Koşulları</span>
                </a>
                <a href="<?= $appUrl ?>/kvkk" class="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 <?= $currentKey === 'kvkk' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>KVKK Aydınlatma Metni</span>
                </a>
                <a href="<?= $appUrl ?>/iletisim" class="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 <?= $currentKey === 'contact' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    <i class="fa-solid fa-headset"></i>
                    <span>İletişim Bilgileri</span>
                </a>
            </div>

            <!-- Content Card -->
            <article class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200/80">
                <header class="border-b border-slate-100 pb-6 mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 mb-2">
                                <i class="fa-solid fa-circle-check text-indigo-500"></i>
                                <span>Resmi Yasal Bilgilendirme</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                <?= htmlspecialchars($pageTitle ?? '') ?>
                            </h1>
                        </div>
                        <div class="text-xs text-slate-400 flex items-center space-x-1.5">
                            <i class="fa-regular fa-clock"></i>
                            <span>Son Güncelleme: <?= date('d.m.Y') ?></span>
                        </div>
                    </div>
                </header>

                <div class="prose prose-slate max-w-none text-sm text-slate-700 leading-relaxed space-y-5">
                    <?= $pageContent ?? '' ?>
                </div>
            </article>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-8 text-xs text-slate-500 mt-auto">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div class="space-y-1">
                <div>
                    &copy; <?= date('Y') ?> <strong>TakvimTakip</strong>. Tüm hakları saklıdır.
                </div>
                <div>
                    <a href="https://www.trendvillas.net" target="_blank" rel="noopener" class="text-slate-700 hover:text-indigo-600 font-semibold transition">www.trendvillas.net</a> Güvencesiyle
                </div>
            </div>
            <div class="flex items-center space-x-4 text-slate-500">
                <span class="flex items-center"><i class="fa-solid fa-lock text-emerald-500 mr-1.5"></i>256-Bit SSL</span>
                <span class="flex items-center"><i class="fa-solid fa-shield-halved text-indigo-500 mr-1.5"></i>KVKK & Apple Uyumlu</span>
            </div>
        </div>
    </footer>

</body>
</html>
