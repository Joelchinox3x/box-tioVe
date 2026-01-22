# Guía de URLs Correctas para las Vistas

## ⚠️ IMPORTANTE: Los archivos de vistas tienen errores de sintaxis

Los scripts automáticos crearon errores de sintaxis en las vistas. Necesitas corregirlos manualmente.

## ✅ Patrones CORRECTOS a usar:

### 1. Enlaces simples (href):
```php
<!-- ❌ INCORRECTO -->
<a href="/clientes">

<!-- ✅ CORRECTO -->
<a href="<?= url('/clientes') ?>">
```

### 2. Formularios (action):
```php
<!-- ❌ INCORRECTO -->
<form action="/clientes/store" method="POST">

<!-- ✅ CORRECTO -->
<form action="<?= url('/clientes/store') ?>" method="POST">
```

### 3. Enlaces con parámetros dinámicos:
```php
<!-- ❌ INCORRECTO -->
<a href="/clientes/edit/<?= $cliente['id'] ?>">

<!-- ✅ CORRECTO -->
<a href="<?= url('/clientes/edit/' . $cliente['id']) ?>">
```

### 4. Imágenes y assets (usa asset() en lugar de url()):
```php
<!-- ❌ INCORRECTO -->
<img src="/<?= $imagen ?>">

<!-- ✅ CORRECTO -->
<img src="<?= asset('/' . $imagen) ?>">
```

### 5. En JavaScript:
```php
<script>
// ❌ INCORRECTO
window.location.href = '/clientes/delete/${id}';

// ✅ CORRECTO
window.location.href = '<?= url('/clientes/delete/') ?>' + id;
</script>
```

### 6. Fetch/AJAX:
```php
<script>
// ❌ INCORRECTO
fetch(`/clientes/search?q=${term}`)

// ✅ CORRECTO
fetch(`<?= url('/clientes/search') ?>?q=${term}`)
</script>
```

## 📝 Lista de archivos que NECESITAN corrección manual:

### Clientes:
- ✅ `app/Views/clientes/index.php` - Corregir href y fetch
- ✅ `app/Views/clientes/create.php` - Corregir action
- ✅ `app/Views/clientes/edit.php` - Corregir action y href

### Productos:
- ✅ `app/Views/productos/index.php` - Corregir href y fetch
- ✅ `app/Views/productos/create.php` - Corregir action
- ✅ `app/Views/productos/edit.php` - Corregir action
- ✅ `app/Views/productos/show.php` - Corregir href e img src

### Proformas:
- ✅ `app/Views/proformas/index.php` - Corregir href
- ⚠️ `app/Views/proformas/create.php` - Necesita implementación completa
- ⚠️ `app/Views/proformas/view.php` - Necesita implementación completa

## 🔍 Errores Comunes a Buscar y Corregir:

1. **Comillas mezcladas:**
   ```php
   ❌ href="<?= url("/clientes') ?>"
   ✅ href="<?= url('/clientes') ?>"
   ```

2. **Concatenación rota:**
   ```php
   ❌ href="<?= url('/clientes/<?= $id ?>')"
   ✅ href="<?= url('/clientes/' . $id) ?>"
   ```

3. **Asset vs URL:**
   ```php
   ❌ src="<?= url('/uploads/foto.jpg') ?>"
   ✅ src="<?= asset('/uploads/foto.jpg') ?>"
   ```

## 🚀 Cómo Probar que Funciona:

1. Abre: `http://localhost:8080/proformamvc/public/`
2. Haz click en cualquier enlace
3. La URL debe mantener `/proformamvc/public/` en la ruta
4. Ejemplo correcto: `http://localhost:8080/proformamvc/public/clientes/create`

## ⚡ Acción Inmediata Requerida:

**OPCIÓN 1:** Corregir manualmente cada archivo siguiendo los patrones de arriba

**OPCIÓN 2:** Te puedo crear nuevamente todos los archivos correctamente (recomendado)

¿Quieres que re-cree todos los archivos de vistas con la sintaxis correcta?
