# 🎨 INSTALACIÓN DEL SISTEMA DE TEMAS

## ✅ Estado Actual

**Archivos creados y listos:**
- ✓ ThemeHelper.php
- ✓ SettingsController.php
- ✓ Vista de configuración (settings/index.php)
- ✓ Tema Corporativo (home/index_corporate.php)
- ✓ Tema Vibrante (home/index_vibrant.php)
- ✓ HomeController modificado
- ✓ index.php detector de temas

## 🚀 PASOS PARA ACTIVAR (Solo 1 paso)

### Agregar estas 2 líneas a tus rutas:

Busca tu archivo de rutas (probablemente en `routes/web.php` o `config/routes.php`) y agrega:

```php
// Configuración de Temas
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/change-theme', 'SettingsController@changeTheme');
```

## 🎯 Cómo Probar

1. **Ir a la página principal:**
   ```
   http://localhost/proformaMVC/
   ```
   Deberías ver el tema **Corporativo** (discreto, gris-azulado)

2. **Ir a configuración:**
   ```
   http://localhost/proformaMVC/settings
   ```

3. **Cambiar al tema Vibrante:**
   - Click en la tarjeta "Vibrante"
   - Verás el cambio instantáneo

4. **Volver al home:**
   ```
   http://localhost/proformaMVC/
   ```
   Ahora verás el tema **Vibrante** (colorido con efectos)

## 🎨 Temas Disponibles

### Corporativo (Default)
- Colores: Slate (gris-azulado)
- Estilo: Profesional y discreto
- Logo: 16x16 (grande)

### Vibrante
- Colores: Azul, púrpura, verde, naranja
- Estilo: Moderno con animaciones
- Efectos: Partículas flotantes, glassmorphism
- Logo: 16x16 (grande)

## 🔧 Verificar Funcionamiento

### Test 1: Ver tema actual
```php
// En cualquier lugar de tu código
session_start();
echo $_SESSION['app_theme'] ?? 'corporate';
```

### Test 2: Cambiar tema manualmente
```php
// En cualquier controlador o vista
session_start();
$_SESSION['app_theme'] = 'vibrant';
// Refrescar la página
```

## 📱 Agregar Botón de Configuración (Opcional)

### En el Header (app/Views/partials/header.php)

Después de la línea 290 (después del botón de acción), agregar:

```php
<!-- Botón de Configuración -->
<a href="<?= url('/settings') ?>"
   class="neo-btn group w-9 h-9 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 backdrop-blur-md border border-white/10 flex-shrink-0">
    <i class="ph-bold ph-gear text-white text-lg group-hover:rotate-45 transition-transform duration-500"></i>
</a>
```

### En cualquier menú:

```php
<a href="<?= url('/settings') ?>" class="menu-item">
    <i class="ph-bold ph-palette"></i>
    Personalizar Tema
</a>
```

## 🐛 Solución de Problemas

### Problema 1: "El home no se ve"
**Solución:**
1. Ir directamente a: `http://localhost/proformaMVC/`
2. Verificar que existan los archivos:
   - `app/Views/home/index.php`
   - `app/Views/home/index_corporate.php`
   - `app/Views/home/index_vibrant.php`

### Problema 2: "Error 404 en /settings"
**Solución:** Agregar las rutas mencionadas arriba

### Problema 3: "El tema no cambia"
**Solución:**
```php
// Limpiar la sesión y probar de nuevo
session_start();
unset($_SESSION['app_theme']);
```

## ✨ ¡Listo!

Ahora tu aplicación tiene:
- ✅ 2 temas profesionales
- ✅ Sistema de cambio instantáneo
- ✅ Interfaz visual para elegir
- ✅ Fácil de extender

Para ver el sistema funcionando:
1. Agrega las 2 rutas
2. Ve a `/settings`
3. Cambia entre temas
4. ¡Disfruta!

---

**Nota:** El tema se guarda en la sesión, por lo que se mantendrá mientras el navegador esté abierto.