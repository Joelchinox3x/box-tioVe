# Cambios Realizados: Género y Apellidos

## Resumen
Se agregaron dos nuevos campos a la base de datos:
- `genero` en la tabla `peleadores` (ENUM: 'masculino' | 'femenino')
- `apellidos` en la tabla `usuarios` (VARCHAR(100), opcional)

## Archivos Modificados

### 1. Base de Datos
**Archivo**: `backend/database/add_genero_to_peleadores.sql`
- Agrega columna `genero` a tabla `peleadores` con índice
- Agrega columna `apellidos` a tabla `usuarios`

### 2. PeleadoresController.php
**Cambios**:
- ✅ Método `listar()`: Ahora incluye `genero` y `apellidos` en el SELECT
- ✅ Método `obtenerPorId()`: Incluye `apellidos` del usuario
- ✅ Método `inscribir()`:
  - Agrega `genero` como campo requerido
  - Valida que genero sea 'masculino' o 'femenino'
  - Inserta `apellidos` en tabla usuarios (opcional)
  - Inserta `genero` en tabla peleadores
- ✅ Método `ranking()`: Incluye `genero` y `apellidos`

### 3. EventosController.php
**Cambios**:
- ✅ Método `getEventoPrincipal()`:
  - Query de peleadores destacados: incluye `genero` y `apellidos`
  - Query de peleas pactadas: incluye `genero` y `apellidos` para ambos peleadores

## Validaciones Agregadas

```php
// En PeleadoresController::inscribir()
$required = ['nombre', 'email', 'password', 'apodo', 'fecha_nacimiento',
            'peso_actual', 'documento_identidad', 'club_id', 'genero'];

// Validación de género
if (!in_array($data['genero'], ['masculino', 'femenino'])) {
    return ["success" => false, "message" => "El género debe ser 'masculino' o 'femenino'"];
}
```

## Ejemplo de Datos Esperados

### Request POST /api/peleadores
```json
{
  "nombre": "Juan",
  "apellidos": "Pérez García",
  "email": "juan@example.com",
  "password": "12345678",
  "telefono": "+54 11 1234-5678",
  "apodo": "El Martillo",
  "fecha_nacimiento": "1995-01-15",
  "peso_actual": 75.5,
  "altura": 1.78,
  "genero": "masculino",
  "club_id": 1,
  "documento_identidad": "12345678",
  "estilo": "fajador",
  "experiencia_anos": 5
}
```

### Response GET /api/eventos
```json
{
  "success": true,
  "peleadores_destacados": [
    {
      "id": 1,
      "nombre": "Juan",
      "apellidos": "Pérez García",
      "apodo": "El Martillo",
      "genero": "masculino",
      "estilo": "fajador",
      ...
    }
  ]
}
```

## Próximos Pasos
- [ ] Actualizar frontend para incluir selector de género
- [ ] Actualizar frontend para campo apellidos (opcional)
- [ ] Actualizar tipos TypeScript en el frontend
