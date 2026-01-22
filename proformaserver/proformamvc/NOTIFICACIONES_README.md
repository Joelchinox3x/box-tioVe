# Sistema de Notificaciones Global

## ✅ Instalación Completa

El sistema de notificaciones ya está integrado en el layout principal (`layouts/main.php`) y está disponible en todas las páginas de tu aplicación automáticamente.

## 📁 Archivos Creados

- `public/js/notifications.js` - Lógica del sistema de notificaciones
- `public/css/notifications.css` - Estilos y animaciones
- `app/Views/layouts/main.php` - Actualizado para incluir los archivos

## 🚀 Uso Básico

### Función Principal

```javascript
mostrarNotificacion(titulo, mensaje, tipo, duracion);
```

**Parámetros:**
- `titulo` (string) - Título de la notificación
- `mensaje` (string) - Mensaje descriptivo
- `tipo` (string) - Tipo de notificación: `'success'`, `'info'`, `'warning'`, `'error'`
- `duracion` (number, opcional) - Duración en milisegundos (default: 3000)

### Ejemplos de Uso

```javascript
// Notificación de éxito
mostrarNotificacion('¡Éxito!', 'Cliente guardado correctamente', 'success');

// Notificación de error
mostrarNotificacion('Error', 'No se pudo eliminar el registro', 'error');

// Notificación de advertencia
mostrarNotificacion('Atención', 'Este campo es requerido', 'warning');

// Notificación informativa
mostrarNotificacion('Info', 'Sincronizando datos...', 'info');

// Con duración personalizada (5 segundos)
mostrarNotificacion('Procesando', 'Generando reporte...', 'info', 5000);
```

## 🎯 Funciones Auxiliares (Shortcuts)

Para mayor comodidad, puedes usar estas funciones cortas:

```javascript
// Alias general
notify('Título', 'Mensaje', 'success');

// Funciones específicas por tipo
notifySuccess('Cliente creado', 'Se guardó exitosamente');
notifyError('Error al guardar', 'Intente nuevamente');
notifyWarning('Advertencia', 'Revise los datos');
notifyInfo('Información', 'Procesando...');
```

## 📋 Ejemplos Prácticos

### 1. En un formulario (después de guardar)

```javascript
document.getElementById('formCliente').addEventListener('submit', function(e) {
  e.preventDefault();
  
  // Simular guardado
  fetch('/clientes/store', {
    method: 'POST',
    body: new FormData(this)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      notifySuccess('¡Guardado!', 'Cliente creado exitosamente');
      setTimeout(() => window.location = '/clientes', 1500);
    } else {
      notifyError('Error', data.message || 'No se pudo guardar');
    }
  })
  .catch(error => {
    notifyError('Error de conexión', 'Intente nuevamente');
  });
});
```

### 2. Al eliminar un registro

