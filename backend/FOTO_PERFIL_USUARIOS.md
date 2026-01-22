# Gestión de Foto de Perfil para Usuarios

## Descripción
Se ha implementado la funcionalidad completa para que los usuarios puedan subir, actualizar y visualizar su foto de perfil.

## Cambios Realizados

### 1. Base de Datos
- **Tabla `usuarios`**: Ya cuenta con el campo `foto_perfil VARCHAR(500)`
- **Nueva migración**: `add_apellidos_to_usuarios.sql` para agregar el campo `apellidos` (usado en el formulario de registro)

### 2. Backend - UsuariosController.php

#### Método `registrarEspectador()` (Modificado)
- **Líneas 67-96**: Procesamiento de foto de perfil durante el registro
- Acepta archivos subidos vía `$_FILES['foto_perfil']`
- Validación de imagen con `getimagesize()`
- Genera nombre único: `timestamp_random.ext`
- Guarda en: `backend/files/usuarios/`
- Devuelve la ruta en la respuesta: `foto_perfil` en el JSON

#### Método `updateProfile()` (Nuevo)
- **Líneas 226-357**: Actualización completa del perfil de usuario
- Permite actualizar: `nombre`, `apellidos`, `telefono`, `foto_perfil`
- Al subir nueva foto, elimina la foto anterior automáticamente
- Validación de usuario existente
- Transacciones seguras con rollback en caso de error
- Endpoint: `PUT /api/usuarios/{id}/perfil`

#### Método `login()` (Sin cambios necesarios)
- Ya devuelve todos los campos del usuario, incluyendo `foto_perfil`

### 3. Backend - index.php (Rutas)

#### Ruta de Registro (Modificada)
```php
POST /api/usuarios/registro
```
- Ahora detecta si viene `multipart/form-data` (con archivo)
- Si hay archivo, usa `$_POST`, si no, usa JSON
- Soporta ambos formatos para compatibilidad

#### Ruta de Actualización de Perfil (Nueva)
```php
PUT /api/usuarios/{id}/perfil
```
- Acepta `multipart/form-data` para subir foto
- Acepta JSON para actualizar datos sin foto
- Llama a `updateProfile($id, $data)`

### 4. Directorio de Archivos
- **Creado**: `/backend/files/usuarios/`
- **Permisos**: `777` (escritura completa)
- **Estructura**:
  ```
  backend/files/
  ├── peleadores/      (ya existía)
  └── usuarios/        (nuevo)
  ```

## Estructura de las Imágenes

### Almacenamiento
- **Ubicación física**: `/backend/files/usuarios/`
- **Formato de nombre**: `{timestamp}_{random}.{ext}`
- **Ejemplo**: `1736634726_a3f8b2c1.jpg`

### Referencia en BD
- **Campo**: `foto_perfil` en tabla `usuarios`
- **Valor almacenado**: `files/usuarios/1736634726_a3f8b2c1.jpg`
- **URL completa**: `http://tu-dominio.com/api/files/usuarios/1736634726_a3f8b2c1.jpg`

## Uso desde el Frontend

### 1. Registro de Usuario con Foto

```javascript
const formData = new FormData();
formData.append('nombre', 'Juan');
formData.append('apellidos', 'Pérez');
formData.append('email', 'juan@example.com');
formData.append('password', 'password123');
formData.append('telefono', '+51999999999');
formData.append('club_id', '1');
formData.append('foto_perfil', fileInput.files[0]); // Archivo de imagen

const response = await fetch('http://localhost/api/usuarios/registro', {
  method: 'POST',
  body: formData // No enviar Content-Type, el navegador lo maneja
});

const result = await response.json();
// result.foto_perfil contiene la ruta: "files/usuarios/..."
```

### 2. Actualizar Perfil con Foto

```javascript
const formData = new FormData();
formData.append('nombre', 'Juan Carlos');
formData.append('telefono', '+51988888888');
formData.append('foto_perfil', newFileInput.files[0]); // Nueva foto

const response = await fetch('http://localhost/api/usuarios/15/perfil', {
  method: 'PUT',
  body: formData
});

const result = await response.json();
// result.usuario contiene todos los datos actualizados
```

### 3. Actualizar Perfil SIN Foto (solo datos)

