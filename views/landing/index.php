<?php
use App\Helpers;
use App\Auth;

$isLoggedIn = Auth::check();
$dashboardUrl = APP_URL . '/dashboard';
$loginUrl = APP_URL . '/login';
$registerUrl = APP_URL . '/register';

/**
 * LANDING PAGE TEMA AYARI (THEME CONFIGURATION)
 * - 'light' : Modern ve ferah açık tema (Varsayılan)
 * - 'dark'  : Koyu / Cam efektli tema
 * 
 * İleride tekrar dark temaya geçmek isterseniz aşağıdaki $landingTheme değişkenini 'dark' yapmanız yeterlidir.
 * Tüm dark tema kod yapısı ve renk paletleri $T haritasında ve CSS bloklarında korunmuştur.
 */
$landingTheme = 'light'; // Seçenekler: 'light' | 'dark'
$isDark = ($landingTheme === 'dark');

// Dinamik Tema Sınıf Haritası (Dark tema kodlarını tam korur)
$T = [
    'html'            => $isDark ? 'bg-slate-900' : 'bg-slate-50',
    'body'            => $isDark ? 'bg-slate-950 text-slate-100' : 'bg-white text-slate-800',
    
    // Navbar
    'nav_bg'          => $isDark ? 'glass-nav border-b border-slate-800/80' : 'bg-[#4338ca] border-b border-indigo-900/40 shadow-md text-white',
    'nav_brand_title' => $isDark ? 'text-white' : 'text-white',
    'nav_brand_sub'   => $isDark ? 'text-slate-400' : 'text-indigo-200',
    'nav_link'        => $isDark ? 'text-slate-300 hover:text-white' : 'text-slate-100 hover:text-amber-300',
    'nav_login'       => $isDark ? 'text-slate-300 hover:text-white' : 'text-slate-100 hover:text-amber-300',
    'mobile_btn'      => $isDark ? 'bg-slate-800 text-slate-300 hover:text-white' : 'bg-indigo-800 text-white hover:bg-indigo-900',
    'mobile_menu'     => $isDark ? 'bg-slate-900/95 border-b border-slate-800' : 'bg-[#4338ca] border-b border-indigo-900 text-white',

    // Hero Section
    'hero_glow_1'     => $isDark ? 'bg-indigo-600/15' : 'bg-indigo-500/10',
    'hero_glow_2'     => $isDark ? 'bg-emerald-500/10' : 'bg-emerald-500/10',
    'hero_eyebrow'    => $isDark ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-300' : 'bg-indigo-50 border-indigo-200 text-indigo-700',
    'hero_title'      => $isDark ? 'text-white' : 'text-slate-900',
    'hero_grad_text'  => $isDark ? 'from-indigo-400 via-indigo-300 to-emerald-400' : 'from-indigo-600 via-indigo-500 to-emerald-600',
    'hero_sub'        => $isDark ? 'text-slate-300' : 'text-slate-600',
    'hero_cta_sec'    => $isDark ? 'bg-slate-800/80 hover:bg-slate-800 text-slate-200 border-slate-700/80' : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-200',
    'hero_trust_text' => $isDark ? 'text-slate-400' : 'text-slate-500',

    // Section Backgrounds & Borders
    'sec_alt_bg'      => $isDark ? 'bg-slate-900 border-y border-slate-800/80' : 'bg-slate-50/70 border-y border-slate-200/80',
    'sec_main_bg'     => $isDark ? 'bg-slate-950' : 'bg-white',
    
    // Headings & Text Colors
    'tag_indigo'      => $isDark ? 'text-indigo-400' : 'text-indigo-600',
    'tag_rose'        => $isDark ? 'text-rose-400' : 'text-rose-600',
    'tag_emerald'     => $isDark ? 'text-emerald-400' : 'text-emerald-600',
    'heading'         => $isDark ? 'text-white' : 'text-slate-900',
    'body_muted'      => $isDark ? 'text-slate-400' : 'text-slate-600',
    'body_sub'        => $isDark ? 'text-slate-300' : 'text-slate-600',

    // Cards
    'card'            => $isDark ? 'card-glass' : 'card-glass-light',
    'sub_card'        => $isDark ? 'bg-slate-900 border-slate-800' : 'bg-slate-50 border-slate-200/80',
    'sub_card_alt'    => $isDark ? 'bg-slate-950 border-slate-800' : 'bg-white border-slate-200/80',

    // Problem vs Solution Section
    'problem_box'     => $isDark ? 'bg-rose-950/20 border-rose-900/30' : 'bg-rose-50/60 border-rose-200',
    'problem_header'  => $isDark ? 'border-rose-900/40 text-rose-400' : 'border-rose-200 text-rose-700',
    'problem_sub'     => $isDark ? 'text-rose-300/80' : 'text-rose-600',
    'problem_text'    => $isDark ? 'text-slate-300' : 'text-slate-700',

    'solution_box'    => $isDark ? 'bg-indigo-950/20 border-indigo-800/40' : 'bg-indigo-50/60 border-indigo-200',
    'solution_header' => $isDark ? 'border-indigo-900/40 text-indigo-400' : 'border-indigo-200 text-indigo-700',
    'solution_sub'    => $isDark ? 'text-indigo-300/80' : 'text-indigo-600',
    'solution_text'   => $isDark ? 'text-slate-300' : 'text-slate-700',

    // Pricing
    'plan_basic'      => $isDark ? 'bg-slate-950 border-slate-800' : 'bg-slate-50/80 border-slate-200/90',
    'plan_pro'        => $isDark ? 'card-glass border-2 border-indigo-500/80 shadow-2xl shadow-indigo-500/10' : 'bg-white border-2 border-indigo-600 shadow-xl shadow-indigo-600/10',

    // Accordion / FAQ
    'faq_item'        => $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200/90 shadow-sm',
    'faq_btn'         => $isDark ? 'text-white' : 'text-slate-900',
    'faq_content'     => $isDark ? 'text-slate-400 border-slate-800/60' : 'text-slate-600 border-slate-100',

    // Final CTA
    'cta_bg'          => $isDark ? 'bg-slate-900 border-t border-slate-800' : 'bg-gradient-to-br from-indigo-50 via-white to-slate-50 border-t border-slate-200/80',
    'cta_heading'     => $isDark ? 'text-white' : 'text-slate-900',
    'cta_sub'         => $isDark ? 'text-slate-300' : 'text-slate-600',
    'cta_glow'        => $isDark ? 'from-indigo-900/30 via-slate-900 to-emerald-900/20' : 'from-indigo-500/5 via-white to-emerald-500/5',

    // Footer & Modal
    'footer_bg'       => $isDark ? 'bg-slate-950 border-t border-slate-800/80 text-slate-400' : 'bg-slate-50/90 border-t border-slate-200/80 text-slate-600',
    'footer_title'    => $isDark ? 'text-white' : 'text-slate-900',
    'footer_link'     => $isDark ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-indigo-600',
    'footer_subtext'  => $isDark ? 'text-slate-400' : 'text-slate-500',
    'footer_border'   => $isDark ? 'border-slate-800/80' : 'border-slate-200/80',
    'modal_bg'        => $isDark ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-800',
];
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth <?= $T['html'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title>Villa & Bungalov Takvim Yönetimi | TakvimTakip</title>
    <meta name="title" content="Villa & Bungalov Takvim Yönetimi | TakvimTakip">
    <meta name="description" content="Villa, bungalov ve kısa dönem konaklama tesisleriniz için hızlı ve pratik takvim yönetim yazılımı. Dolu ve müsait tarihlerinizi saniyeler içinde açın, kapatın ve iCal ile senkronize edin.">
    <meta name="keywords" content="villa takvim yönetimi, bungalov takvimi, konaklama takvim yazılımı, müsaitlik takvimi, ical senkronizasyon, villa kiralama takvimi">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= APP_URL ?>">
    <meta property="og:title" content="Villa & Bungalov Takvim Yönetimi | TakvimTakip">
    <meta property="og:description" content="Villa ve bungalovlarınızın müsaitlik takvimini kolayca yönetin. Tarih aralıklarını saniyeler içinde açın, kapatın ve doluluklarınızı kontrol altında tutun.">
    <!-- Microsoft Clarity Analytics -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "yhoon3z6i5");
    </script>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Custom Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eep2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        },
                        reserved: '#fd5a5f',
                    },
                    animation: {
                        'pulse-subtle': 'pulseSubtle 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 4s ease-in-out infinite',
                    },
                    keyframes: {
                        pulseSubtle: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.85' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-6px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Fine-tuned Glassmorphism & Micro Styles (Preserves both Dark & Light Themes) */
        .glass-nav {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .glass-nav-light {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .hero-gradient {
            background: <?= $isDark ? 'radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.18) 0%, rgba(15, 23, 42, 0) 70%)' : 'radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.08) 0%, rgba(255, 255, 255, 0) 70%)' ?>;
        }
        .card-glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .card-glass-light {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05);
        }
        /* Custom Split Diagonal Gradient Preview */
        .gradient-checkin {
            background: linear-gradient(135deg, #ffffff 49%, #fd5a5f 51%) !important;
        }
        .gradient-checkout {
            background: linear-gradient(135deg, #fd5a5f 49%, #ffffff 51%) !important;
        }
        .gradient-both {
            background: linear-gradient(135deg, #fd5a5f calc(50% - 3px), #ffffff calc(50% - 3px), #ffffff calc(50% + 3px), #fd5a5f calc(50% + 3px)) !important;
        }
    </style>

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "TakvimTakip",
      "operatingSystem": "Web, Mobile",
      "applicationCategory": "BusinessApplication",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "TRY"
      },
      "description": "Villa ve bungalov işletmecileri için pratik rezervasyon takvimi ve müsaitlik yönetim yazılımı."
    }
    </script>
</head>
<body class="<?= $T['body'] ?> antialiased font-sans selection:bg-indigo-500 selection:text-white">

<!-- =========================================================================
     1. STICKY NAVBAR
     ========================================================================= -->
<header class="fixed top-0 left-0 right-0 z-50 <?= $T['nav_bg'] ?> transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        
        <!-- Brand Logo -->
        <a href="<?= APP_URL ?>" class="flex items-center space-x-3 group">
            <img src="<?= APP_URL ?>/uploads/logo.png" alt="TakvimTakip" class="h-10 w-auto object-contain max-h-12" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
            <div class="flex items-center space-x-3" style="display:none;">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-calendar-days text-white text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-extrabold <?= $T['nav_brand_title'] ?> tracking-tight">
                        TakvimTakip
                    </span>
                    <span class="text-[10px] <?= $T['nav_brand_sub'] ?> font-medium tracking-wide">Villa & Bungalov Takvimi</span>
                </div>
            </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-6 xl:space-x-8 text-xs font-semibold">
            <a href="#how-it-works" class="<?= $T['nav_link'] ?> transition-colors">Nasıl Çalışır?</a>
            <a href="#features" class="<?= $T['nav_link'] ?> transition-colors">Özellikler</a>
            <a href="#ical-sync" class="<?= $T['nav_link'] ?> transition-colors">iCal Entegrasyonu</a>
            <a href="#pricing" class="<?= $T['nav_link'] ?> transition-colors">Fiyatlandırma</a>
            <a href="#faq" class="<?= $T['nav_link'] ?> transition-colors">SSS</a>
            <a href="#contact" class="<?= $T['nav_link'] ?> transition-colors">İletişim</a>
        </nav>

        <!-- Action CTAs -->
        <div class="hidden sm:flex items-center space-x-3">
            <?php if ($isLoggedIn): ?>
                <a href="<?= $dashboardUrl ?>" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition active:scale-95 flex items-center space-x-2">
                    <i class="fa-solid fa-gauge-high text-xs"></i>
                    <span>Dashboard'a Git</span>
                </a>
            <?php else: ?>
                <a href="<?= $loginUrl ?>" class="px-4 py-2.5 <?= $T['nav_login'] ?> font-bold text-xs transition">
                    Giriş Yap
                </a>
                <a href="<?= $registerUrl ?>" class="px-5 py-2.5 bg-white hover:bg-slate-100 text-[#4033c6] font-extrabold text-xs rounded-xl shadow-md transition active:scale-95 flex items-center space-x-1.5">
                    <span>Ücretsiz Başla</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Hamburger Button -->
        <div class="md:hidden flex items-center space-x-2">
            <?php if ($isLoggedIn): ?>
                <a href="<?= $dashboardUrl ?>" class="px-3.5 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl">
                    Panel
                </a>
            <?php endif; ?>
            <button onclick="toggleMobileMenu()" class="w-10 h-10 rounded-xl <?= $T['mobile_btn'] ?> flex items-center justify-center focus:outline-none">
                <i class="fa-solid fa-bars text-lg" id="hamburgerIcon"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div id="mobileMenu" class="hidden md:hidden <?= $T['mobile_menu'] ?> px-4 pt-3 pb-6 space-y-3">
        <a href="#how-it-works" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold <?= $T['nav_link'] ?>">Nasıl Çalışır?</a>
        <a href="#features" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold <?= $T['nav_link'] ?>">Özellikler</a>
        <a href="#ical-sync" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold <?= $T['nav_link'] ?>">iCal Entegrasyonu</a>
        <a href="#pricing" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold <?= $T['nav_link'] ?>">Fiyatlandırma</a>
        <a href="#faq" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold <?= $T['nav_link'] ?>">SSS</a>
        <a href="#contact" onclick="toggleMobileMenu()" class="block py-2 text-sm font-semibold <?= $T['nav_link'] ?>">İletişim</a>
        <div class="pt-3 border-t <?= $isDark ? 'border-slate-800' : 'border-indigo-900/50' ?> flex flex-col space-y-2">
            <?php if (!$isLoggedIn): ?>
                <a href="<?= $loginUrl ?>" class="w-full py-2.5 text-center <?= $isDark ? 'bg-slate-800 text-slate-200' : 'bg-indigo-800 text-white' ?> font-bold text-xs rounded-xl">Giriş Yap</a>
                <a href="<?= $registerUrl ?>" class="w-full py-2.5 text-center bg-white text-[#4033c6] font-extrabold text-xs rounded-xl shadow-md">Ücretsiz Başla</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- =========================================================================
     2. HERO SECTION
     ========================================================================= -->
<section class="relative pt-32 pb-20 md:pt-40 md:pb-28 overflow-hidden hero-gradient">
    <!-- Ambient Background Glows -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] <?= $T['hero_glow_1'] ?> blur-[140px] rounded-full pointer-events-none"></div>
    <div class="absolute top-1/3 right-10 w-[350px] h-[350px] <?= $T['hero_glow_2'] ?> blur-[120px] rounded-full pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-4xl mx-auto text-center space-y-6">
            
            <!-- Target Audience Eyebrow Badge -->
            <div class="inline-flex items-center space-x-2 sm:space-x-2.5 px-4 sm:px-5 py-2 <?= $T['hero_eyebrow'] ?> rounded-full text-xs sm:text-sm md:text-base font-bold tracking-tight sm:tracking-wide whitespace-nowrap">
                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                <span class="whitespace-nowrap">Villa & Bungalov İşletmecileri İçin Özel Takvim Yazılımı</span>
            </div>

            <!-- Main Value H1 Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold <?= $T['hero_title'] ?> tracking-tight leading-[1.15]">
                Excel, WhatsApp ve Defter <br class="hidden sm:inline">Karmaşasına Son
            </h1>

            <!-- Subheadline -->
            <p class="text-base sm:text-xl <?= $T['hero_sub'] ?> max-w-3xl mx-auto leading-relaxed">
                Villa ve bungalovlarınızın doluluk takvimini tek ekrandan yönetin. Tarihlerinizi saniyeler içinde açın, kapatın ve çifte rezervasyon riskini sıfırlayın.
            </p>
            <p class="text-sm sm:text-base font-bold text-indigo-600 tracking-wide">
                Çok daha fazlası TakvimTakip'te...
            </p>

            <!-- Hero CTAs -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="<?= $isLoggedIn ? $dashboardUrl : $registerUrl ?>" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-sm rounded-2xl shadow-xl shadow-indigo-600/35 transition active:scale-95 flex items-center justify-center space-x-2 group">
                    <span><?= $isLoggedIn ? 'Panelinize Gidin' : '7 Gün Ücretsiz Dene' ?></span>
                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
                
                <a href="#interactive-showcase" class="w-full sm:w-auto px-6 py-4 <?= $T['hero_cta_sec'] ?> font-bold text-sm rounded-2xl border transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-circle-play text-indigo-500 text-xs"></i>
                    <span>Canlı Simülasyonu Gör</span>
                </a>
            </div>

            <!-- Trust Micro-Badges -->
            <div class="pt-6 flex flex-wrap items-center justify-center gap-8 text-xs <?= $T['hero_trust_text'] ?> font-semibold">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-check text-emerald-500"></i>
                    <span>Kurulum Gerektirmez</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-mobile-screen-button text-emerald-500"></i>
                    <span>Mobil & Masaüstü Uyumlu</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-emerald-500"></i>
                    <span>Kullanıcı Dostu Arayüz</span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     3. ANA DEĞER ÖNERİSİ (CORE VALUE PROPOSITION - 3 CARDS)
     ========================================================================= -->
<section class="py-20 <?= $T['sec_alt_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_indigo'] ?>">Neden TakvimTakip?</h2>
            <p class="text-2xl sm:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                Rezervasyon Takviminizi Yönetmenin En Kolay Yolu.
            </p>
            <p class="text-sm <?= $T['body_muted'] ?>">
                Karmaşık yazılımlara veya defterlere ihtiyacınız yok. İhtiyacınız olan her şey tek bir sade takvim üzerinde.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Card 1 -->
            <div class="<?= $T['card'] ?> p-8 rounded-3xl space-y-4 hover:border-indigo-500/50 transition-all duration-300 group">
                <div class="w-14 h-14 rounded-2xl <?= $isDark ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-indigo-50 text-indigo-600 border-indigo-100' ?> border flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <div class="text-xs font-bold text-indigo-600 tracking-wider">01 — ANINDA GÖRÜNTÜLEME</div>
                <h3 class="text-xl font-bold <?= $T['heading'] ?>">Takvim Üzerinde Müsaitliği Anında Görün</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Hangi tarihlerin dolu, hangi tarihlerin boş olduğunu takvim üzerinde tek bakışta görün.
                </p>
            </div>

            <!-- Card 2 -->
            <div class="<?= $T['card'] ?> p-8 rounded-3xl space-y-4 hover:border-rose-500/50 transition-all duration-300 group">
                <div class="w-14 h-14 rounded-2xl <?= $isDark ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-rose-50 text-rose-600 border-rose-100' ?> border flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <div class="text-xs font-bold text-rose-600 tracking-wider">02 — HIZLI TARİH KAPATMA</div>
                <h3 class="text-xl font-bold <?= $T['heading'] ?>">Tarih Aralığını Saniyeler İçinde Kapatın</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Günlük işlerinizi yaparken istediğiniz tarih aralıklarını kolayca açıp kapatın.
                </p>
            </div>

            <!-- Card 3 -->
            <div class="<?= $T['card'] ?> p-8 rounded-3xl space-y-4 hover:border-emerald-500/50 transition-all duration-300 group">
                <div class="w-14 h-14 rounded-2xl <?= $isDark ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-emerald-50 text-emerald-600 border-emerald-100' ?> border flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="text-xs font-bold text-emerald-600 tracking-wider">03 — YAKLAŞAN REZERVASYONLAR</div>
                <h3 class="text-xl font-bold <?= $T['heading'] ?>">Yaklaşan Rezervasyonlarınızı Görün</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Önümüzdeki&nbsp;&nbsp;7 gün içinde gerçekleşecek tüm rezervasyonları tek bakışta görün.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     4. PROBLEMI ANLAT (BEFORE / AFTER COMPARISON)
     ========================================================================= -->
<section class="py-20 <?= $T['sec_main_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-5xl mx-auto space-y-3 mb-16">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_rose'] ?>">GERÇEK İŞLETME PROBLEMLERİ</h2>
            <p class="text-xl sm:text-2xl md:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight whitespace-normal sm:whitespace-nowrap">
                Takvim Takibinde Yaşanan Günlük Karmaşaya Son Verin.
            </p>
            <p class="text-sm <?= $T['body_muted'] ?>">
                Geleneksel yöntemlerle takvim takip etmek zaman kaybı ve çifte rezervasyon riski yaratır.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
            
            <!-- Problem Column (Eski & Karmaşık Yöntemler) -->
            <div class="<?= $T['problem_box'] ?> border p-8 rounded-3xl space-y-6">
                <div class="flex items-center space-x-3 <?= $T['problem_header'] ?> border-b pb-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold <?= $T['heading'] ?> text-base">Eski & Zahmetli Yöntemler</h3>
                        <p class="text-xs <?= $T['problem_sub'] ?>">Kâğıt, mesajlar ve karışık tablolar</p>
                    </div>
                </div>

                <ul class="space-y-4 text-xs sm:text-sm <?= $T['problem_text'] ?>">
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-xmark text-rose-500 text-base shrink-0 mt-0.5"></i>
                        <span>Müşteri aradığında boş tarihleri hatırlayamamak ve vakit kaybetmek.</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-xmark text-rose-500 text-base shrink-0 mt-0.5"></i>
                        <span>Kapatılması gereken tarihleri unutup çifte rezervasyon krizi yaşamak.</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-xmark text-rose-500 text-base shrink-0 mt-0.5"></i>
                        <span>WhatsApp mesajları, ajanda ve defter notları arasında takvimi kaybetmek.</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-xmark text-rose-500 text-base shrink-0 mt-0.5"></i>
                        <span>Acentelere veya müşterilere tek tek manuel doluluk bilgisi yazmak.</span>
                    </li>
                </ul>
            </div>

            <!-- Solution Column (TakvimTakip İle Kolaylık) -->
            <div class="<?= $T['solution_box'] ?> border p-8 rounded-3xl space-y-6">
                <div class="flex items-center space-x-3 <?= $T['solution_header'] ?> border-b pb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-check text-lg text-emerald-500"></i>
                    </div>
                    <div>
                        <h3 class="font-bold <?= $T['heading'] ?> text-base">TakvimTakip İle Kolay Yönetim</h3>
                        <p class="text-xs <?= $T['solution_sub'] ?>">Saniyeler içinde kontrol sizde</p>
                    </div>
                </div>

                <ul class="space-y-4 text-xs sm:text-sm <?= $T['solution_text'] ?>">
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0 mt-0.5"></i>
                        <span>Müşteri telefondayken boş tarihleri <strong>anında görüp bilgi verin.</strong></span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0 mt-0.5"></i>
                        <span>Dolu tarihleri tek dokunuşla kapatarak <strong>çifte rezervasyon riskini sıfırlayın.</strong></span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0 mt-0.5"></i>
                        <span>Tüm dolulukları tek ve düzenli ekranda bir arada kontrol edin.</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0 mt-0.5"></i>
                        <span>Müşterilerinize ve acentelerinize <strong>tek tıkla canlı takvim linki</strong> gönderin.</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     5. TAKVİM ODAKLI PRODUCT SHOWCASE (INTERACTIVE DEMO SECTION)
     ========================================================================= -->
<section id="interactive-showcase" class="py-20 <?= $T['sec_alt_bg'] ?> relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-12">
            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 rounded-full text-xs font-bold">
                <i class="fa-solid fa-toggle-on text-xs"></i>
                <span>ANINDA TARİH AÇ / KAPAT</span>
            </div>
            <h2 class="text-2xl sm:text-4xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                Takviminizi Tek Bakışta Görün.
            </h2>
            <p class="text-sm <?= $T['body_sub'] ?>">
                Müsait ve kapalı tarihleri kolayca ayırt edin. Değişiklikleri doğrudan takvim üzerinden saniyeler içinde gerçekleştirin.
            </p>
        </div>

        <!-- Minimal Interactive Calendar Mockup Container -->
        <div class="max-w-2xl mx-auto bg-white p-5 sm:p-7 rounded-3xl text-slate-800 shadow-2xl space-y-4 relative border border-slate-200/80">
            
            <!-- Calendar Top Bar -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-800">Canlı Simülasyonu Test Et</h3>
                </div>
                <div class="flex items-center space-x-3 text-xs font-semibold">
                    <div class="hidden sm:flex items-center space-x-3 text-[11px] text-slate-500">
                        <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-full bg-white border border-slate-300"></span><span>Müsait</span></span>
                        <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-full bg-[#fd5a5f]"></span><span class="font-bold text-rose-600">Dolu</span></span>
                    </div>
                    <button onclick="demoResetCalendar()" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        <span>Sıfırla</span>
                    </button>
                </div>
            </div>

            <!-- Showcase Step Progress Bar -->
            <div id="showcaseStepBar" class="p-2.5 rounded-xl bg-indigo-50/80 border border-indigo-100 text-xs font-semibold text-indigo-900 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span id="showcaseStepBadge" class="w-5 h-5 rounded-full bg-indigo-600 text-white font-extrabold text-[11px] flex items-center justify-center">1</span>
                    <span id="showcaseStepText"><b>1. Adım:</b> Başlangıç (Giriş) tarihine tıklayın <span class="text-indigo-600 font-bold">(Örn: 15 Temmuz)</span></span>
                </div>
                <span class="text-[10px] text-indigo-600 font-bold hidden sm:inline">Takvimden Seç</span>
            </div>

            <!-- Days of Week Header -->
            <div class="grid grid-cols-7 gap-1.5 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                <div>Pzt</div><div>Sal</div><div>Çar</div><div>Per</div><div>Cum</div><div>Cmt</div><div>Paz</div>
            </div>

            <!-- Showcase Grid Cells -->
            <div class="grid grid-cols-7 gap-1.5" id="showcaseGrid">
                <!-- Dynamic demo dates generated via JS -->
            </div>

            <!-- Minimal Action Feedback Console -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <div class="font-semibold text-slate-600 flex items-center space-x-2">
                    <i class="fa-solid fa-circle-info text-indigo-600 text-sm"></i>
                    <span id="showcaseStatusText">Takvim üzerinden başlangıç ve bitiş tarihlerini seçerek form ekranını açabilirsiniz.</span>
                </div>
            </div>

            <!-- SHOWCASE MODAL OVERLAY -->
            <div id="showcaseModalOverlay" class="hidden absolute inset-0 bg-slate-900/60 backdrop-blur-sm z-30 flex items-center justify-center p-3 sm:p-4 transition-all rounded-3xl">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-100 space-y-4 text-slate-800">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-[11px] font-bold mb-1">
                                <i class="fa-solid fa-house text-[10px]"></i>
                                <span>Villa Lighthouse 4</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-800" id="showcaseModalTitle">Yeni Tarih Aralığı İşlemi</h3>
                            <div id="showcaseModalDateRangeText" class="text-xs sm:text-sm font-bold text-indigo-600 mt-0.5">23.09.2026 &nbsp;➔&nbsp; 25.09.2026</div>
                        </div>
                        <button onclick="closeShowcaseModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:bg-slate-200 flex items-center justify-center text-sm font-bold transition">&times;</button>
                    </div>

                    <!-- Action Selector Radio Buttons -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">İşlem Türü</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button type="button" onclick="setShowcaseModalStatus('agency')" id="showcaseStatusAgencyBtn" class="py-2.5 px-1.5 sm:px-2 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-indigo-600 bg-indigo-50 text-indigo-700 flex items-center justify-center space-x-1 sm:space-x-1.5 transition min-w-0">
                                <i class="fa-solid fa-briefcase text-indigo-600 shrink-0"></i>
                                <span class="truncate">Acente Rezervasyonu</span>
                            </button>
                            <button type="button" onclick="setShowcaseModalStatus('booked')" id="showcaseStatusBookedBtn" class="py-2.5 px-1.5 sm:px-2 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-slate-200 text-slate-600 flex items-center justify-center space-x-1 sm:space-x-1.5 transition min-w-0">
                                <i class="fa-solid fa-user-shield text-rose-500 shrink-0"></i>
                                <span class="truncate">Ev Sahibi Rezervasyonu</span>
                            </button>
                        </div>
                    </div>

                    <!-- Input Fields: Agency Mode (Matches Screenshot) -->
                    <div id="showcaseAgencyBox" class="space-y-3 pt-1 text-xs">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Acente Adı">Acente Adı</label>
                                <input type="text" id="showcaseAgencyName" placeholder="Örn: TUI, Mavitur" class="w-full px-2.5 sm:px-3 py-2 rounded-xl border border-indigo-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50/40">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Rezervasyon Alınma Tarihi">Rez. Alınma Tarihi</label>
                                <input type="text" id="showcaseAgencyBookingDate" value="17.09.2026" readonly class="w-full px-2 sm:px-3 py-2 rounded-xl border border-indigo-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50/40">
                            </div>
                        </div>
                    </div>

                    <!-- Input Fields: Owner Mode (Matches Screenshot) -->
                    <div id="showcaseOwnerBox" class="hidden space-y-3 pt-1 text-xs">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Müşteri Ad Soyad</label>
                                <input type="text" placeholder="Ahmet Bey" class="w-full px-2.5 sm:px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Telefon Numarası</label>
                                <input type="tel" placeholder="0532 000 00 00" class="w-full px-2.5 sm:px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Rez. Alınma Tarihi</label>
                                <input type="text" id="showcaseOwnerBookingDate" value="17.09.2026" readonly class="w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Rezervasyon Kaynağı</label>
                                <select class="w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option selected>Eski Misafir</option>
                                    <option>Sosyal Medya</option>
                                    <option>Web Sitem</option>
                                    <option>Tavsiye</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 sm:gap-3">
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Toplam Fiyat</label>
                                <input type="text" id="showcaseOwnerPrice" oninput="handleShowcaseMoneyInput(this)" placeholder="Örn: 100.000 ₺" class="w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Ön Ödeme</label>
                                <input type="text" id="showcaseOwnerDownPayment" oninput="handleShowcaseMoneyInput(this)" placeholder="Örn: 20.000 ₺" class="w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Kalan Ödeme</label>
                                <input type="text" id="showcaseOwnerRemainingPayment" readonly placeholder="Örn: 80.000 ₺" class="w-full px-2 sm:px-3 py-2 rounded-xl border border-emerald-300 text-xs font-bold text-emerald-700 outline-none focus:ring-2 focus:ring-indigo-500 bg-emerald-50/40">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1">Not / Özel İstekler</label>
                            <textarea rows="2" placeholder="Örn: 2 çocuklu aile, bebek yatağı eklenecek." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-white resize-y"></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex items-center space-x-2">
                        <button onclick="submitShowcaseModalForm()" id="showcaseModalSubmitBtn" class="flex-grow py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg transition active:scale-[0.99] flex items-center justify-center">
                            <span>Rezervasyonu Kaydet</span>
                        </button>
                        <button type="button" onclick="closeShowcaseModal()" class="py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm rounded-xl transition shrink-0">
                            İptal
                        </button>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

<!-- =========================================================================
     6. iCal & PLATFORM ENTEGRASYONLARI
     ========================================================================= -->
<section id="ical-sync" class="py-20 <?= $T['sec_main_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-stretch">
            
            <!-- Left Text Content -->
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-600 text-xs font-bold tracking-wide">
                    <i class="fa-solid fa-rotate text-xs"></i>
                    <span>ÇİFT YÖNLÜ iCAL SENKRONİZASYONU</span>
                </div>

                <h2 class="text-2xl sm:text-4xl font-extrabold <?= $T['heading'] ?> tracking-tight leading-tight">
                    Airbnb, Booking.com ve Acentelerinizle Otomatik Senkronizasyon.
                </h2>

                <p class="text-sm sm:text-base <?= $T['body_sub'] ?> leading-relaxed">
                    Dış platformlardan aldığınız takvim bağlantılarını (iCal / .ics) sisteme ekleyin. Doluluklar takviminize otomatik yansısın. Villanıza özel ürettiğimiz korumalı dış takvim linkini de acentelerinize güvenle verin.
                </p>

                <div class="space-y-4 pt-2">
                    
                    <div class="flex items-start space-x-3.5 p-4 rounded-2xl <?= $T['sub_card'] ?>">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                            <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold <?= $T['heading'] ?> text-sm">iCal İçe Aktarma (Import)</h3>
                            <p class="text-xs <?= $T['body_muted'] ?> mt-0.5">Airbnb, Booking.com veya acentenizin iCal linkini ekleyin, dolu tarihler takviminize otomatik işlensin.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3.5 p-4 rounded-2xl <?= $T['sub_card'] ?>">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0 font-bold">
                            <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold <?= $T['heading'] ?> text-sm">Güvenli iCal Dışa Aktarma (Export)</h3>
                            <p class="text-xs <?= $T['body_muted'] ?> mt-0.5">Kendi web siteniz veya acenteleriniz için özel `.ics` bağlantısı üretin. Müşteri kişisel bilgileri (ad, telefon, fiyat) kesinklikle gizli tutulur.</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Visual Component Mockup -->
            <div class="lg:col-span-6 flex">
                <div class="<?= $T['card'] ?> p-6 sm:p-8 rounded-3xl w-full flex flex-col justify-between space-y-4">
                    
                    <div class="flex items-center justify-between border-b <?= $isDark ? 'border-slate-800' : 'border-slate-100' ?> pb-4 gap-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <i class="fa-solid fa-link text-indigo-600 text-xl shrink-0"></i>
                            <div class="min-w-0">
                                <h4 class="font-bold <?= $T['heading'] ?> text-sm truncate">Takvim Bağlantıları</h4>
                                <p class="text-xs <?= $T['body_muted'] ?> truncate">Aktif Takvim Senkronizasyonları</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 text-xs font-bold rounded-lg whitespace-nowrap shrink-0">Otomatik Eşitleme</span>
                    </div>

                    <!-- Integration Item 1 -->
                    <div class="p-4 rounded-2xl <?= $T['sub_card'] ?> flex items-center justify-between text-xs gap-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="px-2 py-1 rounded bg-rose-500/10 text-rose-600 font-bold text-[10px] uppercase shrink-0">Airbnb</span>
                            <div class="min-w-0">
                                <div class="font-bold <?= $T['heading'] ?> truncate">Airbnb Takvim Bağlantısı</div>
                                <div class="text-[11px] <?= $T['body_muted'] ?> font-mono truncate max-w-[130px] xs:max-w-[180px] sm:max-w-xs">https://www.airbnb.com/calendar/ical/...</div>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-emerald-500/10 text-emerald-600 font-bold text-[10px] rounded whitespace-nowrap shrink-0">Başarılı</span>
                    </div>

                    <!-- Integration Item 2 -->
                    <div class="p-4 rounded-2xl <?= $T['sub_card'] ?> flex items-center justify-between text-xs gap-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="px-2 py-1 rounded bg-blue-500/10 text-blue-600 font-bold text-[10px] uppercase shrink-0">Booking</span>
                            <div class="min-w-0">
                                <div class="font-bold <?= $T['heading'] ?> truncate">Booking.com Takvim Bağlantısı</div>
                                <div class="text-[11px] <?= $T['body_muted'] ?> font-mono truncate max-w-[130px] xs:max-w-[180px] sm:max-w-xs">https://admin.booking.com/hotel/ical/...</div>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-emerald-500/10 text-emerald-600 font-bold text-[10px] rounded whitespace-nowrap shrink-0">Başarılı</span>
                    </div>

                    <!-- Export Link Item -->
                    <div class="p-4 rounded-2xl <?= $isDark ? 'bg-indigo-950/30 border border-indigo-800/40' : 'bg-indigo-50/80 border border-indigo-200' ?> text-xs space-y-2">
                        <div class="flex items-center justify-between font-bold <?= $isDark ? 'text-indigo-300' : 'text-indigo-700' ?>">
                            <span>Sizin Özel Export Linkiniz</span>
                            <i class="fa-regular fa-copy text-indigo-600 cursor-pointer"></i>
                        </div>
                        <div class="p-2 <?= $isDark ? 'bg-slate-950 border-slate-800' : 'bg-white border-slate-200' ?> rounded-xl border font-mono text-[11px] <?= $T['body_muted'] ?> truncate">
                            <?= APP_URL ?>/api/calendar/v_lighthouse3_export.ics
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     7. MOBİL DENEYİM (TAKVİMİNİZ CEBİNİZDE)
     ========================================================================= -->
<section class="py-20 <?= $T['sec_alt_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Phone Screen Visual -->
            <div class="lg:col-span-5 flex justify-center order-2 lg:order-1">
                <div class="w-full max-w-[290px] bg-slate-900 p-4 rounded-[40px] border-4 border-slate-800 shadow-2xl space-y-4 relative">
                    
                    <!-- Phone Notch -->
                    <div class="w-28 h-4 bg-slate-800 rounded-b-xl mx-auto"></div>

                    <!-- Phone Screen Content -->
                    <div class="bg-white rounded-2xl p-3 text-slate-800 space-y-3 shadow-inner">
                        <div class="flex items-center justify-between border-b pb-2">
                            <span class="font-extrabold text-xs text-slate-900">TakvimTakip Mobil</span>
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">Mobil Uyumlu</span>
                        </div>

                        <div class="text-[11px] font-bold text-slate-700">Müsaitlik Takvimi</div>

                        <div class="grid grid-cols-7 gap-1 text-[9px] text-center font-bold text-slate-400">
                            <div>P</div><div>S</div><div>Ç</div><div>P</div><div>C</div><div>C</div><div>P</div>
                        </div>

                        <!-- Mini Mobile Grid -->
                        <div class="grid grid-cols-7 gap-1 text-[10px] font-bold text-center">
                            <div class="p-1 rounded bg-slate-100 text-slate-400">28</div>
                            <div class="p-1 rounded bg-slate-100 text-slate-400">29</div>
                            <div class="p-1 rounded bg-slate-100 text-slate-400">30</div>
                            <div class="p-1 rounded bg-slate-50">1</div>
                            <div class="p-1 rounded bg-slate-50">2</div>
                            <div class="p-1 rounded bg-slate-50">3</div>
                            <div class="p-1 rounded bg-slate-50">4</div>

                            <div class="p-1 rounded gradient-checkout text-slate-800">5</div>
                            <div class="p-1 rounded bg-rose-500 text-white">6</div>
                            <div class="p-1 rounded bg-rose-500 text-white">7</div>
                            <div class="p-1 rounded bg-rose-500 text-white">8</div>
                            <div class="p-1 rounded gradient-checkin text-slate-800">9</div>
                            <div class="p-1 rounded bg-slate-50">10</div>
                            <div class="p-1 rounded bg-slate-50">11</div>
                        </div>

                        <button class="w-full py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow">
                            Tarih Kapat
                        </button>
                    </div>

                    <!-- Home Bar -->
                    <div class="w-20 h-1 bg-slate-700 rounded-full mx-auto"></div>
                </div>
            </div>

            <!-- Right Text Content -->
            <div class="lg:col-span-7 space-y-6 order-1 lg:order-2">
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-full text-emerald-600 text-xs font-bold tracking-wide">
                    <i class="fa-solid fa-mobile-screen-button text-xs"></i>
                    <span>%100 MOBİL UYUMLU KULLANIM</span>
                </div>

                <h2 class="text-2xl sm:text-4xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                    Takviminiz Cebinizde. İstediğiniz Her Yerden Erişin.
                </h2>

                <p class="text-sm sm:text-base <?= $T['body_sub'] ?> leading-relaxed">
                    Bilgisayar başında durmak zorunda değilsiniz. Akıllı telefonunuzun tarayıcısından istediğiniz an giriş yapın, yoldayken veya dışarıdayken tarihinizi saniyeler içinde güncelleyin.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-2xl <?= $T['sub_card_alt'] ?> space-y-1 border">
                        <div class="font-bold <?= $T['heading'] ?> text-sm flex items-center space-x-2">
                            <i class="fa-solid fa-bolt text-indigo-600"></i>
                            <span>Hızlı Yüklenme</span>
                        </div>
                        <p class="text-xs <?= $T['body_muted'] ?>">Mobil ağlarda bile anında açılan ve kasmayan ultra hafif arayüz.</p>
                    </div>

                    <div class="p-4 rounded-2xl <?= $T['sub_card_alt'] ?> space-y-1 border">
                        <div class="font-bold <?= $T['heading'] ?> text-sm flex items-center space-x-2">
                            <i class="fa-solid fa-hand-pointer text-indigo-600"></i>
                            <span>Dokunmatik Uyumlu</span>
                        </div>
                        <p class="text-xs <?= $T['body_muted'] ?>">Parmağınızla tarih aralığı seçmek ve butonlara basmak çok rahat.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     8. ÖZELLİKLER (FEATURES)
     ========================================================================= -->
<section id="features" class="py-20 <?= $T['sec_main_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_indigo'] ?>">ÜRÜN YETENEKLERİ</h2>
            <p class="text-2xl sm:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                İşletmenizi Kolaylaştıracak Tüm Gerçek Özellikler.
            </p>
            <p class="text-sm <?= $T['body_muted'] ?>">
                Sadece villa ve bungalov işletmeciliğinde ihtiyaç duyacağınız işlevsel araçlar.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Feature 1 -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-3">
                <div class="w-10 h-10 rounded-xl <?= $isDark ? 'bg-indigo-500/10 text-indigo-400' : 'bg-indigo-50 text-indigo-600' ?> flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <h3 class="font-bold <?= $T['heading'] ?> text-base">Müsaitlik & Doluluk Takvimi</h3>
                <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">
                    Açık ve kapalı günleri renkli bloklar halinde görün, karışıklığa izin vermeyin.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-3">
                <div class="w-10 h-10 rounded-xl <?= $isDark ? 'bg-rose-500/10 text-rose-400' : 'bg-rose-50 text-rose-600' ?> flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-calendar-minus"></i>
                </div>
                <h3 class="font-bold <?= $T['heading'] ?> text-base">Tarih Aralığı Yönetimi</h3>
                <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">
                    Başlangıç ve bitiş tarihini dokunarak seçin, "Kapat" butonu ile saniyesinde kilitleyin.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-3">
                <div class="w-10 h-10 rounded-xl <?= $isDark ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600' ?> flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-rotate"></i>
                </div>
                <h3 class="font-bold <?= $T['heading'] ?> text-base">iCal Çift Yönlü Senkronizasyon</h3>
                <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">
                    Airbnb, Booking.com ve diğer platform doluluklarını takviminize otomatik eşitleyin.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="w-10 h-10 rounded-xl <?= $isDark ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600' ?> flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-share-nodes"></i>
                    </div>
                    <h3 class="font-bold <?= $T['heading'] ?> text-base flex flex-wrap items-center gap-1.5">
                        <span>Müşteri Linki</span>
                        <span class="text-slate-300 font-normal text-xs">|</span>
                        <a href="https://takvimtakip.com.tr/villa/villa-gunay-giray" target="_blank" rel="noopener" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline inline-flex items-center space-x-1">
                            <span>Örnek link için tıklayınız</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </h3>
                    <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">
                        Müşterilerinize güncel takvim, fiyat, resim, konum vb. bilgileri tek ekranda gösteren linki kolayca gönderin.
                    </p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-3">
                <div class="w-10 h-10 rounded-xl <?= $isDark ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600' ?> flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <h3 class="font-bold <?= $T['heading'] ?> text-base">Dönemsel Fiyatlandırma</h3>
                <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">
                    Sezonluk, gecelik ve haftalık konaklama fiyatlarınızı takviminize tanımlayın.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-3">
                <div class="w-10 h-10 rounded-xl <?= $isDark ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600' ?> flex items-center justify-center font-bold text-xl">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <h3 class="font-bold <?= $T['heading'] ?> text-base">WhatsApp ile Otomatik Bilgilendirme</h3>
                <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">
                    WhatsApp üzerinden müşterilerinize rezervasyon detaylarını otomatik olarak anında gönderin.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     9. HEDEF KİTLE (KİMLER İÇİN?)
     ========================================================================= -->
<section id="who-is-it-for" class="py-20 <?= $T['sec_alt_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_emerald'] ?>">HEDEF KİTLE</h2>
            <p class="text-2xl sm:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                TakvimTakip Kimler İçin Geliştirildi?
            </p>
            <p class="text-sm <?= $T['body_muted'] ?>">
                Konaklama sektörünün gerçek ihtiyaçlarına özel çözümler.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Persona 1 -->
            <div class="<?= $T['sub_card_alt'] ?> p-8 rounded-3xl border space-y-4">
                <div class="w-12 h-12 rounded-2xl <?= $isDark ? 'bg-indigo-500/10 text-indigo-400' : 'bg-indigo-50 text-indigo-600' ?> flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-house-chimney"></i>
                </div>
                <h3 class="text-lg font-bold <?= $T['heading'] ?>">Villa Sahipleri</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Kendi villasını günlük, haftalık veya sezonluk kiraya veren ve tarih çakışmalarını önlemek isteyen ev sahipleri.
                </p>
            </div>

            <!-- Persona 2 -->
            <div class="<?= $T['sub_card_alt'] ?> p-8 rounded-3xl border space-y-4">
                <div class="w-12 h-12 rounded-2xl <?= $isDark ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600' ?> flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-campground"></i>
                </div>
                <h3 class="text-lg font-bold <?= $T['heading'] ?>">Bungalov & Tiny House İşletmecileri</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Bungalov tesislerinde yoğun geçen rezervasyon dönemlerinde takvimi pratik şekilde güncellemek isteyen işletmeler.
                </p>
            </div>

            <!-- Persona 3 -->
            <div class="<?= $T['sub_card_alt'] ?> p-8 rounded-3xl border space-y-4">
                <div class="w-12 h-12 rounded-2xl <?= $isDark ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600' ?> flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 class="text-lg font-bold <?= $T['heading'] ?>">Kısa Dönem Konaklama Yöneticileri</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Birden fazla bağımsız evi veya daireyi yöneten ve acentelere güncel doluluk sunmak isteyen profesyoneller.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     10. NASIL ÇALIŞIR? (3 ADIM)
     ========================================================================= -->
<section id="how-it-works" class="py-20 <?= $T['sec_main_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_indigo'] ?>">BASİT VE HIZLI</h2>
            <p class="text-2xl sm:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                3 Basit Adımda Takviminizi Kontrol Edin.
            </p>
            <p class="text-sm <?= $T['body_muted'] ?>">
                Karmaşık eğitimlere gerek yok. 1 dakika içinde kullanmaya başlayabilirsiniz.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            
            <!-- Step 1 -->
            <div class="<?= $T['card'] ?> p-8 rounded-3xl space-y-4 relative">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm shadow-md">
                    01
                </div>
                <h3 class="text-lg font-bold <?= $T['heading'] ?>">Konutunuzu Seçin</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Sisteme kayıtlı villa veya bungalovunuzu açılan listeden seçin.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="<?= $T['card'] ?> p-8 rounded-3xl space-y-4 relative">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm shadow-md">
                    02
                </div>
                <h3 class="text-lg font-bold <?= $T['heading'] ?>">Tarih Aralığını İşaretleyin</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Açmak veya kapatmak istediğiniz başlangıç ve bitiş tarihlerine dokunun.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="<?= $T['card'] ?> p-8 rounded-3xl space-y-4 relative">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm shadow-md">
                    03
                </div>
                <h3 class="text-lg font-bold <?= $T['heading'] ?>">Durumu Güncelleyin</h3>
                <p class="text-xs sm:text-sm <?= $T['body_muted'] ?> leading-relaxed">
                    Açılan formu doldurun ve Rezervasyon Kaydet butonuna basın.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     11. FİYATLANDIRMA (PRICING)
     ========================================================================= -->
<section id="pricing" class="py-20 <?= $T['sec_alt_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-12">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_indigo'] ?>">ŞEFFAF FİYATLANDIRMA</h2>
            <p class="text-2xl sm:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                İhtiyacınıza Uygun Esnek Planlar
            </p>
            <p class="text-sm <?= $T['body_muted'] ?>">
                Gizli maliyet veya sürpriz ücret yok. İster tek konut ister tüm tesisleriniz için.
            </p>
        </div>

        <!-- Comparison Table Box -->
        <div class="max-w-5xl mx-auto bg-white rounded-3xl border border-slate-200/80 shadow-xl overflow-hidden text-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[640px]">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="py-5 px-6 font-bold text-[#4338ca] text-sm sm:text-base w-1/3">Özellik</th>
                            <th class="py-5 px-4 font-bold text-[#4338ca] text-sm sm:text-base text-center">Tek Konut</th>
                            <th class="py-5 px-4 font-bold text-[#4338ca] text-sm sm:text-base text-center">Küçük İşletme</th>
                            <th class="py-5 px-4 font-bold text-[#4338ca] text-sm sm:text-base text-center">Profesyonel</th>
                            <th class="py-5 px-4 font-bold text-[#4338ca] text-sm sm:text-base text-center">Kurumsal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                        <!-- Row 1: Konut adedi -->
                        <tr>
                            <td class="py-4 px-6 font-bold text-slate-800">Konut adedi</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-center">1 Adet</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-center">1–5 Adet</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-center">6–10 Adet</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-center">11+ Adet</td>
                        </tr>
                        <!-- Row 2: Fiyat -->
                        <tr>
                            <td class="py-4 px-6 font-bold text-slate-800">Fiyat</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-center whitespace-nowrap">Ücretsiz / 7 Gün</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-center whitespace-nowrap">1.000 TL / yıl</td>
                            <td class="py-4 px-4 font-bold text-slate-800 text-center whitespace-nowrap">1.750 TL / yıl</td>
                            <td class="py-4 px-4 font-bold text-center whitespace-nowrap">
                                <a href="https://wa.me/905437161546?text=Merhaba,%20TakvimTakip%20kurumsal%20paket%20i%C3%A7in%20fiyat%20teklifi%20almak%20istiyorum." target="_blank" rel="noopener" class="inline-flex items-center justify-center px-4 py-1.5 bg-[#4338ca] hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition active:scale-95 space-x-1.5">
                                    <span>Teklif Al</span>
                                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                                </a>
                            </td>
                        </tr>
                        <!-- Row 3: Takvim -->
                        <tr>
                            <td class="py-4 px-6">Takvim</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                        <!-- Row 4: Tarih açma & kapatma -->
                        <tr>
                            <td class="py-4 px-6">Tarih açma & kapatma</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                        <!-- Row 5: Rezervasyon yönetimi -->
                        <tr>
                            <td class="py-4 px-6">Rezervasyon yönetimi</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                        <!-- Row 6: Airbnb entegrasyonu -->
                        <tr>
                            <td class="py-4 px-6">Airbnb entegrasyonu</td>
                            <td class="py-4 px-4 text-center font-bold text-rose-500">✕</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                        <!-- Row 7: Booking entegrasyonu -->
                        <tr>
                            <td class="py-4 px-6">Booking entegrasyonu</td>
                            <td class="py-4 px-4 text-center font-bold text-rose-500">✕</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                        <!-- Row 8: Özel müşteri linki -->
                        <tr>
                            <td class="py-4 px-6">Özel müşteri linki</td>
                            <td class="py-4 px-4 text-center font-bold text-rose-500">✕</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                        <!-- Row 9: Çoklu konut yönetimi -->
                        <tr>
                            <td class="py-4 px-6">Çoklu konut yönetimi</td>
                            <td class="py-4 px-4 text-center font-bold text-rose-500">✕</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                        <!-- Row 10: Öncelikli destek -->
                        <tr>
                            <td class="py-4 px-6">Öncelikli destek</td>
                            <td class="py-4 px-4 text-center font-bold text-rose-500">✕</td>
                            <td class="py-4 px-4 text-center font-bold text-rose-500">✕</td>
                            <td class="py-4 px-4 text-center font-bold text-rose-500">✕</td>
                            <td class="py-4 px-4 text-center font-bold text-emerald-500">✓</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Bottom CTA Footer -->
            <div class="p-6 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs sm:text-sm text-slate-600 text-center sm:text-left font-medium">
                    <span class="font-bold text-indigo-600">7 Gün Ücretsiz Dene</span> — Kredi kartı gerekmeden hemen başlayın.
                </div>
                <a href="<?= $registerUrl ?>" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center space-x-2 shrink-0">
                    <span>Ücretsiz Kaydolun</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

    </div>
</section>

<!-- =========================================================================
     12. SIKÇA SORULAN SORULAR (FAQ ACCORDION)
     ========================================================================= -->
<section id="faq" class="py-20 <?= $T['sec_main_bg'] ?>">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <div class="text-center space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_indigo'] ?>">MERAK EDİLENLER</h2>
            <p class="text-2xl sm:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                Sıkça Sorulan Sorular
            </p>
        </div>

        <div class="space-y-4" id="faqAccordion">
            
            <!-- Item 1 -->
            <div class="<?= $T['faq_item'] ?> border rounded-2xl overflow-hidden transition">
                <button onclick="toggleFaq(1)" class="w-full p-5 text-left font-bold text-sm sm:text-base <?= $T['faq_btn'] ?> flex items-center justify-between gap-4">
                    <span>TakvimTakip nedir ve ne işe yarar?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" id="faqIcon1"></i>
                </button>
                <div id="faqContent1" class="hidden px-5 pb-5 text-xs sm:text-sm <?= $T['faq_content'] ?> leading-relaxed border-t pt-3">
                    TakvimTakip; villa, bungalov ve kısa dönem konaklama işletmecilerinin rezervasyon takvimlerini kolayca yönetmesini sağlayan web tabanlı bir takvim yazılımıdır. Belirli tarih aralıklarını saniyeler içinde açmanızı veya kapatmanızı sağlar.
                </div>
            </div>

            <!-- Item 2 -->
            <div class="<?= $T['faq_item'] ?> border rounded-2xl overflow-hidden transition">
                <button onclick="toggleFaq(2)" class="w-full p-5 text-left font-bold text-sm sm:text-base <?= $T['faq_btn'] ?> flex items-center justify-between gap-4">
                    <span>Belirli tarih aralıklarını nasıl kapatabilirim?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" id="faqIcon2"></i>
                </button>
                <div id="faqContent2" class="hidden px-5 pb-5 text-xs sm:text-sm <?= $T['faq_content'] ?> leading-relaxed border-t pt-3">
                    Takvim ekranınızda kapatmak istediğiniz başlangıç ve bitiş tarihlerine dokunmanız veya tıklamanız yeterlidir. "Tarihleri Kapat" butonuna bastığınızda seçtiğiniz tarihler anında kapalı duruma geçer.
                </div>
            </div>

            <!-- Item 3 -->
            <div class="<?= $T['faq_item'] ?> border rounded-2xl overflow-hidden transition">
                <button onclick="toggleFaq(3)" class="w-full p-5 text-left font-bold text-sm sm:text-base <?= $T['faq_btn'] ?> flex items-center justify-between gap-4">
                    <span>Mobil telefondan kullanabilir miyim?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" id="faqIcon3"></i>
                </button>
                <div id="faqContent3" class="hidden px-5 pb-5 text-xs sm:text-sm <?= $T['faq_content'] ?> leading-relaxed border-t pt-3">
                    Evet! TakvimTakip mobil cihazlarla %100 uyumludur. Akıllı telefonunuzun tarayıcısından tek tıkla giriş yaparak dışarıdayken veya yoldayken takviminizi rahatça yönetebilirsiniz.
                </div>
            </div>

            <!-- Item 4 -->
            <div class="<?= $T['faq_item'] ?> border rounded-2xl overflow-hidden transition">
                <button onclick="toggleFaq(4)" class="w-full p-5 text-left font-bold text-sm sm:text-base <?= $T['faq_btn'] ?> flex items-center justify-between gap-4">
                    <span>Airbnb ve Booking.com takvimlerimle senkronize olur mu?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" id="faqIcon4"></i>
                </button>
                <div id="faqContent4" class="hidden px-5 pb-5 text-xs sm:text-sm <?= $T['faq_content'] ?> leading-relaxed border-t pt-3">
                    Evet. Çift yönlü iCal entegrasyonu sayesinde Airbnb, Booking.com ve diğer platform takvim linklerini içe aktarabilir, doluluklarınızın otomatik eşitlemesini sağlayabilirsiniz.
                </div>
            </div>

            <!-- Item 5 -->
            <div class="<?= $T['faq_item'] ?> border rounded-2xl overflow-hidden transition">
                <button onclick="toggleFaq(5)" class="w-full p-5 text-left font-bold text-sm sm:text-base <?= $T['faq_btn'] ?> flex items-center justify-between gap-4">
                    <span>Müşterilerime villamın takvimini gönderebilir miyim?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" id="faqIcon5"></i>
                </button>
                <div id="faqContent5" class="hidden px-5 pb-5 text-xs sm:text-sm <?= $T['faq_content'] ?> leading-relaxed border-t pt-3">
                    Evet! Her villanız için özel bir canlı takvim linki oluşturulur. Bu bağlantıda ekstra olarak resimler, fiyatlar, konum vb. gibi villanıza ait özel verileriniz de görünür.
                </div>
            </div>

            <!-- Item 6 -->
            <div class="<?= $T['faq_item'] ?> border rounded-2xl overflow-hidden transition">
                <button onclick="toggleFaq(6)" class="w-full p-5 text-left font-bold text-sm sm:text-base <?= $T['faq_btn'] ?> flex items-center justify-between gap-4">
                    <span>Birden fazla villa veya bungalov ekleyebilir miyim?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" id="faqIcon6"></i>
                </button>
                <div id="faqContent6" class="hidden px-5 pb-5 text-xs sm:text-sm <?= $T['faq_content'] ?> leading-relaxed border-t pt-3">
                    Evet, hesabınıza dilediğiniz kadar villa veya bungalov ekleyebilir ve hepsinin takvimini tek bir panellerden yönetebilirsiniz.
                </div>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================================
     13. İLETİŞİM & DESTEK (CONTACT)
     ========================================================================= -->
<section id="contact" class="py-20 <?= $T['sec_alt_bg'] ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-widest <?= $T['tag_indigo'] ?>">İLETİŞİM & DESTEK</h2>
            <p class="text-2xl sm:text-3xl font-extrabold <?= $T['heading'] ?> tracking-tight">
                7/24 İrtibat ve Destek Hattı
            </p>
            <p class="text-sm <?= $T['body_muted'] ?>">
                Sorularınız, üyelik detayları veya takvim kurulumu desteği için dilediğiniz an bizimle iletişime geçebilirsiniz.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto items-stretch">
            <!-- Card 1: Phone & WhatsApp -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-4 flex flex-col justify-between border">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <h3 class="text-lg font-bold <?= $T['heading'] ?>">Telefon & WhatsApp</h3>
                    <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">Hızlı bilgi ve doğrudan irtibat için bizi arayabilir veya WhatsApp üzerinden yazabilirsiniz.</p>
                    <div class="text-xl font-extrabold <?= $isDark ? 'text-white' : 'text-slate-900' ?> pt-1 tracking-tight">
                        0543 716 15 46
                    </div>
                </div>
                <div class="flex items-center space-x-2 pt-4">
                    <a href="tel:05437161546" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition text-center flex items-center justify-center space-x-1.5 shadow-sm shadow-indigo-600/20">
                        <i class="fa-solid fa-phone text-xs"></i>
                        <span>Hemen Ara</span>
                    </a>
                    <a href="https://wa.me/905437161546" target="_blank" rel="noopener" class="flex-1 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs rounded-xl transition text-center flex items-center justify-center space-x-1.5">
                        <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- Card 2: Hours & Guarantees -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-4 flex flex-col justify-between border">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3 class="text-lg font-bold <?= $T['heading'] ?>">Çalışma Saatleri & Güvence</h3>
                    <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">Haftanın 7 günü takvim yönetimi ve teknik konularda kesintisiz destek veriyoruz.</p>
                    <div class="text-xs font-semibold <?= $T['heading'] ?> space-y-1.5 pt-1">
                        <div class="flex items-center space-x-2">
                            <i class="fa-regular fa-clock text-indigo-500"></i>
                            <span>Pzt - Paz: 09:00 - 22:00</span>
                        </div>
                        <div class="flex items-center space-x-2 text-emerald-600">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Hızlı Yanıt Garantisi</span>
                        </div>
                    </div>
                </div>
                <div class="pt-4">
                    <a href="https://www.trendvillas.net" target="_blank" rel="noopener" class="w-full p-2.5 <?= $isDark ? 'bg-slate-800/60 hover:bg-slate-800 border-slate-700 text-indigo-300' : 'bg-indigo-50/80 hover:bg-indigo-100 border-indigo-200/80 text-indigo-700' ?> rounded-xl border text-[11px] text-center font-bold transition flex items-center justify-center space-x-1.5 active:scale-95">
                        <i class="fa-solid fa-shield-check text-indigo-600"></i>
                        <span>www.trendvillas.net güvencesiyle</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] opacity-75"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Free Setup Assistance -->
            <div class="<?= $T['card'] ?> p-6 rounded-3xl space-y-4 flex flex-col justify-between border">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-xl font-bold">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <h3 class="text-lg font-bold <?= $T['heading'] ?>">Ücretsiz Kurulum Desteği</h3>
                    <p class="text-xs <?= $T['body_muted'] ?> leading-relaxed">Villalarınızı veya Airbnb / Booking iCal bağlantılarınızı eklemekte yardıma ihtiyacınız olursa ekibimiz yanınızda.</p>
                </div>
                <div class="pt-4">
                    <a href="<?= $registerUrl ?>" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition text-center block shadow-sm">
                        Ücretsiz Hesabınızı Oluşturun
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     14. FINAL CTA SECTION
     ========================================================================= -->
<section class="<?= $T['cta_bg'] ?> py-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-tr <?= $T['cta_glow'] ?> pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-6">
        
        <h2 class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-extrabold <?= $T['cta_heading'] ?> tracking-tight sm:whitespace-nowrap">
            Villanızın Takvimini TakvimTakip ile Kontrol Altına Alın.
        </h2>

        <p class="text-base sm:text-lg <?= $T['cta_sub'] ?> max-w-2xl mx-auto leading-relaxed">
            Karmaşık defterleri ve mesaj takibini bir kenara bırakın. Müsaitlik tarihlerinizi saniyeler içinde yönetmeye başlayın.
        </p>

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= $isLoggedIn ? $dashboardUrl : $registerUrl ?>" class="w-full sm:w-auto px-9 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-sm rounded-2xl shadow-xl shadow-indigo-600/30 transition active:scale-95 flex items-center justify-center space-x-2">
                <span>Ücretsiz Hesabınızı Oluşturun</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- =========================================================================
     15. FOOTER
     ========================================================================= -->
<footer class="<?= $T['footer_bg'] ?> pt-16 pb-12 text-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <!-- Column 1: Brand Info -->
            <div class="space-y-4 md:col-span-2">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold shadow-sm">
                        <i class="fa-solid fa-calendar-days text-sm"></i>
                    </div>
                    <span class="text-base font-extrabold <?= $T['footer_title'] ?> tracking-tight">TakvimTakip</span>
                </div>
                <p class="text-xs <?= $T['footer_subtext'] ?> max-w-sm leading-relaxed">
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
                <h4 class="font-bold <?= $T['footer_title'] ?> text-xs uppercase tracking-wider">Hızlı Bağlantılar</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#how-it-works" class="<?= $T['footer_link'] ?> transition">Nasıl Çalışır?</a></li>
                    <li><a href="#interactive-showcase" class="<?= $T['footer_link'] ?> transition">Canlı Simülasyon</a></li>
                    <li><a href="#features" class="<?= $T['footer_link'] ?> transition">Özellikler</a></li>
                    <li><a href="#ical-sync" class="<?= $T['footer_link'] ?> transition">iCal Senkronizasyonu</a></li>
                    <li><a href="#pricing" class="<?= $T['footer_link'] ?> transition">Fiyatlandırma</a></li>
                    <li><a href="#contact" class="<?= $T['footer_link'] ?> transition">İletişim</a></li>
                </ul>
            </div>

            <!-- Column 3: Legal & Support -->
            <div class="space-y-3">
                <h4 class="font-bold <?= $T['footer_title'] ?> text-xs uppercase tracking-wider">Kurumsal & Yasal</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="<?= APP_URL ?>/iletisim" class="<?= $T['footer_link'] ?> transition block text-left">İletişim Bilgileri</a></li>
                    <li><a href="<?= APP_URL ?>/gizlilik" class="<?= $T['footer_link'] ?> transition block text-left">Gizlilik Sözleşmesi</a></li>
                    <li><a href="<?= APP_URL ?>/kullanim-kosullari" class="<?= $T['footer_link'] ?> transition block text-left">Kullanım Koşulları</a></li>
                    <li><a href="<?= APP_URL ?>/kvkk" class="<?= $T['footer_link'] ?> transition block text-left">KVKK Aydınlatma Metni</a></li>
                    <li><a href="<?= $loginUrl ?>" class="<?= $T['footer_link'] ?> transition block">Giriş Yap</a></li>
                </ul>
            </div>

        </div>

        <div class="border-t <?= $T['footer_border'] ?> pt-8 flex flex-col sm:flex-row items-center justify-between <?= $T['footer_subtext'] ?> text-[11px] gap-4 text-center sm:text-left">
            <div class="space-y-1">
                <div>
                    &copy; <?= date('Y') ?> TakvimTakip. Tüm hakları saklıdır. Villa & Bungalov <span class="whitespace-nowrap">Takvim Yazılımı.</span>
                </div>
                <div class="text-center sm:text-left pt-0.5">
                    <a href="https://www.trendvillas.net" target="_blank" rel="noopener" class="<?= $isDark ? 'text-slate-400 hover:text-indigo-400' : 'text-slate-700 hover:text-indigo-600' ?> transition font-semibold">www.trendvillas.net</a> Güvencesiyle
                </div>
            </div>
            <div class="flex items-center justify-center space-x-4 shrink-0">
                <span><i class="fa-solid fa-lock text-emerald-500 mr-1"></i>SSL Korumalı</span>
                <span><i class="fa-solid fa-shield-halved text-indigo-500 mr-1"></i>Güvenli Altyapı</span>
            </div>
        </div>

    </div>
</footer>

<!-- Legal Pop-up Modal -->
<div id="legalModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="<?= $T['modal_bg'] ?> border rounded-3xl max-w-2xl w-full p-6 space-y-4 max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b <?= $isDark ? 'border-slate-800' : 'border-slate-200' ?> pb-3">
            <h3 class="font-bold text-base" id="legalModalTitle">Yasal Bilgilendirme</h3>
            <button onclick="closeLegalModal()" class="w-8 h-8 rounded-full <?= $isDark ? 'bg-slate-800 text-slate-400 hover:text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?> flex items-center justify-center font-bold">&times;</button>
        </div>
        <div class="text-xs <?= $isDark ? 'text-slate-300' : 'text-slate-600' ?> leading-relaxed space-y-3" id="legalModalBody">
            <!-- Content filled by JS -->
        </div>
    </div>
</div>

<!-- =========================================================================
     15. INTERACTIVE CLIENT-SIDE SCRIPTS
     ========================================================================= -->
<script>
// =========================================================================
// HERO CALENDAR INTERACTIVE SIMULATION (Start Date -> End Date -> Form Modal -> Closed)
// =========================================================================
let heroStartDate = null;
let heroEndDate = null;
let heroClosedRanges = []; // Array of {start, end, status}
let heroSelectedStatus = 'agency';

function renderHeroCalendar() {
    const grid = document.getElementById('heroCalendarGrid');
    if (!grid) return;

    let html = '';
    // June 2026 starts on Monday (0 pad cells)
    for (let d = 1; d <= 30; d++) {
        let cellClass = "bg-slate-50 text-slate-700 border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/60";
        let isClosed = false;
        let isStartCell = false;
        let isEndCell = false;

        // Check if day falls inside any closed range
        for (let r of heroClosedRanges) {
            if (d >= r.start && d <= r.end) {
                isClosed = true;
                if (d === r.start) isStartCell = true;
                if (d === r.end) isEndCell = true;
                break;
            }
        }

        if (isClosed) {
            if (isStartCell && isEndCell) {
                cellClass = "bg-[#fd5a5f] text-white font-extrabold shadow-sm";
            } else if (isStartCell) {
                cellClass = "gradient-checkin text-slate-900 border-2 border-rose-500 font-extrabold shadow-sm";
            } else if (isEndCell) {
                cellClass = "gradient-checkout text-slate-900 border-2 border-rose-500 font-extrabold shadow-sm";
            } else {
                cellClass = "bg-[#fd5a5f] text-white font-extrabold border border-rose-600 shadow-sm";
            }
        } else if (heroStartDate !== null && heroEndDate !== null && d >= heroStartDate && d <= heroEndDate) {
            if (d === heroStartDate || d === heroEndDate) {
                cellClass = "bg-indigo-600 text-white font-extrabold ring-2 ring-indigo-400 shadow-sm";
            } else {
                cellClass = "bg-indigo-100 text-indigo-900 font-bold border border-indigo-300";
            }
        } else if (heroStartDate !== null && d === heroStartDate) {
            cellClass = "bg-indigo-600 text-white font-extrabold ring-2 ring-indigo-400 animate-pulse shadow-sm";
        }

        html += `
            <div onclick="handleHeroDateClick(${d})" class="aspect-square rounded-xl text-xs sm:text-sm flex items-center justify-center font-bold cursor-pointer transition select-none ${cellClass}">
                ${d}
            </div>
        `;
    }

    // Fill remaining cells for 35 total cells
    for (let p = 1; p <= 5; p++) {
        html += `<div class="aspect-square bg-slate-50 text-slate-300 rounded-xl text-xs flex items-center justify-center opacity-40 select-none">${p}</div>`;
    }

    grid.innerHTML = html;
    updateHeroStepBar();
}

function handleHeroDateClick(day) {
    if (heroStartDate === null) {
        // Step 1: Click Start Date
        heroStartDate = day;
        heroEndDate = null;
        renderHeroCalendar();
    } else if (heroEndDate === null) {
        // Step 2: Click End Date
        if (day <= heroStartDate) {
            heroStartDate = day;
            heroEndDate = null;
            renderHeroCalendar();
        } else {
            heroEndDate = day;
            renderHeroCalendar();
            // Step 3: Trigger Form Modal Screen
            openHeroModal();
        }
    } else {
        // Restart selection with new start date
        heroStartDate = day;
        heroEndDate = null;
        renderHeroCalendar();
    }
}

function updateHeroStepBar() {
    const badge = document.getElementById('heroStepBadge');
    const text = document.getElementById('heroStepText');
    const resultStatus = document.getElementById('heroResultStatus');
    const stepBar = document.getElementById('heroStepBar');

    if (!badge || !text) return;

    if (heroClosedRanges.length > 0 && heroStartDate === null) {
        stepBar.classList.add('hidden');
        resultStatus.classList.remove('hidden');
    } else {
        stepBar.classList.remove('hidden');
        resultStatus.classList.add('hidden');

        if (heroStartDate === null) {
            badge.innerText = "1";
            badge.className = "w-5 h-5 rounded-full bg-indigo-600 text-white font-extrabold text-[11px] flex items-center justify-center";
            text.innerHTML = `<b>1. Adım:</b> Başlangıç (Giriş) tarihine tıklayın <span class="text-indigo-600 font-bold">(Örn: 12 Haziran)</span>`;
        } else if (heroEndDate === null) {
            badge.innerText = "2";
            badge.className = "w-5 h-5 rounded-full bg-amber-500 text-white font-extrabold text-[11px] flex items-center justify-center";
            text.innerHTML = `<b>2. Adım:</b> Bitiş (Çıkış) tarihine tıklayın <span class="text-amber-600 font-bold">(Örn: 15 Haziran)</span>`;
        } else {
            badge.innerText = "3";
            badge.className = "w-5 h-5 rounded-full bg-emerald-600 text-white font-extrabold text-[11px] flex items-center justify-center";
            text.innerHTML = `<b>3. Adım:</b> Form ekranında <b>"Tarihleri Kapat"</b> butonuna basın.`;
        }
    }
}

function openHeroModal() {
    const modal = document.getElementById('heroModalOverlay');
    const rangeText = document.getElementById('heroModalDateRangeText');
    if (!modal || !rangeText) return;

    const nights = heroEndDate - heroStartDate;
    rangeText.innerText = `${heroStartDate}.06.2026 - ${heroEndDate}.06.2026 (${nights} Gece)`;
    modal.classList.remove('hidden');
}

function closeHeroModal() {
    const modal = document.getElementById('heroModalOverlay');
    if (modal) modal.classList.add('hidden');
    heroStartDate = null;
    heroEndDate = null;
    renderHeroCalendar();
}

function setHeroModalStatus(type) {
    heroSelectedStatus = type;
    const agencyBtn = document.getElementById('heroStatusAgencyBtn');
    const bookedBtn = document.getElementById('heroStatusBookedBtn');

    if (type === 'agency') {
        agencyBtn.className = "py-2 px-2 text-center font-bold text-[11px] rounded-xl border-2 border-indigo-600 bg-indigo-50 text-indigo-700 flex items-center justify-center space-x-1 transition";
        bookedBtn.className = "py-2 px-2 text-center font-bold text-[11px] rounded-xl border-2 border-slate-200 text-slate-600 flex items-center justify-center space-x-1 transition";
    } else {
        bookedBtn.className = "py-2 px-2 text-center font-bold text-[11px] rounded-xl border-2 border-rose-500 bg-rose-50 text-rose-700 flex items-center justify-center space-x-1 transition";
        agencyBtn.className = "py-2 px-2 text-center font-bold text-[11px] rounded-xl border-2 border-slate-200 text-slate-600 flex items-center justify-center space-x-1 transition";
    }
}

function submitHeroModalForm() {
    if (heroStartDate !== null && heroEndDate !== null) {
        heroClosedRanges.push({
            start: heroStartDate,
            end: heroEndDate,
            status: heroSelectedStatus
        });
        
        const resText = document.getElementById('heroResultText');
        const nights = heroEndDate - heroStartDate;
        if (resText) {
            resText.innerHTML = `
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                <span class="font-bold text-rose-600">${heroStartDate} - ${heroEndDate} Haziran Kapatıldı (${nights} Gece Rezerve)</span>
            `;
        }
    }
    
    document.getElementById('heroModalOverlay').classList.add('hidden');
    heroStartDate = null;
    heroEndDate = null;
    renderHeroCalendar();
}

function resetHeroCalendar() {
    heroStartDate = null;
    heroEndDate = null;
    heroClosedRanges = [];
    const modal = document.getElementById('heroModalOverlay');
    if (modal) modal.classList.add('hidden');
    renderHeroCalendar();
}

// =========================================================================
// SHOWCASE SECTION INTERACTIVE CALENDAR
// =========================================================================
let showcaseStartDate = null;
let showcaseEndDate = null;
let showcaseClosedRanges = [];
let showcaseSelectedStatus = 'agency';

function renderShowcaseGrid() {
    const grid = document.getElementById('showcaseGrid');
    if (!grid) return;

    let html = '';
    // Empty pad for July 2026 (Wednesday = 2 pad cells)
    html += `<div class="aspect-square opacity-0 pointer-events-none"></div>`;
    html += `<div class="aspect-square opacity-0 pointer-events-none"></div>`;

    for (let d = 1; d <= 31; d++) {
        let cellClass = "bg-slate-50 text-slate-700 border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/60";
        let isClosed = false;
        let isStartCell = false;
        let isEndCell = false;

        for (let r of showcaseClosedRanges) {
            if (d >= r.start && d <= r.end) {
                isClosed = true;
                if (d === r.start) isStartCell = true;
                if (d === r.end) isEndCell = true;
                break;
            }
        }

        if (isClosed) {
            if (isStartCell && isEndCell) {
                cellClass = "bg-[#fd5a5f] text-white font-extrabold shadow-sm";
            } else if (isStartCell) {
                cellClass = "gradient-checkin text-slate-900 border-2 border-rose-400 font-extrabold shadow-sm";
            } else if (isEndCell) {
                cellClass = "gradient-checkout text-slate-900 border-2 border-rose-400 font-extrabold shadow-sm";
            } else {
                cellClass = "bg-[#fd5a5f] text-white font-extrabold border border-rose-600 shadow-sm";
            }
        } else if (showcaseStartDate !== null && showcaseEndDate !== null && d >= showcaseStartDate && d <= showcaseEndDate) {
            if (d === showcaseStartDate || d === showcaseEndDate) {
                cellClass = "bg-indigo-600 text-white font-extrabold ring-2 ring-indigo-400 shadow-sm";
            } else {
                cellClass = "bg-indigo-100 text-indigo-900 font-bold border border-indigo-300";
            }
        } else if (showcaseStartDate !== null && d === showcaseStartDate) {
            cellClass = "bg-indigo-600 text-white font-extrabold ring-2 ring-indigo-400 animate-pulse shadow-sm";
        }

        html += `
            <div onclick="handleShowcaseDateClick(${d})" class="aspect-square rounded-xl text-xs sm:text-sm flex items-center justify-center font-bold cursor-pointer transition select-none ${cellClass}">
                ${d}
            </div>
        `;
    }

    grid.innerHTML = html;
    updateShowcaseStepBar();
}

function handleShowcaseDateClick(day) {
    if (showcaseStartDate === null) {
        showcaseStartDate = day;
        showcaseEndDate = null;
        renderShowcaseGrid();
    } else if (showcaseEndDate === null) {
        if (day <= showcaseStartDate) {
            showcaseStartDate = day;
            showcaseEndDate = null;
            renderShowcaseGrid();
        } else {
            showcaseEndDate = day;
            renderShowcaseGrid();
            openShowcaseModal();
        }
    } else {
        showcaseStartDate = day;
        showcaseEndDate = null;
        renderShowcaseGrid();
    }
}

function updateShowcaseStepBar() {
    const badge = document.getElementById('showcaseStepBadge');
    const text = document.getElementById('showcaseStepText');
    const statusText = document.getElementById('showcaseStatusText');

    if (!badge || !text) return;

    if (showcaseStartDate === null) {
        badge.innerText = "1";
        badge.className = "w-5 h-5 rounded-full bg-indigo-600 text-white font-extrabold text-[11px] flex items-center justify-center";
        if (showcaseClosedRanges.length === 0) {
            text.innerHTML = `<b>1. Adım:</b> Başlangıç (Giriş) tarihine tıklayın <span class="text-indigo-600 font-bold">(Örn: 15 Temmuz)</span>`;
        } else {
            text.innerHTML = `<b>1. Adım:</b> Yeni bir işlem için Giriş tarihine tıklayın`;
        }
    } else if (showcaseEndDate === null) {
        badge.innerText = "2";
        badge.className = "w-5 h-5 rounded-full bg-amber-500 text-white font-extrabold text-[11px] flex items-center justify-center";
        const exEnd = Math.min(31, showcaseStartDate + 5);
        text.innerHTML = `<b>2. Adım:</b> Bitiş (Çıkış) tarihine tıklayın <span class="text-amber-600 font-bold">(Seçilen Giriş: ${showcaseStartDate} Temmuz — Örn Çıkış: ${exEnd} Temmuz)</span>`;
    } else {
        badge.innerText = "3";
        badge.className = "w-5 h-5 rounded-full bg-emerald-600 text-white font-extrabold text-[11px] flex items-center justify-center";
        text.innerHTML = `<b>3. Adım:</b> Form ekranında <b>"Tarihleri Kapat"</b> butonuna basın.`;
    }

    if (statusText && showcaseClosedRanges.length > 0) {
        const last = showcaseClosedRanges[showcaseClosedRanges.length - 1];
        const nights = last.end - last.start;
        statusText.innerHTML = `<span class="text-rose-600 font-bold"><i class="fa-solid fa-lock text-rose-500 mr-1"></i>${last.start} - ${last.end} Temmuz tarihleri başarıyla KAPATILDI (${nights} Gece Dolu)</span>`;
    }
}

function openShowcaseModal() {
    const modal = document.getElementById('showcaseModalOverlay');
    const rangeText = document.getElementById('showcaseModalDateRangeText');
    if (!modal || !rangeText) return;

    const sDay = (showcaseStartDate < 10 ? '0' : '') + showcaseStartDate;
    const eDay = (showcaseEndDate < 10 ? '0' : '') + showcaseEndDate;
    rangeText.innerHTML = `${sDay}.09.2026 &nbsp;➔&nbsp; ${eDay}.09.2026`;

    const today = new Date();
    const dd = String(today.getDate()).padStart(2, '0');
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const yyyy = today.getFullYear();
    const todayFormatted = `${dd}.${mm}.${yyyy}`;

    const agencyDateInput = document.getElementById('showcaseAgencyBookingDate');
    if (agencyDateInput) agencyDateInput.value = todayFormatted;

    const ownerDateInput = document.getElementById('showcaseOwnerBookingDate');
    if (ownerDateInput) ownerDateInput.value = todayFormatted;

    // Reset financial inputs
    const pInput = document.getElementById('showcaseOwnerPrice');
    const dInput = document.getElementById('showcaseOwnerDownPayment');
    const rInput = document.getElementById('showcaseOwnerRemainingPayment');
    if (pInput) pInput.value = '';
    if (dInput) dInput.value = '';
    if (rInput) rInput.value = '';

    setShowcaseModalStatus('agency');
    modal.classList.remove('hidden');
}

function parseShowcaseMoney(str) {
    if (!str) return 0;
    let clean = String(str).replace(/[^0-9]/g, '');
    return clean ? parseInt(clean, 10) : 0;
}

function handleShowcaseMoneyInput(input) {
    let oldVal = input.value;
    let oldPos = input.selectionStart || 0;

    let raw = parseShowcaseMoney(oldVal);
    if (raw > 0) {
        input.value = raw.toLocaleString('tr-TR');
    } else {
        input.value = '';
    }

    let newVal = input.value;
    let addedChars = newVal.length - oldVal.length;
    let newPos = Math.max(0, oldPos + addedChars);
    if (input.setSelectionRange && document.activeElement === input) {
        try {
            input.setSelectionRange(newPos, newPos);
        } catch (e) {}
    }

    calculateShowcaseRemaining();
}

function calculateShowcaseRemaining() {
    const priceInput = document.getElementById('showcaseOwnerPrice');
    const downInput = document.getElementById('showcaseOwnerDownPayment');
    const remainingInput = document.getElementById('showcaseOwnerRemainingPayment');

    if (!priceInput || !downInput || !remainingInput) return;

    const price = parseShowcaseMoney(priceInput.value);
    const down = parseShowcaseMoney(downInput.value);

    if (price > 0) {
        const remaining = Math.max(0, price - down);
        remainingInput.value = remaining.toLocaleString('tr-TR');
    } else {
        remainingInput.value = '';
    }
}

function closeShowcaseModal() {
    const modal = document.getElementById('showcaseModalOverlay');
    if (modal) modal.classList.add('hidden');
    showcaseStartDate = null;
    showcaseEndDate = null;
    renderShowcaseGrid();
}

function setShowcaseModalStatus(type) {
    showcaseSelectedStatus = type;
    const agencyBtn = document.getElementById('showcaseStatusAgencyBtn');
    const bookedBtn = document.getElementById('showcaseStatusBookedBtn');
    const submitBtn = document.getElementById('showcaseModalSubmitBtn');
    const agencyBox = document.getElementById('showcaseAgencyBox');
    const ownerBox = document.getElementById('showcaseOwnerBox');

    if (type === 'agency') {
        if (agencyBox) agencyBox.classList.remove('hidden');
        if (ownerBox) ownerBox.classList.add('hidden');

        agencyBtn.className = "py-2.5 px-1.5 sm:px-2 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-indigo-600 bg-indigo-50 text-indigo-700 flex items-center justify-center space-x-1 sm:space-x-1.5 transition min-w-0";
        bookedBtn.className = "py-2.5 px-1.5 sm:px-2 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-slate-200 text-slate-600 flex items-center justify-center space-x-1 sm:space-x-1.5 transition min-w-0";
        if (submitBtn) {
            submitBtn.className = "flex-grow py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg transition active:scale-[0.99] flex items-center justify-center";
            submitBtn.innerHTML = "<span>Rezervasyonu Kaydet</span>";
        }
    } else {
        if (agencyBox) agencyBox.classList.add('hidden');
        if (ownerBox) ownerBox.classList.remove('hidden');

        bookedBtn.className = "py-2.5 px-1.5 sm:px-2 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-rose-500 bg-rose-50 text-rose-700 flex items-center justify-center space-x-1 sm:space-x-1.5 transition min-w-0";
        agencyBtn.className = "py-2.5 px-1.5 sm:px-2 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-slate-200 text-slate-600 flex items-center justify-center space-x-1 sm:space-x-1.5 transition min-w-0";
        if (submitBtn) {
            submitBtn.className = "flex-grow py-3 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg transition active:scale-[0.99] flex items-center justify-center";
            submitBtn.innerHTML = "<span>Rezervasyonu Kaydet</span>";
        }
    }
}

function submitShowcaseModalForm() {
    if (showcaseStartDate !== null && showcaseEndDate !== null) {
        showcaseClosedRanges.push({
            start: showcaseStartDate,
            end: showcaseEndDate,
            status: showcaseSelectedStatus
        });
    }
    
    document.getElementById('showcaseModalOverlay').classList.add('hidden');
    showcaseStartDate = null;
    showcaseEndDate = null;
    renderShowcaseGrid();
}

function demoResetCalendar() {
    showcaseStartDate = null;
    showcaseEndDate = null;
    showcaseClosedRanges = [];
    const modal = document.getElementById('showcaseModalOverlay');
    if (modal) modal.classList.add('hidden');
    renderShowcaseGrid();
}

// --- FAQ ACCORDION ---
function toggleFaq(num) {
    const content = document.getElementById(`faqContent${num}`);
    const icon = document.getElementById(`faqIcon${num}`);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// --- MOBILE MENU ---
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// --- LEGAL MODALS ---
function openLegalModal(type) {
    const modal = document.getElementById('legalModal');
    const title = document.getElementById('legalModalTitle');
    const body = document.getElementById('legalModalBody');

    if (type === 'contact') {
        title.innerText = 'İletişim Bilgileri';
        body.innerHTML = `
            <p><strong>TakvimTakip İrtibat Hattı:</strong></p>
            <p class="text-base font-extrabold text-emerald-600 my-2 flex items-center space-x-2">
                <i class="fa-solid fa-phone"></i>
                <a href="tel:05437161546" class="hover:underline">0543 716 15 46</a>
            </p>
            <p><strong>WhatsApp Destek:</strong> <a href="https://wa.me/905437161546" target="_blank" rel="noopener" class="text-emerald-600 underline font-bold">WhatsApp İle Mesaj Gönder (0543 716 15 46)</a></p>
            <p><strong>Altyapı & Güvence:</strong> <a href="https://www.trendvillas.net" target="_blank" rel="noopener" class="text-indigo-600 underline">www.trendvillas.net</a></p>
            <p>Haftanın 7 günü 09:00 - 22:00 saatleri arasında destek ekibimize ulaşabilir, takvim kurulumu ve üyelik desteği alabilirsiniz.</p>
        `;
    } else if (type === 'privacy') {
        title.innerText = 'Gizlilik Sözleşmesi';
        body.innerHTML = `
            <div class="space-y-3">
                <h4 class="font-bold text-slate-800 text-sm">TakvimTakip'te Verileriniz Nasıl Korunuyor?</h4>
                <p>TakvimTakip olarak kişisel verilerinizin, müşteri bilgilerinizin ve işletme verilerinizin gizliliğine büyük önem veriyoruz. Sistemi tasarlarken yalnızca ihtiyaç duyduğumuz verileri toplamayı ve bu verilerin yalnızca gerekli olduğu amaçlarla kullanılmasını esas alıyoruz.</p>
                <p><strong>1. Yalnızca Gerekli Verileri Topluyoruz:</strong><br>TakvimTakip'i kullanabilmeniz ve rezervasyonlarınızı yönetebilmeniz için gerekli olan bilgileri topluyoruz. Bunlar; ad, e-posta adresi, telefon numarası, konut/işletme bilgileri ve rezervasyon yönetimi için sisteme eklediğiniz bilgiler olabilir. Gereksiz kişisel verilerin toplanmasından kaçınıyoruz.</p>
                <p><strong>2. Müşteri Bilgileriniz Gizli Kalır:</strong><br>TakvimTakip'e eklediğiniz müşterilerin ad, soyad, telefon numarası, iletişim bilgileri ve benzeri kişisel bilgileri herkese açık değildir. Bu bilgiler, siz ve yetkili kullanıcıların hesapları üzerinden erişebileceği şekilde sistem içerisinde güvenle tutulur.</p>
                <p><strong>3. Paylaşılan Takvimlerde Özel Bilgiler Gösterilmez:</strong><br>TakvimTakip üzerinden oluşturduğunuz iCal bağlantısı veya müsaitlik/paylaşım linklerinde, müşterilerinize ait kişisel bilgiler paylaşılmaz. Bu bağlantılar yalnızca paylaşılması gereken müsaitlik ve takvim bilgilerini göstermek amacıyla kullanılır.</p>
                <p><strong>4. Verilerinizi Pazarlama Amacıyla Satmıyoruz:</strong><br>Kişisel verileriniz reklam, pazarlama veya ticari amaçlarla üçüncü taraflara satılmaz veya kiralanmaz.</p>
                <p><strong>5. Verilerinize Erişim Kontrol Altındadır:</strong><br>TakvimTakip içerisindeki müşteri ve rezervasyon bilgileri, yalnızca ilgili hesap üzerinden yetkili kullanıcıların erişimine açıktır. Hesabınıza ait bilgilerin korunması için güvenli oturum açma ve erişim kontrol mekanizmaları kullanılmaktadır.</p>
                <p><strong>6. Verileriniz Yedeklenir ve Korunur:</strong><br>Sistemde bulunan rezervasyon ve işletme bilgilerinin kaybolmasını önlemek amacıyla düzenli veri yedekleme ve sistem güvenliği uygulamaları kullanılmaktadır. Böylece donanım arızası veya teknik sorunlar gibi durumlarda veri kaybı riskinin azaltılması hedeflenmektedir.</p>
                <p><strong>7. Veri Aktarımı Güvenli Bir Şekilde Gerçekleştirilir:</strong><br>TakvimTakip ile cihazınız arasındaki veri iletişiminde güvenli bağlantı teknolojileri kullanılır. Böylece sisteme giriş bilgilerinizin ve sistemle gerçekleştirdiğiniz veri alışverişinin güvenliği korunmaya çalışılır.</p>
                <p><strong>8. Verileriniz Üzerinde Kontrol:</strong><br>TakvimTakip hesabınızda bulunan işletme ve müşteri bilgilerinizi kolayca yönetebilirsiniz. İhtiyaç duyduğunuzda bilgilerinizi güncelleyebilir veya hesabınızdaki verileri silebilirsiniz.</p>
                <p><strong>9. KVKK'ya Uygunluk:</strong><br>TakvimTakip olarak kişisel verilerin işlenmesi süreçlerinde 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında gerekli yükümlülüklere uymayı hedefliyoruz.</p>
            </div>
        `;
    } else if (type === 'terms') {
        title.innerText = 'Kullanım Koşulları';
        body.innerHTML = `
            <p>TakvimTakip hizmetini kullanarak aşağıdaki şartları kabul etmiş sayılırsınız:</p>
            <p><strong>1. Hizmet Amacı:</strong> TakvimTakip, konaklama işletmelerinin müsaitlik takvimini yönetmesini sağlayan bir SaaS platformudur.</p>
            <p><strong>2. Kullanıcı Sorumluluğu:</strong> Hesabınıza eklenen konut verilerinin ve harici iCal bağlantılarının doğruluğu kullanıcı sorumluluğundadır.</p>
            <p><strong>3. Hizmet Kesintisizliği:</strong> Platform %99.9 sunucu erişilebilirliği hedefiyle çalışmaktadır.</p>
        `;
    } else if (type === 'kvkk') {
        title.innerText = 'KVKK Aydınlatma Metni';
        body.innerHTML = `
            <p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca kişisel verileriniz işlenmektedir:</p>
            <p>Veri sorumlusu sıfatıyla TakvimTakip, kullanıcı verilerini kanunda belirtilen ilkeler doğrultusunda güvenli sunucularda saklar.</p>
            <p>Veri sahipleri diledikleri zaman hesap kapatma, veri güncelleme ve silme hakkına sahiptir.</p>
        `;
    }

    modal.classList.remove('hidden');
}

function closeLegalModal() {
    document.getElementById('legalModal').classList.add('hidden');
}

// Initialize Interactive Demos on Load
document.addEventListener('DOMContentLoaded', function() {
    renderHeroCalendar();
    renderShowcaseGrid();
});
</script>

<!--Start of Tawk.to Script-->
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
<!--End of Tawk.to Script-->

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/905437161546?text=Merhaba,%20TakvimTakip%20hakk%C4%B1nda%20bilgi%20almak%20istiyorum." 
   target="_blank" 
   rel="noopener noreferrer" 
   class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-2xl shadow-emerald-500/40 hover:scale-110 active:scale-95 transition-all duration-300 group"
   title="WhatsApp ile İletişime Geçin">
    <i class="fa-brands fa-whatsapp text-3xl"></i>
    <span class="absolute right-16 bg-slate-900 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
        WhatsApp'tan Yazın
    </span>
</a>

</body>
</html>
