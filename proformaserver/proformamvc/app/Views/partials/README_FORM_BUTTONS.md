# Componente: Form Buttons

Botones flotantes reutilizables para formularios con diseño consistente.

## Ubicación
`app/Views/partials/form_buttons.php`

## Uso Básico

```php
<?php
$form_buttons = [
    'cancel_url' => url('/ruta/cancelar'),
    'submit_text' => 'Guardar',
];
include __DIR__ . '/../partials/form_buttons.php';
?>
```

## Parámetros

| Parámetro | Tipo | Requerido | Default | Descripción |
|-----------|------|-----------|---------|-------------|
| `cancel_url` | string | ✅ | `#` | URL a la que redirige el botón cancelar |
| `submit_text` | string | ❌ | `Guardar` | Texto del botón principal |
| `submit_icon` | string | ❌ | `ph-check` | Clase de ícono Phosphor |
| `submit_color` | string | ❌ | `purple` | Color del botón: `purple`, `blue`, `green`, `orange`, `red` |
| `cancel_text` | string | ❌ | `Cancelar` | Texto del botón cancelar |
| `extra_buttons` | array | ❌ | `[]` | Array de botones adicionales |

## Ejemplos

### Ejemplo 1: Básico
```php
<?php
$form_buttons = [
    'cancel_url' => url('/clientes'),
    'submit_text' => 'Crear Cliente',
];
include __DIR__ . '/../partials/form_buttons.php';
?>
```

### Ejemplo 2: Con color e ícono personalizado
```php
<?php
$form_buttons = [
    'cancel_url' => url('/productos'),
    'submit_text' => 'Guardar Producto',
    'submit_icon' => 'ph-floppy-disk',
    'submit_color' => 'blue',
];
include __DIR__ . '/../partials/form_buttons.php';
?>
```

### Ejemplo 3: Con botones adicionales
```php
<?php
$form_buttons = [
    'cancel_url' => url('/pdf-templates'),
    'submit_text' => 'Guardar Cambios',
    'submit_icon' => 'ph-floppy-disk',
    'submit_color' => 'purple',
    'extra_buttons' => [
        [
            'text' => 'Vista Previa',
            'icon' => 'ph-eye',
            'color' => 'green',
            'href' => url('/pdf-templates/preview/1'),
            'target' => '_blank'
        ]
    ]
];
include __DIR__ . '/../partials/form_buttons.php';
?>
```

### Ejemplo 4: Múltiples botones extra
```php
<?php
$form_buttons = [
    'cancel_url' => url('/proformas'),
    'submit_text' => 'Guardar Proforma',
    'submit_color' => 'blue',
    'extra_buttons' => [
        [
            'text' => 'Vista Previa',
            'icon' => 'ph-eye',
            'color' => 'green',
            'href' => url('/proformas/preview/1'),
            'target' => '_blank'
        ],
        [
            'text' => 'Duplicar',
            'icon' => 'ph-copy',
            'color' => 'orange',
            'href' => url('/proformas/duplicate/1')
        ]
    ]
];
include __DIR__ . '/../partials/form_buttons.php';
?>
```

## Estructura de botones extra

Cada botón en el array `extra_buttons` puede tener:

```php
[
    'text' => 'Texto del botón',        // Requerido
    'icon' => 'ph-icon-name',           // Opcional
    'color' => 'blue',                   // Opcional (purple, blue, green, orange, red)
    'href' => url('/ruta'),              // Requerido
    'target' => '_blank'                 // Opcional (_blank para nueva pestaña)
]
```

## Colores disponibles

- `purple` - Morado (default)
- `blue` - Azul
- `green` - Verde
- `orange` - Naranja
- `red` - Rojo

## Íconos (Phosphor Icons)

Usa cualquier ícono de [Phosphor Icons](https://phosphoricons.com/):
- `ph-check`
- `ph-floppy-disk`
- `ph-plus`
- `ph-eye`
- `ph-copy`
- `ph-trash`
- etc.

## Diseño

Los botones se renderizan con:
- Borde superior separador
- Alineación a la derecha
- Espaciado consistente
- Animaciones de hover
- Sombras sutiles

## Casos de uso

✅ Formularios de creación
✅ Formularios de edición
✅ Formularios con acciones múltiples (guardar + preview)
✅ Cualquier formulario que necesite botones flotantes

## Migración

Para migrar formularios existentes:

### Antes:
```php
<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
    <a href="<?= url('/clientes') ?>" class="px-5 py-2.5 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-colors">Cancelar</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-700 shadow-lg shadow-purple-200 transition-all flex items-center gap-2">
        <i class="ph-bold ph-check"></i> Guardar
    </button>
</div>
```

### Después:
```php
<?php
$form_buttons = [
    'cancel_url' => url('/clientes'),
    'submit_text' => 'Guardar',
];
include __DIR__ . '/../partials/form_buttons.php';
?>
```

## Archivos actualizados

Ya migrados a este componente:
- ✅ `pdf_templates/create.php`
- ✅ `pdf_templates/edit.php`

Pendientes de migrar:
- [ ] `clientes/create.php`
- [ ] `clientes/edit.php`
- [ ] `inventario/create.php`
- [ ] `inventario/edit.php`
- [ ] `proformas/create.php`
- [ ] `proformas/edit.php`
