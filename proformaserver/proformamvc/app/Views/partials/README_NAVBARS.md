# Guía de Navbars - ProformaMVC

Este proyecto cuenta con **3 versiones de navbar** con diferentes estilos visuales. Puedes elegir el que más te guste o cambiar entre ellos fácilmente.

---

## 📱 Navbars Disponibles

### 1. **navbar.php** - Original Ultra Premium
**Estilo**: Botón central flotante épico con glassmorphism y animaciones

**Características**:
- ✨ Botón central flotante que rota y flota
- 🎨 Colores que cambian según la sección (azul, verde, naranja)
- 💫 Animaciones de pulso y ondas
- 🔔 Soporte para badges de notificaciones
- 🎯 Feedback háptico en móviles
- 📍 Indicador de sección activa con puntos

**Cuándo usar**: Para una app moderna y llamativa con énfasis en el botón de creación.

---

### 2. **navbar_v2.php** - Minimalista con Barra Deslizante
**Estilo**: Diseño limpio con barra indicadora animada en la parte superior

**Características**:
- 📊 Barra indicadora superior que se desliza entre secciones
- 🎯 Grid de 4 columnas equilibrado
- 🔄 Animaciones suaves de transición
- 🎪 Efecto ripple al hacer clic
- 🎈 Iconos con efecto bounce al activarse
- 🚀 FAB (Floating Action Button) en esquina inferior derecha
- 🌟 Etiquetas de texto siempre visibles

**Cuándo usar**: Para una interfaz más organizada y profesional con mejor legibilidad.

---

### 3. **navbar_v3.php** - Dock Estilo macOS/iOS
**Estilo**: Dock con efecto de magnificación al estilo macOS

**Características**:
- 🔍 Efecto de magnificación al pasar el cursor (se agrandan los iconos)
- 🪞 Reflejo inferior tipo espejo
- 🌈 Iconos con gradientes de colores
- 💎 Glassmorphism extremo con blur intenso
- ⚡ Separador visual entre navegación y acciones
- 🎨 Botón "Nuevo" con efecto especial arcoíris
- 📱 Tooltips flotantes al hacer hover
- ✨ Badges de notificación (comentados, listos para usar)

**Cuándo usar**: Para una experiencia premium tipo desktop/tablet con interacciones elegantes.

---

## 🔧 Cómo Cambiar de Navbar

### Paso 1: Editar el Layout Principal

Abre el archivo: `/app/Views/layouts/main.php`

Busca esta línea (cerca de la línea 64):

```php
<?php include __DIR__ . '/../partials/navbar.php'; ?>
```

### Paso 2: Cambiar por el navbar deseado

**Opción A - Navbar Original (Ultra Premium)**:
```php
<?php include __DIR__ . '/../partials/navbar.php'; ?>
```

**Opción B - Navbar Minimalista (v2)**:
```php
<?php include __DIR__ . '/../partials/navbar_v2.php'; ?>
```

**Opción C - Navbar Dock macOS (v3)**:
```php
<?php include __DIR__ . '/../partials/navbar_v3.php'; ?>
```

### Paso 3: Guardar y refrescar

Guarda el archivo y recarga la página en tu navegador. ¡Listo!

---

## 🎨 Personalización

### Cambiar Colores

Cada navbar tiene sus colores definidos en PHP. Busca estas secciones:

#### navbar.php
```php
// Líneas 16-34
if (strpos($currentPath, '/clientes') !== false) {
    $btn_color_class = 'from-emerald-500 to-green-600';
    // Cambia estos valores
}
```

#### navbar_v2.php
```php
// Líneas 9-39
$sections = [
    'home' => [
        'gradient' => 'from-slate-600 to-slate-800', // Cambia aquí
        // ...
    ],
    // ...
];
```

#### navbar_v3.php
```php
// Líneas 9-51
$dock_items = [
    'home' => [
        'color_from' => '#64748b', // Cambia aquí
        'color_to' => '#475569',   // Y aquí
        // ...
    ],
    // ...
];
```

---

## 🔗 Modificar Links

Para cambiar a dónde apuntan los botones, edita las URLs en cada navbar:

```php
// Ejemplo en navbar.php (línea 164)
<a href="<?= url('/') ?>" ...>  <!-- Botón Home -->

// Ejemplo en navbar_v2.php (líneas 11-39)
'url' => url('/proformas'),  <!-- Link a Proformas -->

// Ejemplo en navbar_v3.php (líneas 11-51)
'url' => url('/clientes'),   <!-- Link a Clientes -->
```

---

## ➕ Agregar Nuevos Botones

### navbar.php (Original)

