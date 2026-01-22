# Cambios en Formulario de Registro de Peleadores

## Resumen de Cambios

### 1. Nuevos Campos Agregados
- ✅ **Apellidos** (opcional) - Campo de texto después del nombre
- ✅ **Género** (requerido) - Selector con opciones: Masculino / Femenino

### 2. Campos Removidos
- ❌ **Experiencia** - Se configurará posteriormente en el perfil del usuario

### 3. Validaciones Actualizadas

#### Edad
- **ANTES**: 18-60 años
- **AHORA**: 12-100 años
- Mensaje de error para < 12: "Debes tener al menos 12 años"
- Mensaje de error para > 100: "Edad máxima: 100 años"

#### Peso
- **ANTES**: 40-150 kg
- **AHORA**: 30-140 kg
- Mensaje de error: "Peso debe estar entre 30 y 140 kg"

#### Altura
- **ANTES**: 140-220 cm
- **AHORA**: 130-220 cm
- Mensaje de error: "Altura debe estar entre 130 y 220 cm"

#### Género (NUEVO)
- **Requerido**: Sí
- **Opciones**: Masculino / Femenino
- Mensaje de error: "Debes seleccionar tu género"

### 4. Estructura del FormData

```typescript
interface FormData {
  nombre: string;           // Requerido
  apellidos: string;        // Opcional
  apodo: string;           // Opcional
  edad: string;            // Requerido (12-100)
  peso: string;            // Requerido (30-140 kg)
  altura: string;          // Requerido (130-220 cm)
  genero: string;          // Requerido (masculino/femenino)
  email: string;           // Requerido
  telefono: string;        // Opcional
  dni: string;             // Requerido
  club_id: string | number; // Requerido
}
```

### 5. Datos Enviados al Backend

```javascript
{
  nombre: "Miguel",
  apellidos: "Rodríguez García",  // o null si está vacío
  email: "miguel@example.com",
  password: "12345678",  // DNI como password inicial
  telefono: "+54 9 11 1234-5678",
  apodo: "El Trueno",  // o primer nombre si está vacío
  fecha_nacimiento: "1998-01-01",  // calculada desde edad
  peso_actual: 75.5,
  altura: 1.80,  // convertida a metros
  genero: "masculino",  // NUEVO
  documento_identidad: "12345678",
  club_id: 1,
  estilo: "fajador",  // Por defecto - configurable en perfil
  experiencia_anos: 0  // Por defecto - configurable en perfil
}
```

### 6. UI del Formulario

**Orden de campos:**

1. **DATOS PERSONALES** 👤
   - Nombre (requerido)
   - Apellidos (opcional) - NUEVO
   - Apodo (opcional)
   - DNI (requerido)

2. **CARACTERÍSTICAS FÍSICAS** 💪
   - Género (requerido) - NUEVO
   - Edad (requerido) - Validación actualizada
   - Peso (requerido) - Validación actualizada
   - Altura (requerido) - Validación actualizada

3. **CONTACTO** 📱
   - Email (requerido)
   - Teléfono (opcional)

4. **CLUB / GIMNASIO** 🥊
   - Selección de club (requerido)

5. ~~**EXPERIENCIA** 🏆~~ - REMOVIDO

### 7. Notas Importantes

- El campo **estilo** se establece por defecto en "fajador" y será configurable en el perfil
- El campo **experiencia_anos** se establece por defecto en 0 y será configurable en el perfil
- El género es un campo ENUM en la base de datos con valores: 'masculino' | 'femenino'
- Los apellidos son opcionales en el frontend pero se envían como `null` al backend si están vacíos