```javascript
function eliminarCliente(id) {
  if (confirm('¿Está seguro de eliminar este cliente?')) {
    fetch(`/clientes/delete/${id}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          notifySuccess('Eliminado', 'Cliente eliminado correctamente');
          // Recargar tabla o quitar fila
        } else {
          notifyError('Error', 'No se pudo eliminar');
        }
      });
  }
}
```

### 3. Al agregar productos a una proforma

```javascript
function agregarProducto(producto) {
  // Agregar producto a la lista
  items.push(producto);
  
  // Mostrar notificación
  notifySuccess(
    producto.nombre, 
    'Agregado a la proforma', 
    2000  // 2 segundos
  );
  
  // Actualizar UI
  renderizarItems();
}
```

### 4. Validaciones

```javascript
function validarFormulario() {
  const nombre = document.getElementById('nombre').value;
  const email = document.getElementById('email').value;
  
  if (!nombre) {
    notifyWarning('Campo requerido', 'El nombre es obligatorio');
    return false;
  }
  
  if (!email) {
    notifyWarning('Campo requerido', 'El email es obligatorio');
    return false;
  }
  
  if (!email.includes('@')) {
    notifyError('Email inválido', 'Ingrese un email válido');
    return false;
  }
  
  return true;
}
```

### 5. Procesos largos

```javascript
async function generarReporte() {
  notifyInfo('Procesando', 'Generando reporte PDF...', 5000);
  
  try {
    const response = await fetch('/reportes/generar');
    const data = await response.json();
    
    if (data.success) {
      notifySuccess('¡Listo!', 'Reporte generado correctamente');
      window.open(data.url, '_blank');
    }
  } catch (error) {
    notifyError('Error', 'No se pudo generar el reporte');
  }
}
```

### 6. Con AJAX en PHP tradicional

```html
<script>
function guardarConfiguracion() {
  const formData = new FormData(document.getElementById('formSettings'));
  
  fetch('/settings/save', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(html => {
    notifySuccess('Configuración guardada', 'Cambios aplicados correctamente');
  })
  .catch(() => {
    notifyError('Error', 'No se pudieron guardar los cambios');
  });
}
</script>
```

## 🎨 Tipos de Notificaciones

### Success (Verde)
- Operaciones exitosas
- Guardado correcto
- Eliminación exitosa
- Proceso completado

```javascript
notifySuccess('¡Éxito!', 'Operación completada');
```

### Error (Rojo)
- Errores del servidor
- Validaciones fallidas
- Operaciones no permitidas

```javascript
notifyError('Error', 'No se pudo completar la acción');
```

### Warning (Amarillo)
- Advertencias
- Campos requeridos
- Confirmaciones necesarias

```javascript
notifyWarning('Atención', 'Revise los datos ingresados');
```

### Info (Azul)
- Información general
- Procesos en curso
- Actualizaciones

```javascript
notifyInfo('Información', 'Cargando datos...');
```

## 🔧 Personalización

### Cambiar duración por defecto

Edita `public/js/notifications.js` y modifica:

```javascript
function mostrarNotificacion(titulo, mensaje, tipo = 'info', duracion = 5000) {
  // duracion ahora es 5 segundos por defecto
}
```

### Cambiar posición

Edita `public/css/notifications.css`:

```css
#notificationContainer {
  /* Cambiar de top-right a top-left */
  left: 0.5rem;
  right: auto;
}
```

### Cambiar colores

Edita `public/js/notifications.js` en la sección de colores:

```javascript
const colores = {
  success: 'bg-green-50 border-green-500 text-green-800',
  info: 'bg-purple-50 border-purple-500 text-purple-800', // Cambiado
  // ...
};
```

## 💡 Tips

1. **Mensajes cortos**: Mantén los títulos y mensajes concisos
2. **Duración apropiada**: Usa 2-3 segundos para mensajes cortos, 5-7 para largos
3. **No abuses**: No muestres muchas notificaciones simultáneas
4. **Feedback inmediato**: Muestra la notificación justo después de la acción
5. **Mensajes claros**: Usa lenguaje que el usuario entienda

## ❓ Problemas Comunes

**Las notificaciones no aparecen:**
- Verifica que los archivos CSS y JS estén cargando correctamente
- Abre la consola del navegador para ver errores
- Confirma que estás usando el layout principal

**Las animaciones no funcionan:**
- Verifica que el archivo CSS esté cargando
- Comprueba que Tailwind CSS esté disponible

**Notificaciones se quedan pegadas:**
- Verifica la consola por errores de JavaScript
- Asegúrate de que el contenedor existe en el DOM

## 🚀 Integración con Backend (PHP)

Puedes mostrar notificaciones basadas en parámetros GET:

```php
// En el controlador
$this->redirect('/clientes', ['msg' => 'created', 'nombre' => $cliente['nombre']]);

// En la vista
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    notifySuccess(
      '¡Cliente creado!',
      '<?= htmlspecialchars($_GET['nombre'] ?? '') ?> fue agregado'
    );
  });
</script>
<?php endif; ?>
```

¡Listo! Ahora tienes un sistema de notificaciones profesional en toda tu aplicación.
