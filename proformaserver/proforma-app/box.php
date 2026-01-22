<!DOCTYPE html>

<html lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
<title>Noche Corporativa - Live Event</title>
<!-- Tailwind CSS v3 with Plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          colors: {
            brand: {
              black: '#000000',
              dark: '#121212',
              card: '#1c1c1e',
              yellow: '#FFD700', // Bright Gold/Yellow
              gray: '#8e8e93',
            }
          }
        }
      }
    }
  </script>
<style data-purpose="utilities">
    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
      -ms-overflow-style: none;  /* IE and Edge */
      scrollbar-width: none;  /* Firefox */
    }
    
    /* Custom gradient for the hero card to fade images into background */
    .hero-gradient {
      background: linear-gradient(to top, #1c1c1e 10%, transparent 50%);
    }

    /* Glassmorphism for Navbar */
    .glass-nav {
      background: rgba(20, 20, 20, 0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
  </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-brand-black text-white font-sans antialiased pb-40">
<!-- BEGIN: MainHeader -->
<header class="sticky top-0 z-40 bg-brand-black/90 backdrop-blur-sm px-4 pt-12 pb-4 flex justify-between items-center">
<!-- Event Info -->
<div class="flex items-center gap-3">
<!-- Icon Placeholder -->
<div class="w-8 h-8 rounded bg-brand-yellow flex items-center justify-center text-black">
<svg class="w-5 h-5" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M4.5 4.5a3 3 0 00-3 3v9a3 3 0 003 3h8.25a3 3 0 003-3v-9a3 3 0 00-3-3H4.5zM19.5 4.5h-1.5a.75.75 0 000 1.5h1.5a1.5 1.5 0 011.5 1.5v1.5a.75.75 0 001.5 0v-1.5a3 3 0 00-3-3z"></path>
</svg>
</div>
<div class="flex flex-col">
<span class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">Live Event</span>
<h1 class="text-xl font-bold leading-none text-2xl">Noche Corporativa</h1>
</div>
</div>
<!-- User Actions -->
<div class="flex items-center gap-4">
<button class="relative text-gray-400 hover:text-white transition">
<svg class="w-6 h-6" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9a2.25 2.25 0 104.496 0 25.057 25.057 0 01-4.496 0z" fill-rule="evenodd"></path>
</svg>
<span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border border-black"></span>
</button>
<!-- Avatar Placeholder -->
<div class="w-9 h-9 rounded-full bg-gray-700 overflow-hidden border border-gray-600">
<img alt="Admin" class="w-full h-full object-cover opacity-80" onerror="this.style.display='none'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSe7WNVQZhY9OyU6HQ_W0PZ8kPAf2d2FA-j5zqpoQVGIRRIwcoUrhniAo6BBhR-rBSzyeaJ52T9_7wxj1lDSRDTkcg3PThPkYBJ4xvEnBS9BQlvMPRUJ_oMgwYkI4VlQhAN62g0GP3Q9y6BibrpysgGJLnU2jSJ313qqtlXLUK053fatGiGCmdaa0_7JI7k7w_IRLxPa48PF-_RedWS4aFZF4dIohoPTQcVvwzxhbv62tSxQEtwanD0-6FYOYhxx0bCbT6tHVEHk0s"/>
<div class="w-full h-full flex items-center justify-center text-xs text-gray-400 bg-gray-800">U</div>
</div>
</div>
</header>
<!-- END: MainHeader -->
<!-- BEGIN: CategoryTabs -->
<nav class="px-4 mb-6">
<ul class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
<li>
<button class="bg-white text-black px-6 py-2 rounded-full text-sm font-bold whitespace-nowrap">
          Todos
        </button>
</li>
<li>
<button class="bg-brand-card text-gray-400 px-6 py-2 rounded-full text-sm font-medium whitespace-nowrap border border-white/5">
          Peso Pesado
        </button>
</li>
<li>
<button class="bg-brand-card text-gray-400 px-6 py-2 rounded-full text-sm font-medium whitespace-nowrap border border-white/5">
          Sector Tech
        </button>
</li>
<li>
<button class="bg-brand-card text-gray-400 px-6 py-2 rounded-full text-sm font-medium whitespace-nowrap border border-white/5">
          Exhibición
        </button>
</li>
</ul>
</nav>
<!-- END: CategoryTabs -->
<main class="space-y-8 px-4">
<!-- BEGIN: HeroEvent -->
<section>
<div class="flex justify-between items-end mb-4">
<h2 class="text-2xl font-bold">Evento Estelar</h2>
<a class="text-xs text-gray-400 hover:text-white transition" href="#">Ver todo -&gt;</a>
</div>
<!-- Main Match Card -->
<article class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden bg-brand-card border border-white/5">
<!-- Background Gradient -->
<div class="absolute inset-0 bg-gradient-to-br from-gray-900 to-black z-0"></div>
<!-- Fighters Container -->
<div class="relative z-10 w-full h-full flex pt-4">
<!-- Fighter Left -->
<div class="w-1/2 h-full relative"><img alt="Carlos CEO" class="w-full h-full object-cover object-top opacity-90" onerror="this.src='https://placehold.co/300x400/333/666?text=Fighter+1'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSe7WNVQZhY9OyU6HQ_W0PZ8kPAf2d2FA-j5zqpoQVGIRRIwcoUrhniAo6BBhR-rBSzyeaJ52T9_7wxj1lDSRDTkcg3PThPkYBJ4xvEnBS9BQlvMPRUJ_oMgwYkI4VlQhAN62g0GP3Q9y6BibrpysgGJLnU2jSJ313qqtlXLUK053fatGiGCmdaa0_7JI7k7w_IRLxPa48PF-_RedWS4aFZF4dIohoPTQcVvwzxhbv62tSxQEtwanD0-6FYOYhxx0bCbT6tHVEHk0s"/>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent z-10"></div>
<div class="absolute bottom-0 left-0 w-full p-4 z-20">
<div class="inline-block bg-brand-yellow text-black text-[10px] font-bold px-1.5 py-0.5 rounded mb-1">VS</div>
<h3 class="text-lg font-bold leading-tight drop-shadow-md">Carlos 'CEO'</h3>
<p class="text-xs text-gray-400">TechGlobal</p>
</div></div>
<!-- VS Center Text (Stylized) -->
<div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-30 flex items-center justify-center w-24 h-24" style="background: radial-gradient(circle, rgba(255, 215, 0, 0.15) 0%, transparent 70%);">
<span class="text-4xl font-black italic text-white/30 tracking-tighter drop-shadow-lg">VS</span>
</div>
<!-- Fighter Right -->
<div class="w-1/2 h-full relative"><img alt="Maria Shark" class="w-full h-full object-cover object-top opacity-90" onerror="this.src='https://placehold.co/300x400/333/666?text=Fighter+2'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB2CTrCAfv55KRI8i-Qq6SDgG1LCEamSzOmODHfMPEML8EfxEHgWZEvDHqOqDKaJ0iHICcFxJRdyC8rcE8JnlW7aDg2s7edGWBfaYewTsr1UDSJngamPUuY5NFLpvmgFz8d7bqxVu8UWes9QO0E_KV35D_b_N1AMDRwFPIWudtkAK-_houwAwLFMj6i1oN3suoZDGHfi9Bv20U_uhHrehsI7cuwE2uA5HmCU7BqjUYklb2e3d76twvR_JTqNLPeA-nv7Ywwi6PM-EMN"/>
<div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent z-10"></div>
<div class="absolute bottom-0 right-0 w-full p-4 text-right z-20">
<h3 class="text-lg font-bold leading-tight drop-shadow-md">Maria 'Shark'</h3>
<p class="text-xs text-gray-400">FinCorp</p>
</div></div>
</div>
</article>
</section>
<!-- END: HeroEvent -->
<!-- BEGIN: RecentFighters -->
<section>
<div class="mb-4">
<h2 class="text-xl font-bold">Últimos Inscritos</h2>
<p class="text-xs text-gray-500 mt-1">Luchadores ejecutivos destacados de esta temporada</p>
</div>
<div class="grid grid-cols-4 gap-3">
<!-- Fighter 1 -->
<div class="aspect-square bg-brand-card overflow-hidden border border-white/5 relative rounded-full">
<img class="w-full h-full object-cover" onerror="this.src='https://placehold.co/150x150/222/555'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSe7WNVQZhY9OyU6HQ_W0PZ8kPAf2d2FA-j5zqpoQVGIRRIwcoUrhniAo6BBhR-rBSzyeaJ52T9_7wxj1lDSRDTkcg3PThPkYBJ4xvEnBS9BQlvMPRUJ_oMgwYkI4VlQhAN62g0GP3Q9y6BibrpysgGJLnU2jSJ313qqtlXLUK053fatGiGCmdaa0_7JI7k7w_IRLxPa48PF-_RedWS4aFZF4dIohoPTQcVvwzxhbv62tSxQEtwanD0-6FYOYhxx0bCbT6tHVEHk0s"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
</div>
<!-- Fighter 2 -->
<div class="aspect-square bg-brand-card overflow-hidden border border-white/5 relative rounded-full">
<img class="w-full h-full object-cover" onerror="this.src='https://placehold.co/150x150/222/555'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB2CTrCAfv55KRI8i-Qq6SDgG1LCEamSzOmODHfMPEML8EfxEHgWZEvDHqOqDKaJ0iHICcFxJRdyC8rcE8JnlW7aDg2s7edGWBfaYewTsr1UDSJngamPUuY5NFLpvmgFz8d7bqxVu8UWes9QO0E_KV35D_b_N1AMDRwFPIWudtkAK-_houwAwLFMj6i1oN3suoZDGHfi9Bv20U_uhHrehsI7cuwE2uA5HmCU7BqjUYklb2e3d76twvR_JTqNLPeA-nv7Ywwi6PM-EMN"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
</div>
<!-- Fighter 3 -->
<div class="aspect-square bg-brand-card overflow-hidden border border-white/5 relative rounded-full">
<img class="w-full h-full object-cover" onerror="this.src='https://placehold.co/150x150/222/555'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSe7WNVQZhY9OyU6HQ_W0PZ8kPAf2d2FA-j5zqpoQVGIRRIwcoUrhniAo6BBhR-rBSzyeaJ52T9_7wxj1lDSRDTkcg3PThPkYBJ4xvEnBS9BQlvMPRUJ_oMgwYkI4VlQhAN62g0GP3Q9y6BibrpysgGJLnU2jSJ313qqtlXLUK053fatGiGCmdaa0_7JI7k7w_IRLxPa48PF-_RedWS4aFZF4dIohoPTQcVvwzxhbv62tSxQEtwanD0-6FYOYhxx0bCbT6tHVEHk0s"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
</div>
<!-- Fighter 4 -->
<div class="aspect-square bg-brand-card overflow-hidden border border-white/5 relative rounded-full">
<img class="w-full h-full object-cover" onerror="this.src='https://placehold.co/150x150/222/555'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB2CTrCAfv55KRI8i-Qq6SDgG1LCEamSzOmODHfMPEML8EfxEHgWZEvDHqOqDKaJ0iHICcFxJRdyC8rcE8JnlW7aDg2s7edGWBfaYewTsr1UDSJngamPUuY5NFLpvmgFz8d7bqxVu8UWes9QO0E_KV35D_b_N1AMDRwFPIWudtkAK-_houwAwLFMj6i1oN3suoZDGHfi9Bv20U_uhHrehsI7cuwE2uA5HmCU7BqjUYklb2e3d76twvR_JTqNLPeA-nv7Ywwi6PM-EMN"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
</div>
</div>
</section>
<!-- END: RecentFighters -->
<!-- BEGIN: ScheduledFights -->
<section>
<div class="mb-4">
<h2 class="text-xl font-bold">Peleas Pactadas</h2>
<p class="text-xs text-gray-500 mt-1">Unóximo manicinos procinon peleadas fightos</p>
</div>
<div class="flex flex-col gap-3">
<!-- Fight Card 1 -->
<article class="bg-brand-card p-3 rounded-2xl flex items-center justify-between border border-white/5">
<!-- Fighter A -->
<div class="flex items-center gap-3 flex-1">
<div class="w-12 h-12 bg-gray-700 overflow-hidden rounded-full">
<img class="w-full h-full object-cover" onerror="this.src='https://placehold.co/100x100/333/777'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSe7WNVQZhY9OyU6HQ_W0PZ8kPAf2d2FA-j5zqpoQVGIRRIwcoUrhniAo6BBhR-rBSzyeaJ52T9_7wxj1lDSRDTkcg3PThPkYBJ4xvEnBS9BQlvMPRUJ_oMgwYkI4VlQhAN62g0GP3Q9y6BibrpysgGJLnU2jSJ313qqtlXLUK053fatGiGCmdaa0_7JI7k7w_IRLxPa48PF-_RedWS4aFZF4dIohoPTQcVvwzxhbv62tSxQEtwanD0-6FYOYhxx0bCbT6tHVEHk0s"/>
</div>
<div>
<p class="text-[10px] text-gray-400">David</p>
<h4 class="font-bold text-sm leading-tight">'The Hammer'</h4>
<p class="text-[10px] text-gray-500 truncate max-w-[80px]">VP of Sales</p>
</div>
</div>
<!-- VS -->
<div class="px-2">
<span class="text-xl font-black italic text-white text-5xl">VS</span>
</div>
<!-- Fighter B -->
<div class="flex items-center gap-3 flex-1 justify-end text-right">
<div>
<p class="text-[10px] text-gray-400">Javier</p>
<h4 class="font-bold text-sm leading-tight">'The Wall'</h4>
<p class="text-[10px] text-gray-500">BuildIt It</p>
</div>
<div class="w-12 h-12 bg-gray-700 overflow-hidden rounded-full">
<img class="w-full h-full object-cover" onerror="this.src='https://placehold.co/100x100/333/777'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB2CTrCAfv55KRI8i-Qq6SDgG1LCEamSzOmODHfMPEML8EfxEHgWZEvDHqOqDKaJ0iHICcFxJRdyC8rcE8JnlW7aDg2s7edGWBfaYewTsr1UDSJngamPUuY5NFLpvmgFz8d7bqxVu8UWes9QO0E_KV35D_b_N1AMDRwFPIWudtkAK-_houwAwLFMj6i1oN3suoZDGHfi9Bv20U_uhHrehsI7cuwE2uA5HmCU7BqjUYklb2e3d76twvR_JTqNLPeA-nv7Ywwi6PM-EMN"/>
</div>
</div>
</article>
</div>
</section>
<!-- END: ScheduledFights -->
<!-- BEGIN: VipSection -->
<section class="mt-4">
<div class="relative w-full rounded-3xl overflow-hidden bg-yellow-900 border border-brand-yellow/30">
<!-- Background Image/Effect -->
<img class="absolute inset-0 w-full h-full object-cover opacity-60 mix-blend-screen" onerror="this.style.background='radial-gradient(circle at center, #ffd700 0%, #5d4000 100%)'" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIVqrN5TsjG8bAmkn1uf_mfVKB-XUj0uPjjknbSMNn82UFz2RVpkdx79ScUzYB_OhoiO7HbfU51XDNUZQlS5LmjSGJHo_3tbuHEPSDxkTeeaGkOy9BsPpzLRZqJNSOobqehMIzF4Rdg06H6wmBtCi5OsVQLypt6W2v6_DbJOjcQJ5jtZyEAPqwimkC_4_65DHKskqbhHvfChgiagHyz69RT9EcpX1puopo0j-cSTH_AHHRxe1VqKb01ZjJwwQUXvDqLg2ydO5uCfGZ"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
<!-- Content -->
<div class="relative z-10 flex flex-col items-center justify-center py-10 px-6 text-center">
<div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center mb-4 text-brand-yellow border border-brand-yellow/50 shadow-[0_0_15px_rgba(255,215,0,0.3)]">
<svg class="w-6 h-6" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M1.5 6.375c0-1.036.84-1.875 1.875-1.875h17.25c1.035 0 1.875.84 1.875 1.875v3.026a.75.75 0 01-.375.65 2.249 2.249 0 000 3.898.75.75 0 01.375.65v3.026c0 1.035-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 17.625v-3.026a.75.75 0 01.374-.65 2.249 2.249 0 000-3.898.75.75 0 01-.374-.65V6.375zm15-1.125a.75.75 0 01.75.75v.75a.75.75 0 01-1.5 0V6a.75.75 0 01.75-.75zm.75 4.5a.75.75 0 00-1.5 0v.75a.75.75 0 001.5 0v-.75zm-.75 3a.75.75 0 01.75.75v.75a.75.75 0 01-1.5 0v-.75a.75.75 0 01.75-.75z" fill-rule="evenodd"></path>
</svg>
</div>
<h3 class="text-2xl font-bold text-brand-yellow mb-1">Entradas VIP</h3>
<p class="text-sm text-gray-300 mb-6 max-w-[200px]">Acceso exclusivo al ringside y afterparty.</p>
<button class="bg-black text-white text-sm font-bold py-3 px-8 rounded-full border border-white/20 hover:bg-gray-900 transition shadow-lg">
            Comprar Ahora
          </button>
</div>
</div>
</section>
<!-- END: VipSection -->
</main>
<!-- BEGIN: BottomNavigation -->
<div style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 400px; background-color: #222; border-radius: 40px; height: 70px; display: flex; align-items: center; justify-content: space-around; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 1000;">
<!-- Inicio -->
<a class="flex flex-col items-center gap-1 w-12" href="#"><svg class="w-6 h-6 text-brand-yellow" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z"></path>
<path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"></path>
</svg>
<span class="text-[9px] font-medium text-brand-yellow">Inicio</span></a>
<!-- Evento -->
<a class="flex flex-col items-center gap-1 w-12 text-gray-400 hover:text-white transition" href="#">
<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
<span class="text-[9px] font-medium">Evento</span>
</a>
<!-- Center FAB -->
<div class="relative w-14 h-14 flex items-center justify-center -mt-10 bg-brand-yellow rounded-full shadow-[0_4px_15px_rgba(255,215,0,0.4)] border-4 border-black z-50">
<svg class="w-6 h-6 text-black" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M1.5 6.375c0-1.036.84-1.875 1.875-1.875h17.25c1.035 0 1.875.84 1.875 1.875v3.026a.75.75 0 01-.375.65 2.249 2.249 0 000 3.898.75.75 0 01.375.65v3.026c0 1.035-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 17.625v-3.026a.75.75 0 01.374-.65 2.249 2.249 0 000-3.898.75.75 0 01-.374-.65V6.375zm15-1.125a.75.75 0 01.75.75v.75a.75.75 0 01-1.5 0V6a.75.75 0 01.75-.75zm.75 4.5a.75.75 0 00-1.5 0v.75a.75.75 0 001.5 0v-.75zm-.75 3a.75.75 0 01.75.75v.75a.75.75 0 01-1.5 0v-.75a.75.75 0 01.75-.75z" fill-rule="evenodd"></path>
</svg>
</div>
<!-- Peleadores -->
<a class="flex flex-col items-center gap-1 w-12 text-gray-400 hover:text-white transition" href="#">
<svg class="w-6 h-6" fill="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"></path>
</svg>
<span class="text-[9px] font-medium">Peleadores</span>
</a>
<!-- Entradas -->
<a class="flex flex-col items-center gap-1 w-12 text-gray-400 hover:text-white transition" href="#">
<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
<path d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
<span class="text-[9px] font-medium">Entradas</span>
</a>
</div>
<!-- END: BottomNavigation -->
</body></html>