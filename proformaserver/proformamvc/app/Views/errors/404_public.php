<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producto No Encontrado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="max-w-md w-full text-center space-y-6">
        <!-- Ilustración / Icono -->
        <div class="relative w-32 h-32 mx-auto mb-6">
            <div class="absolute inset-0 bg-indigo-100 rounded-full animate-pulse"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="ph-duotone ph-package text-6xl text-indigo-300"></i>
            </div>
            <div class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg border border-slate-100">
                <i class="ph-bold ph-warning-circle text-2xl text-amber-500"></i>
            </div>
        </div>

        <!-- Mensaje -->
        <div class="space-y-2">
            <h1 class="text-2xl font-bold text-slate-800">Producto No Disponible</h1>
            <p class="text-slate-500 text-sm leading-relaxed">
                El enlace que buscas no existe, ha expirado o el producto ha sido retirado de nuestra lista pública.
            </p>
        </div>

        <!-- Decoración -->
        <div class="w-16 h-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full mx-auto opacity-50"></div>

    </div>

</body>
</html>