1. Busca la línea 164 (sección de botones)
2. Agrega un nuevo botón antes del botón central:

```php
<!-- Nuevo botón -->
<a href="<?= url('/mi-nueva-seccion') ?>"
   class="nav-btn group relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 hover:bg-slate-100/80 hover:scale-105 active:scale-95"
   aria-label="Mi Sección">
    <i class="nav-icon ph-bold ph-star text-xl text-slate-500 group-hover:text-purple-600"></i>
</a>
```

### navbar_v2.php (Minimalista)

1. Busca el array `$sections` (línea 9)
2. Agrega una nueva sección:

```php
'mi_seccion' => [
    'url' => url('/mi-seccion'),
    'icon' => 'ph-star',
    'label' => 'Nueva',
    'color' => 'purple',
    'gradient' => 'from-purple-500 to-purple-700',
    'active' => (strpos($currentPath, '/mi-seccion') !== false),
],
```

3. **IMPORTANTE**: Si agregas más de 4 botones, cambia `grid-cols-4` por `grid-cols-5` en la línea 206.

### navbar_v3.php (Dock)

1. Busca el array `$dock_items` (línea 9)
2. Agrega un nuevo item antes del divider:

```php
'mi_item' => [
    'url' => url('/mi-seccion'),
    'icon' => 'ph-star',
    'icon_fill' => 'ph-star',
    'label' => 'Nueva',
    'color_from' => '#a855f7',
    'color_to' => '#9333ea',
    'bg_class' => 'from-purple-500 to-purple-700',
    'active' => (strpos($currentPath, '/mi-seccion') !== false),
],
```

---

## 🎯 Iconos Disponibles

Los navbars usan **Phosphor Icons**. Algunos iconos útiles:

- `ph-house` - Casa/Inicio
- `ph-file-text` - Documentos/Proformas
- `ph-users` / `ph-users-three` - Usuarios/Clientes
- `ph-package` - Paquete/Inventario
- `ph-plus` - Más/Agregar
- `ph-gear` - Configuración
- `ph-chart-bar` - Gráficas/Estadísticas
- `ph-bell` - Notificaciones
- `ph-shopping-cart` - Carrito
- `ph-star` - Favoritos

Ver todos en: https://phosphoricons.com/

**Tip**: Usa `ph-bold` para trazo grueso, `ph-fill` para relleno sólido.

---

## 🐛 Solución de Problemas

### El navbar no aparece
- ✅ Verifica que el archivo esté en `/app/Views/partials/`
- ✅ Revisa que el `include` en `main.php` tenga la ruta correcta
- ✅ Asegúrate que la función `url()` esté definida en helpers

### Los colores no se ven bien
- ✅ Verifica que estés usando clases de Tailwind CSS válidas
- ✅ Si usas colores hex (navbar_v3), asegúrate de incluir el `#`

### El botón central no funciona
- ✅ Revisa que la URL del botón esté correcta
- ✅ Verifica que la ruta exista en tu archivo `routes.php` o Router

### Los iconos no aparecen
- ✅ Asegúrate que Phosphor Icons esté cargado en `main.php`:
```html
<script src="https://unpkg.com/@phosphor-icons/web"></script>
```

---

## 🎨 Comparación Visual

| Característica | navbar.php | navbar_v2.php | navbar_v3.php |
|----------------|-----------|--------------|--------------|
| **Estilo** | Futurista | Minimalista | Premium macOS |
| **Botón Central** | Flotante | FAB esquina | Integrado |
| **Indicador Activo** | Puntos | Barra superior | Punto inferior |
| **Hover Effect** | Escala | Escala + ripple | Magnificación |
| **Mejor para** | Apps modernas | Apps corporativas | Apps elegantes |
| **Complejidad** | Media | Baja | Alta |
| **Mobile-friendly** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Desktop** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

---

## 💡 Recomendaciones

### Para Móviles
- **navbar.php** o **navbar_v2.php** - Son más táctiles y fáciles de usar con el pulgar

### Para Tablets
- **navbar_v3.php** - El efecto de magnificación funciona excelente con el cursor

### Para PWA (Progressive Web App)
- **navbar_v2.php** - Más parecido a apps nativas con su FAB

### Para Máxima Personalización
- **navbar.php** - Tiene más opciones de colores contextuales

---

## 🚀 Próximos Pasos

1. **Prueba los 3 navbars** cambiando el include en `main.php`
2. **Elige tu favorito** según el estilo de tu app
3. **Personaliza los colores** para que coincidan con tu marca
4. **Agrega badges** de notificación si lo necesitas (código comentado incluido)

---

*Documentación creada para ProformaMVC - Última actualización: 28 de diciembre de 2024*
