<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- SEO & Open Graph (WhatsApp/Facebook) -->
<title><?= $seoTitle ?? $appName ?></title>
<meta name="description" content="<?= htmlspecialchars($seoDesc ?? '') ?>">

<meta property="og:type" content="product">
<meta property="og:site_name" content="<?= htmlspecialchars($appName) ?>">
<meta property="og:title" content="<?= htmlspecialchars($seoTitle ?? '') ?>">
<meta property="og:description" content="<?= htmlspecialchars($seoDesc ?? '') ?>">
<meta property="og:image" content="<?= $seoImgUrl ?? '' ?>">
<meta property="og:image:width" content="800">
<meta property="og:image:height" content="800">
<meta property="og:url" content="<?= $urlActual ?? '' ?>">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($seoTitle ?? '') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($seoDesc ?? '') ?>">
<meta name="twitter:image" content="<?= $seoImgUrl ?? '' ?>">

<!-- ESTILOS -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Outfit', 'sans-serif'],
                },
                colors: {
                    dark: {
                        bg: '#0f172a', // Slate 900
                        card: '#1e293b', // Slate 800
                        surface: '#334155' // Slate 700
                    }
                }
            }
        }
    }
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script>window.TOAST_THEME = 'modern';</script> <!-- Configuración de Tema -->
<script src="<?= asset('js/utils/toast.js') ?>"></script> <!-- Sistema Centralizado de Toast -->

<!-- ANIMATION LIBRARIES -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<style>
    body { font-family: 'Outfit', sans-serif; }
    
    /* Animation Utilities */
    .animate-float { animation: float 6s ease-in-out infinite; }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    
    .animate-fade-in-right { animation: fadeInRight 0.4s ease-out forwards; }
    @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    
    .animate-fade-up { animation: fadeUp 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
    @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    
    /* Gradient Animation */
    .animate-gradient-x {
        background-size: 200% 200%;
        animation: gradient-x 6s ease infinite;
    }
    @keyframes gradient-x {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    /* Glassmorphism Utilities */
    .glass-panel {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>
