<?php
// head.php - CABECERA HTML CENTRALIZADA
?>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
  
  <title><?= $page_title ?? 'Tradimacova App' ?></title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <?php if(isset($extra_css)) echo $extra_css; ?>

  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
    
    /* Utilitarios */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    
    /* Animaciones PRO */
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
    .animate-fade-in-down { animation: fadeInDown 0.3s ease-out forwards; }
    
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Glassmorphism Header (Tu diseño) */
    .glass-header { 
        background: rgba(15, 23, 42, 0.95); 
        backdrop-filter: blur(12px); 
        border-bottom: 1px solid rgba(255,255,255,0.1); 
    }
    
    /* Checkbox personalizado (Inventario Style) */
    .custom-checkbox:checked { 
        background-color: #2563EB; 
        border-color: #2563EB; 
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
    }
  </style>
</head>

