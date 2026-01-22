# Configuración de API DNI - ApiPeru.dev

## Pasos para activar la consulta automática de DNI

### 1. Obtener Token de API

1. Ve a [https://apiperu.dev](https://apiperu.dev)
2. Regístrate o inicia sesión
3. Ve a tu dashboard y copia tu **token de API**

### 2. Configurar el Token

Abre el archivo `config/config.php` y agrega tu token:

```php
// API Perú - Consulta DNI/RUC
'apiperu' => [
    'token' => 'TU_TOKEN_AQUI'  // Pegar tu token aquí
]
```

### 3. ¿Cómo funciona?

Una vez configurado:

1. En el formulario de **Crear Cliente**, cuando ingreses **8 dígitos** en el campo DNI/RUC
2. Automáticamente se consultará la API de RENIEC
3. El campo **Nombre** se llenará automáticamente con el nombre completo
4. Verás una notificación de éxito o error

### 4. Características

- ✅ Consulta automática al escribir 8 dígitos
- ✅ Spinner de carga mientras consulta
- ✅ Autocompletado del campo nombre
- ✅ Notificaciones visuales (éxito/error)
- ✅ Animación en el campo nombre cuando se autocompleta

### 5. Endpoint creado

```
POST /clientes/consultar-dni
```

**Body:**
```json
{
  "dni": "12345678"
}
```

**Response (éxito):**
```json
{
  "success": true,
  "data": {
    "nombre_completo": "JUAN PEREZ GARCIA",
    "nombres": "JUAN",
    "apellido_paterno": "PEREZ",
    "apellido_materno": "GARCIA"
  }
}
```

**Response (error):**
```json
{
  "success": false,
  "message": "No se pudo consultar el DNI"
}
```

### 6. Notas importantes

- Solo funciona con **DNI de 8 dígitos** (no con RUC de 11)
- Requiere conexión a internet
- El token debe estar activo y con créditos disponibles
- Si no hay token configurado, mostrará un mensaje de error

### 7. Archivos modificados

- `app/Controllers/ClienteController.php` - Método `consultarDni()`
- `app/Views/clientes/create.php` - JavaScript para consulta automática
- `config/config.php` - Configuración del token
- `public/index.php` - Ruta POST `/clientes/consultar-dni`

### 8. Próximas mejoras posibles

- Agregar consulta RUC (11 dígitos)
- Agregar esta funcionalidad en el formulario de edición
- Caché de consultas para ahorrar créditos
- Mostrar más datos (dirección, etc.)