```javascript
const response = await fetch('http://localhost/api/usuarios/15/perfil', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    nombre: 'Juan Carlos',
    telefono: '+51988888888'
  })
});
```

### 4. Login (devuelve foto_perfil)

```javascript
const response = await fetch('http://localhost/api/usuarios/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'juan@example.com',
    password: 'password123'
  })
});

const result = await response.json();
// result.usuario.foto_perfil contiene la ruta de la foto
```

## Validaciones Implementadas

### Registro
- ✅ Campos requeridos: `nombre`, `email`, `password`, `telefono`, `club_id`
- ✅ Email válido con `filter_var()`
- ✅ Contraseña mínimo 6 caracteres
- ✅ Email único (no duplicados)
- ✅ Foto opcional (validada si se envía)

### Actualización de Perfil
- ✅ Usuario debe existir (HTTP 404 si no existe)
- ✅ Al menos un campo debe actualizarse
- ✅ Validación de imagen si se sube foto
- ✅ Eliminación de foto anterior al actualizar

### Validación de Imágenes
- ✅ `getimagesize()` verifica que sea imagen real
- ✅ Extensión detectada automáticamente
- ✅ Fallback a `.jpg` si no se detecta extensión
- ✅ Nombre único con timestamp + bytes aleatorios

## Logs y Debugging

El sistema genera logs automáticos:

```
✅ FOTO USUARIO SUBIDA: files/usuarios/1736634726_a3f8b2c1.jpg
✅ FOTO USUARIO ACTUALIZADA: files/usuarios/1736634850_d5e9f1a2.jpg
❌ ERROR MOVIENDO ARCHIVO a /path/to/destination
❌ ERROR: El archivo no es una imagen válida
```

Ubicación de logs: `/var/log/apache2/error.log` o logs de PHP según configuración.

## Migración de Base de Datos

Si la tabla `usuarios` no tiene el campo `apellidos`, ejecutar:

```bash
mysql -u root -p boxevent < backend/database/add_apellidos_to_usuarios.sql
```

## Comparación con PeleadoresController

La implementación sigue el mismo patrón que `PeleadoresController.php`:
- ✅ Misma estructura de validación de archivos
- ✅ Mismo directorio padre: `backend/files/`
- ✅ Mismo formato de nombres de archivo
- ✅ Mismos permisos de directorio (0777)
- ✅ Misma lógica de logs
- ✅ Mismo manejo de multipart/form-data en rutas

## Endpoints Disponibles

| Método | Endpoint | Descripción | Body |
|--------|----------|-------------|------|
| POST | `/api/usuarios/registro` | Registrar espectador | `multipart/form-data` o JSON |
| POST | `/api/usuarios/login` | Iniciar sesión | JSON |
| PUT | `/api/usuarios/{id}/perfil` | Actualizar perfil | `multipart/form-data` o JSON |

## Notas Importantes

1. **Seguridad**: El sistema valida que los archivos sean imágenes reales usando `getimagesize()`
2. **Limpieza**: Al actualizar la foto, se elimina automáticamente la foto anterior
3. **Compatibilidad**: Soporta tanto `multipart/form-data` como JSON
4. **Transacciones**: Usa transacciones de BD para garantizar consistencia
5. **Error Handling**: Rollback automático en caso de errores

## Testing

Para probar la funcionalidad:

```bash
# 1. Registro con foto
curl -X POST http://localhost/api/usuarios/registro \
  -F "nombre=Test" \
  -F "email=test@test.com" \
  -F "password=123456" \
  -F "telefono=999999999" \
  -F "club_id=1" \
  -F "foto_perfil=@/path/to/image.jpg"

# 2. Actualizar perfil con foto
curl -X PUT http://localhost/api/usuarios/15/perfil \
  -F "nombre=Test Updated" \
  -F "foto_perfil=@/path/to/new-image.jpg"

# 3. Login
curl -X POST http://localhost/api/usuarios/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"123456"}'
```

## Próximos Pasos (Opcionales)

- [ ] Implementar compresión de imágenes para optimizar tamaño
- [ ] Agregar límite de tamaño de archivo (ej: 5MB máximo)
- [ ] Generar thumbnails automáticamente
- [ ] Implementar cropping de imágenes
- [ ] Agregar endpoint para eliminar foto de perfil
