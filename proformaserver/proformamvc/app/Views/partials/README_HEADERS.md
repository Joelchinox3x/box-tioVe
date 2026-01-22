# 📱 Estilos de Header - Tradimacova App

Esta aplicación cuenta con **3 estilos diferentes de header** que el usuario puede seleccionar desde la página de Configuración.

## 🎨 Headers Disponibles

### 1️⃣ **Ultra Premium** (`header.php`) - Por defecto
**Estilo:** Glassmorphism futurista con efectos de cristal

**Características:**
- Fondo con efecto de vidrio esmerilado (glassmorphism)
- Gradiente sutil de slate con blur y transparencia
- Botones con efecto neón y sombras suaves
- Diseño espacioso y premium
- Altura: ~68px (sin búsqueda) / ~110px (con búsqueda)

**Ideal para:** Aplicaciones modernas que buscan un look premium y futurista

---

### 2️⃣ **Minimalista** (`header_v2.php`)
**Estilo:** Diseño limpio y compacto

**Características:**
- Gradiente simple de slate (135deg, #334155 → #1e293b)
- Espaciado reducido (py-3 en lugar de py-4)
- Botones más pequeños (w-8 h-8)
- Sombra sutil (0 4px 20px rgba(0,0,0,0.1))
- Sin efectos de blur ni glassmorphism
- Altura: ~60px (sin búsqueda) / ~100px (con búsqueda)

**Ideal para:** Usuarios que prefieren un diseño simple, limpio y con menor uso de recursos visuales

---

### 3️⃣ **Modern Gradient** (`header_v3.php`)
**Estilo:** Gradiente animado con efectos visuales dinámicos

**Características:**
- Gradiente animado que cambia de posición (background-size: 200%)
- Círculos flotantes con animación de movimiento
- Gradientes específicos por sección:
  - Proformas: blue-600 → indigo-700
  - Clientes: emerald-600 → cyan-700
  - Inventario: amber-600 → red-600
  - Home: slate-700 → slate-900
- Botones con efecto glass (backdrop-filter blur)
- Animaciones suaves en elementos flotantes
- Altura: ~68px (sin búsqueda) / ~110px (con búsqueda)

**Ideal para:** Aplicaciones dinámicas que quieren destacar con animaciones modernas

---

## 🔧 Cómo Funciona

### Sistema de Carga Dinámica

Todas las vistas usan el archivo `load_header.php` que automáticamente carga el header preferido del usuario:

```php
<?php
// En cualquier vista (ejemplo: clientes/index.php)
$title = 'Clientes';
$section = 'clientes';
$show_home = true;

// Carga dinámica del header según preferencia
include __DIR__ . '/../partials/load_header.php';
?>
```

### Almacenamiento de Preferencias

La preferencia se guarda en sesión:
- **Variable de sesión:** `$_SESSION['header_style']`
- **Valores válidos:** `'header'`, `'header_v2'`, `'header_v3'`
- **Default:** `'header'` (Ultra Premium)

### Cambiar el Header

Los usuarios pueden cambiar el header desde:
1. Ir a **Configuración** (engranaje en home)
2. Seleccionar el header deseado en el carrusel
3. Hacer clic en **"Guardar Cambios"**
4. El cambio se aplica inmediatamente

---

## 🛠️ Personalización

### Variables Disponibles en Headers

Todos los headers soportan las siguientes variables:

| Variable | Tipo | Descripción | Ejemplo |
|----------|------|-------------|---------|
| `$title` | string | Título principal | `'Clientes'` |
| `$subtitle` | string | Subtítulo opcional | `'24 registrados'` |
| `$back_url` | string | URL del botón volver | `url('/clientes')` |
| `$show_home` | bool | Mostrar botón home | `true` |
| `$action_button` | array | Botón de acción | `['url' => '...', 'icon' => 'ph-plus', 'label' => 'Nuevo']` |
| `$badge` | string | Etiqueta superior | `'Editando'` |
| `$search` | bool | Mostrar barra búsqueda | `true` |
| `$section` | string | Sección actual (para colores) | `'clientes'` |

### Colores por Sección

Cada header adapta sus colores según la sección:

- **Proformas:** Azul (blue-600)
- **Clientes:** Verde esmeralda (emerald-600)
- **Inventario:** Ámbar/Naranja (amber-600)
- **Home:** Gris pizarra (slate-700)

---

## 📝 Desarrollo

### Agregar un Nuevo Header

1. Crear archivo `header_v4.php` en `/app/Views/partials/`
2. Agregar a la lista de headers válidos en:
   - `SettingsController.php` → método `changeHeader()`
   - `load_header.php` → array `$valid_headers`
3. Agregar opción en `settings/index.php` en el carrusel de headers
4. Agregar estilo CSS para `.header-v4-radio:checked`

### Estructura Básica de un Header

```php
<?php
// Variables con valores por defecto
$title = $title ?? 'Tradimacova';
$subtitle = $subtitle ?? null;
$back_url = $back_url ?? null;
// ... más variables
?>

<style>
/* Estilos específicos del header */
</style>

<!-- Estructura del header -->
<div class="fixed top-0 left-0 right-0 z-30 flex justify-center">
    <header class="w-full max-w-md ...">
        <!-- Contenido del header -->
    </header>
</div>

<!-- Espaciador para evitar overlap con contenido -->
<div class="h-[68px]"></div>
```

---

## ✅ Checklist de Compatibilidad

Todos los headers deben cumplir:

- ✅ Responsive (max-w-md para móviles)
- ✅ Fixed positioning con z-30
- ✅ Centrado horizontal en pantallas grandes
- ✅ Soporte para todas las variables estándar
- ✅ Espaciador inferior apropiado
- ✅ Colores por sección configurados
- ✅ Accesibilidad (contraste, tamaños de toque)

---

## 🎯 Mejores Prácticas

1. **Mantén la consistencia:** Todos los headers deben tener la misma estructura básica de variables
2. **Optimiza las animaciones:** Usa `will-change` y `transform` para mejor performance
3. **Prueba en móviles:** Los headers se ven principalmente en pantallas pequeñas
4. **Considera el rendimiento:** Evita muchas animaciones simultáneas en `header_v3`
5. **Accesibilidad:** Mantén buenos contrastes de color y tamaños de botones táctiles (min 44x44px)

---

**Última actualización:** 2025-12-28
**Versión:** 1.0.0
