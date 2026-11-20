<?php
declare(strict_types=1);
/**
 * views/legal/layout.php
 * Institutional & Legal Layout (Gizlilik, Kullanım, KVKK, İletişim)
 * Harmonized with Landing Page Header, Footer, and Floating WhatsApp
 */
use App\Auth;

$currentKey = $legalPageKey ?? 'privacy';
$appUrl = defined('APP_URL') ? APP_URL : '';
$isLoggedIn = Auth::check();
?>
<!DOCTYPE html>
<html lang="tr" class="h-full scroll-smooth bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle ?? 'Yasal Bilgilendirme') ?> - TakvimTakip</title>
    <link rel="icon" type="image/png" sizes="192x192" href="<?= $appUrl ?>/css/icon-192.png?v=7">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $appUrl ?>/css/apple-touch-icon.png?v=7">
    <meta name="theme-color" content="#4338ca">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-white text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- =========================================================================
         1. STICKY NAVBAR (Landing Page Altyapısıyla Birebir Uyumlu)
         ========================================================================= -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-[#4338ca] border-b border-indigo-900/40 shadow-md text-white transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="<?= $appUrl ?>/" class="flex items-center space-x-3 group">
                <img src="<?= $appUrl ?>/uploads/logo.png" alt="TakvimTakip" class="h-10 w-auto object-contain max-h-12" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                <div class="flex items-center space-x-3" style="display:none;">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-200">
                        <i class="fa-solid fa-calendar-days text-white text-lg"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-extrabold text-white tracking-tight">TakvimTakip</span>
                        <span class="text-[10px] text-indigo-200 font-medium tracking-wide">Villa & Bungalov Takvimi</span>
                    </div>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center space-x-6 xl:space-x-8 text-xs font-semibold">
                <a href="<?= $appUrl ?>/#how-it-works" class="text-slate-100 hover:text-amber-300 transition-colors">Nasıl Çalışır?</a>
                <a href="<?= $appUrl ?>/#features" class="text-slate-100 hover:text-amber-300 transition-colors">Özellikler</a>
                <a href="<?= $appUrl ?>/#uygulama" class="text-[#FED452] hover:text-yellow-300 font-bold transition-colors">Mobil Uygulama</a>
                <a href="<?= $appUrl ?>/#ical-sync" class="text-slate-100 hover:text-amber-300 transition-colors">iCal Entegrasyonu</a>
                <a href="<?= $appUrl ?>/#pricing" class="text-slate-100 hover:text-amber-300 transition-colors">Fiyatlandırma</a>
                <a href="<?= $appUrl ?>/#faq" class="text-slate-100 hover:text-amber-300 transition-colors">SSS</a>
                <a href="<?= $appUrl ?>/iletisim" class="<?= $currentKey === 'contact' ? 'text-amber-300 font-bold' : 'text-slate-100 hover:text-amber-300' ?> transition-colors">İletişim</a>
            </nav>

            <!-- Action CTAs -->
            <div class="hidden sm:flex items-center space-x-3">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $appUrl ?>/dashboard" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition active:scale-95 flex items-center space-x-2">
                        <i class="fa-solid fa-gauge-high text-xs"></i>
                        <span>Dashboard'a Git</span>
                    </a>
                <?php else: ?>
                    <a href="<?= $appUrl ?>/login" class="px-4 py-2.5 text-slate-100 hover:text-amber-300 font-bold text-xs transition">
                        Giriş Yap
                    </a>
                    <a href="<?= $appUrl ?>/register" class="px-5 py-2.5 bg-white hover:bg-slate-100 text-[#4033c6] font-extrabold text-xs rounded-xl shadow-md transition active:scale-95 flex items-center space-x-1.5">
                        <span>Ücretsiz Başla</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="md:hidden flex items-center space-x-2">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $appUrl ?>/dashboard" class="px-3.5 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl">
                        Panel
                    </a>
                <?php endif; ?>
                <button onclick="toggleMobileMenu()" class="w-10 h-10 rounded-xl bg-indigo-800 text-white hover:bg-indigo-900 flex items-center justify-center focus:outline-none">
                    <i class="fa-solid fa-bars text-lg" id="hamburgerIcon"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-[#4338ca] border-b border-indigo-900 text-white px-4 pt-3 pb-6 space-y-3">
            <a href="<?= $appUrl ?>/#how-it-works" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-slate-100 hover:text-amber-300">Nasıl Çalışır?</a>
            <a href="<?= $appUrl ?>/#features" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-slate-100 hover:text-amber-300">Özellikler</a>
            <a href="<?= $appUrl ?>/#uygulama" onclick="toggleMobileMenu()" class="block py-2 text-sm font-bold text-[#FED452] hover:text-yellow-300 transition-colors">Mobil Uygulama</a>
            <a href="<?= $appUrl ?>/#ical-sync" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-slate-100 hover:text-amber-300">iCal Entegrasyonu</a>
            <a href="<?= $appUrl ?>/#pricing" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-slate-100 hover:text-amber-300">Fiyatlandırma</a>
            <a href="<?= $appUrl ?>/#faq" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold text-slate-100 hover:text-amber-300">SSS</a>
            <a href="<?= $appUrl ?>/iletisim" onclick="toggleMobileMenu()" class="block py-2 text-sm font-bold text-amber-300">İletişim</a>
            <div class="pt-3 border-t border-indigo-900/50 flex flex-col space-y-2">
                <?php if (!$isLoggedIn): ?>
                    <a href="<?= $appUrl ?>/login" class="w-full py-2.5 text-center bg-indigo-800 text-white font-bold text-xs rounded-xl">Giriş Yap</a>
                    <a href="<?= $appUrl ?>/register" class="w-full py-2.5 text-center bg-white text-[#4033c6] font-extrabold text-xs rounded-xl shadow-md">Ücretsiz Başla</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- =========================================================================
         2. MAIN CONTENT CONTAINER
         ========================================================================= -->
    <main class="flex-grow pt-28 pb-12 sm:pt-36 sm:pb-16 bg-slate-50/70 border-b border-slate-200/80">
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
                                <span>Resmi Kurumsal & İletişim Bilgileri</span>
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

    <!-- =========================================================================
         3. FOOTER (Landing Page ile Birebir Aynı Tasarım & İçerik)
         ========================================================================= -->
    <footer class="bg-slate-50/90 border-t border-slate-200/80 text-slate-600 pt-16 pb-12 text-xs mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- Column 1: Brand Info -->
                <div class="space-y-4 md:col-span-2">
                    <div class="flex items-center space-x-3">
                        <img src="<?= $appUrl ?>/uploads/logo.png" alt="TakvimTakip" class="h-9 w-auto object-contain" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                        <div class="flex items-center space-x-3" style="display:none;">
                            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold shadow-sm">
                                <i class="fa-solid fa-calendar-days text-sm"></i>
                            </div>
                            <span class="text-base font-extrabold text-slate-900 tracking-tight">TakvimTakip</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 max-w-sm leading-relaxed">
                        Villa, bungalov ve kısa dönem konaklama işletmecileri için pratik rezervasyon takvimi ve müsaitlik yönetim platformu.
                    </p>
                    <div class="pt-1 flex items-center space-x-3 text-xs">
                        <a href="tel:05437161546" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg font-bold hover:bg-emerald-100 transition">
                            <i class="fa-solid fa-phone text-emerald-600"></i>
                            <span>İrtibat: 0543 716 15 46</span>
                        </a>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Hızlı Bağlantılar</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="<?= $appUrl ?>/#how-it-works" class="text-slate-600 hover:text-indigo-600 transition">Nasıl Çalışır?</a></li>
                        <li><a href="<?= $appUrl ?>/#interactive-showcase" class="text-slate-600 hover:text-indigo-600 transition">Canlı Simülasyon</a></li>
                        <li><a href="<?= $appUrl ?>/#features" class="text-slate-600 hover:text-indigo-600 transition">Özellikler</a></li>
                        <li><a href="<?= $appUrl ?>/#uygulama" class="text-slate-600 hover:text-indigo-600 transition">Mobil Uygulama</a></li>
                        <li><a href="<?= $appUrl ?>/#ical-sync" class="text-slate-600 hover:text-indigo-600 transition">iCal Senkronizasyonu</a></li>
                        <li><a href="<?= $appUrl ?>/#pricing" class="text-slate-600 hover:text-indigo-600 transition">Fiyatlandırma</a></li>
                        <li><a href="<?= $appUrl ?>/iletisim" class="text-slate-600 hover:text-indigo-600 transition">İletişim</a></li>
                    </ul>
                </div>

                <!-- Column 3: Legal & Support -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Kurumsal & Yasal</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="<?= $appUrl ?>/iletisim" class="text-slate-600 hover:text-indigo-600 transition block text-left">İletişim Bilgileri</a></li>
                        <li><a href="<?= $appUrl ?>/gizlilik" class="text-slate-600 hover:text-indigo-600 transition block text-left">Gizlilik Sözleşmesi</a></li>
                        <li><a href="<?= $appUrl ?>/kullanim-kosullari" class="text-slate-600 hover:text-indigo-600 transition block text-left">Kullanım Koşulları</a></li>
                        <li><a href="<?= $appUrl ?>/kvkk" class="text-slate-600 hover:text-indigo-600 transition block text-left">KVKK Aydınlatma Metni</a></li>
                        <li><a href="<?= $appUrl ?>/login" class="text-slate-600 hover:text-indigo-600 transition block">Giriş Yap</a></li>
                    </ul>
                </div>

            </div>

            <div class="border-t border-slate-200/80 pt-8 flex flex-col sm:flex-row items-center justify-between text-slate-500 text-[11px] gap-4 text-center sm:text-left">
                <div class="space-y-1">
                    <div>
                        &copy; <?= date('Y') ?> TakvimTakip. Tüm hakları saklıdır. Villa & Bungalov <span class="whitespace-nowrap">Takvim Yazılımı.</span>
                    </div>
                    <div class="text-center sm:text-left pt-0.5">
                        <a href="https://www.trendvillas.net" target="_blank" rel="noopener" class="text-slate-700 hover:text-indigo-600 transition font-semibold">www.trendvillas.net</a> Güvencesiyle
                    </div>
                </div>
                <div class="flex items-center justify-center space-x-4 shrink-0">
                    <span><i class="fa-solid fa-lock text-emerald-500 mr-1"></i>SSL Korumalı</span>
                    <span><i class="fa-solid fa-shield-halved text-indigo-500 mr-1"></i>Güvenli Altyapı</span>
                </div>
            </div>

        </div>
    </footer>

    <!-- =========================================================================
         4. FLOATING WHATSAPP BUTTON (Sabit Kayan WP Butonu)
         ========================================================================= -->
    <a href="https://wa.me/902422120174?text=Merhaba,%20TakvimTakip%20hakk%C4%B1nda%20bilgi%20almak%20istiyorum." 
       target="_blank" 
       rel="noopener noreferrer" 
       class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-2xl shadow-emerald-500/40 hover:scale-110 active:scale-95 transition-all duration-300 group"
       title="WhatsApp ile İletişime Geçin">
        <i class="fa-brands fa-whatsapp text-3xl"></i>
        <span class="absolute right-16 bg-slate-900 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
            WhatsApp'tan Yazın
        </span>
    </a>

    <!-- Mobile Menu Toggle Script -->
    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const icon = document.getElementById('hamburgerIcon');
        if (menu) {
            menu.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-xmark');
            }
        }
    }
    </script>

    <!-- Start of Tawk.to Script -->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/6aa19acd573c093446d431d0/1k23k9dcp';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!-- End of Tawk.to Script -->

</body>
</html>
