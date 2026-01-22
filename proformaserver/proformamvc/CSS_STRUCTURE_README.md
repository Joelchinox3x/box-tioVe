# 📁 Estructura de CSS del Proyecto

## ✅ Archivos CSS Centralizados

He organizado los estilos del proyecto en **archivos CSS centralizados** para evitar duplicación y facilitar el mantenimiento.

### Ubicación de archivos:
```
public/
├── css/
│   ├── global.css           ← ESTILOS GLOBALES (NUEVO)
│   └── notifications.css    ← Sistema de notificaciones
└── assets/
    └── css/
        └── estilos_pdf.css  ← Estilos para PDFs
```

## 📋 Archivos CSS

### 1. **global.css** - Estilos Globales (NUEVO) ⭐

Contiene todos los estilos reutilizables del proyecto:

#### ✨ Animaciones:
- `fadeInUp` - Aparecer con movimiento hacia arriba
- `fadeIn` - Aparecer simple
- `slide-in` / `slide-out` - Deslizar (notificaciones)
- `scale-in` - Escalar (modales)
- `pulse` - Pulsar (alertas)
- `spin` - Girar (loaders)

#### 🎨 Clases de Utilidad:
```css
.animate-fade-in-up
.animate-fade-in
.animate-slide-in
.animate-slide-out
.animate-scale-in
.animate-pulse
.animate-spin
```

#### 🎯 Componentes:
- `.glass-header` - Header con efecto glassmorphism
- `.glass-card` - Cards con efecto glassmorphism
- `.modal-overlay` - Overlay para modales
- `.custom-checkbox` - Checkboxes personalizados
- `.btn-primary` - Botón primario con gradiente
- `.btn-danger` - Botón de peligro con gradiente
- `.card` - Card base
- `.input-field` - Campo de entrada
- `.badge-*` - Badges de colores (success, error, warning, info)
- `.no-scrollbar` - Ocultar scrollbar

#### 📱 Responsive:
- Breakpoints optimizados para móviles
- Estilos adaptativos automáticos

### 2. **notifications.css** - Sistema de Notificaciones

Contiene estilos específicos para el sistema de notificaciones toast.

### 3. **estilos_pdf.css** - Estilos para PDFs

Estilos específicos para la generación de PDFs.

## 🚀 Cómo Usar

### En el Layout Principal (ya incluido):

```php
<!-- app/Views/layouts/main.php -->
<link rel="stylesheet" href="<?= asset('css/global.css') ?>">
<link rel="stylesheet" href="<?= asset('css/notifications.css') ?>">
```

### En tus vistas HTML:

```html
<!-- Usar clases de animación -->
<div class="animate-fade-in-up">
  Contenido con animación
</div>

<!-- Usar componentes -->
<button class="btn-primary">
  Guardar
</button>

<div class="card">
  Contenido de la tarjeta
</div>

<!-- Badges -->
<span class="badge badge-success">Activo</span>
<span class="badge badge-error">Error</span>

<!-- Inputs -->
<input type="text" class="input-field" placeholder="Nombre">

<!-- Modal -->
<div class="modal-overlay hidden">
  <!-- contenido del modal -->
</div>
```

## ✅ Ventajas de esta Estructura

### 1. **DRY (Don't Repeat Yourself)**
- No duplicar estilos en cada vista
- Cambiar un estilo en un solo lugar

### 2. **Mejor Performance**
- Los navegadores cachean los archivos CSS
- Menos código inline en HTML

### 3. **Mantenibilidad**
- Fácil encontrar y modificar estilos
- Consistencia visual en todo el proyecto

### 4. **Escalabilidad**
- Agregar nuevos componentes es sencillo
- Estructura clara y organizada

## 🔧 Antes vs Después

### ❌ ANTES (Malo):
```html
<!-- Vista 1 -->
<style>
  @keyframes fadeIn { ... }
  .animate-fade-in { ... }
</style>

<!-- Vista 2 -->
<style>
  @keyframes fadeIn { ... }
  .animate-fade-in { ... }
</style>

<!-- Vista 3 -->
<style>
  @keyframes fadeIn { ... }
  .animate-fade-in { ... }
</style>
```
**Problema**: Código duplicado en 30+ archivos

### ✅ DESPUÉS (Bueno):
```html
<!-- Vista 1 -->
<div class="animate-fade-in">Contenido</div>

<!-- Vista 2 -->
<div class="animate-fade-in">Contenido</div>

<!-- Vista 3 -->
<div class="animate-fade-in">Contenido</div>
```
**Solución**: Un solo archivo CSS global

## 📝 Recomendaciones

### ✅ HACER:
1. Usar clases de `global.css` en lugar de estilos inline
2. Agregar nuevos estilos globales a `global.css`
3. Usar Tailwind para utilidades pequeñas
4. Mantener `global.css` organizado por secciones

### ❌ NO HACER:
1. No crear tags `<style>` en las vistas
2. No duplicar código CSS
3. No usar estilos inline para componentes reutilizables
4. No crear múltiples archivos CSS para lo mismo

## 🔄 Migración de Vistas Existentes

Para limpiar vistas con estilos inline:

1. Identifica el `<style>` en la vista
2. Verifica si el estilo ya existe en `global.css`
3. Si existe, elimina el `<style>` de la vista
4. Si no existe, agrégalo a `global.css` y luego elimínalo de la vista

### Ejemplo:
```html
<!-- ANTES -->
<style>
  .animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
  }
</style>
<div class="animate-fade-in-up">Contenido</div>

<!-- DESPUÉS (eliminar el <style>, ya está en global.css) -->
<div class="animate-fade-in-up">Contenido</div>
```

## 📊 Estado Actual

- ✅ **global.css** creado con estilos comunes
- ✅ **notifications.css** para notificaciones
- ✅ Layout principal actualizado
- ✅ Vista de proformas/index limpiada
- ⏳ Pendiente limpiar otras 29 vistas con `<style>` inline

## 💡 Próximos Pasos (Opcional)

1. Limpiar gradualmente las vistas que tienen tags `<style>`
2. Identificar patrones repetidos y moverlos a `global.css`
3. Documentar nuevos componentes que agregues
4. Considerar usar CSS modules o SASS si el proyecto crece más

---

**Nota**: Este cambio mejora significativamente la mantenibilidad del proyecto sin romper nada existente. Las vistas antiguas seguirán funcionando, pero puedes ir limpiándolas gradualmente.
