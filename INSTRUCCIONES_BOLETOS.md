# Sistema de Venta de Boletos - Instrucciones de Uso

## ✅ Sistema Completamente Implementado

### 📊 Estado Actual

**Base de Datos:**
- ✅ Tablas creadas (tipos_boleto, boletos_vendidos, vendedores, ventas_vendedor)
- ✅ 4 tipos de boleto creados para "EL JAB DORADO 2026":
  - General: S/30 (500 disponibles)
  - VIP: S/80 (100 disponibles)
  - Ringside: S/150 (50 disponibles)
  - Mesa VIP: S/500 (10 disponibles)

**Backend:**
- ✅ BoletosController.php (gestión de ventas y validaciones)
- ✅ TiposBoletosController.php (gestión de tipos de boleto - admin)
- ✅ Rutas configuradas en index.php

**Frontend:**
- ✅ BuyTicketsScreen - Pantalla de compra de boletos
- ✅ AdminBoletosScreen - Panel de administración completo
- ✅ Navegación integrada
- ✅ Interfaces TypeScript completas

---

## 🛒 Cómo Comprar Boletos (Usuario)

### Opción 1: Desde la Pantalla de Evento
1. Navega a la pantalla del evento (Event)
2. Presiona el botón **"COMPRAR ENTRADAS"**
3. Selecciona el tipo de boleto (General, VIP, Ringside, Mesa VIP)
4. Elige la cantidad
5. Llena el formulario:
   - Nombres y Apellidos
   - DNI (8 dígitos)
   - Teléfono (9 dígitos, empezando con 9)
   - Método de pago (Yape, Transferencia, Efectivo)
6. Presiona **"COMPRAR BOLETOS"**
7. Se genera un código QR único (formato: BOX-JD-2026-000001)
8. El pago queda pendiente de aprobación por admin

### Opción 2: Desde el Home
1. En el Home, presiona el banner de tickets o botón de comprar
2. Sigue los mismos pasos del punto 3 en adelante

---

## 👨‍💼 Panel de Administración

### Acceso
1. Navega a **AdminPanel** (perfil de admin)
2. En el dashboard, presiona la tarjeta **"Gestionar Boletos"** 🎫

### Funciones Disponibles

#### 📋 Pestaña "Pendientes"
- Ver lista de pagos pendientes de aprobación
- Ver detalles del comprador (nombre, DNI, teléfono, cantidad)
- Ver comprobante de pago subido
- **Aprobar** o **Rechazar** cada pago
- Al aprobar, el boleto cambia a estado "verificado" y puede usarse

#### 🔍 Pestaña "Validar QR"
- Escanear o ingresar código QR del boleto
- Ejemplo: `BOX-JD-2026-000001`
- Validación en tiempo real:
  - ✅ VÁLIDO: Muestra datos del comprador y marca como "usado"
  - ❌ INVÁLIDO: Muestra mensaje de error (ya usado, no verificado, etc.)

#### 📊 Pestaña "Reportes"
- **Resumen General:**
  - Total de ventas
  - Total de boletos vendidos
  - Ingresos totales en soles
- **Desglose por Tipo:**
  - Boletos vendidos por tipo
  - Ingresos por tipo
  - Estados: Verificados, Pendientes, Rechazados

#### 🏷️ Pestaña "Tipos"
- **Crear** nuevos tipos de boleto
- **Editar** tipos existentes (nombre, precio, cantidad, color, descripción)
- **Desactivar** tipos de boleto
- Ver stock disponible en tiempo real

---

## 🔌 API Endpoints

### Públicos (Usuarios)

#### GET `/api/boletos/tipos-boleto/:eventoId`
Obtener tipos de boleto disponibles para un evento
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tipo_nombre": "General",
      "precio": "30.00",
      "cantidad_disponible": 500,
      "color_hex": "#3498db"
    }
  ]
}
```

#### POST `/api/boletos/comprar`
Crear solicitud de compra
```json
{
  "evento_id": 1,
  "tipo_boleto_id": 1,
  "nombres_apellidos": "Juan Pérez",
  "telefono": "987654321",
  "dni": "12345678",
  "cantidad": 2,
  "metodo_pago": "yape"
}
```

Respuesta:
```json
{
  "success": true,
  "message": "Solicitud de compra creada. Procede a realizar el pago.",
  "data": {
    "boleto_id": 5,
    "codigo_qr": "BOX-JD-2026-000005",
    "precio_total": 60.00,
    "mensaje_pago": "Yapea S/60.00 al número 934-567-890 y sube tu comprobante"
  }
}
```

#### POST `/api/boletos/:id/comprobante`
Subir comprobante de pago (multipart/form-data)

### Admin

#### GET `/api/boletos/pendientes`
Obtener boletos con pago pendiente

#### PUT `/api/boletos/:id/validar`
Aprobar o rechazar un pago
```json
{
  "accion": "aprobar",  // o "rechazar"
  "observaciones": "Pago verificado correctamente"
}
```

#### POST `/api/boletos/validar-qr`
Validar QR en entrada del evento
```json
{
  "codigo_qr": "BOX-JD-2026-000001"
}
```

#### POST `/api/tipos-boleto/crear`
Crear tipo de boleto (admin)
```json
{
  "evento_id": 1,
  "nombre": "Palco VIP",
  "precio": 1000.00,
  "cantidad_total": 5,
  "color_hex": "#c0392b",
  "descripcion": "Palco exclusivo con servicio premium",
  "orden": 5
}
```

#### PUT `/api/tipos-boleto/editar/:id`
Editar tipo de boleto (admin)

#### DELETE `/api/tipos-boleto/:id`
Desactivar tipo de boleto (admin)

#### GET `/api/tipos-boleto/evento/:eventoId`
Obtener todos los tipos (admin - incluye inactivos)

---

## 🔐 Validaciones

### DNI
- Debe tener exactamente 8 dígitos numéricos
- Ejemplo válido: `12345678`

### Teléfono
- Debe tener exactamente 9 dígitos
- Debe empezar con 9
- Se aceptan espacios (se eliminan automáticamente)
- Ejemplo válido: `987 654 321` o `987654321`

### Disponibilidad
- El sistema verifica stock disponible antes de vender
- Si no hay suficientes boletos, muestra error con cantidad disponible
- La cantidad vendida se actualiza inmediatamente

---

## 🎯 Flujo Completo de Venta

1. **Usuario compra boleto** → Estado: `pendiente`, Boleto: `activo`
2. **Usuario sube comprobante** → `comprobante_pago` guardado
3. **Admin revisa en "Pendientes"** → Ve detalles y comprobante
4. **Admin aprueba** → Estado: `verificado`, `fecha_validacion` registrada
5. **Usuario llega al evento** → Admin escanea QR en "Validar QR"
6. **QR validado** → Boleto: `usado`, `fecha_uso` registrada

---

## 📱 Pantallas del Sistema

### Frontend
- `BuyTicketsScreen` (BuyTicketsScreenNEW.tsx) - Compra de boletos
- `AdminBoletosScreen` - Panel admin con 4 pestañas

### Navegación
```
HomeScreen → [Banner/Botón] → BuyTickets
EventScreen → [COMPRAR ENTRADAS] → BuyTickets
AdminPanel → [Gestionar Boletos] → AdminBoletos
```

---

## 🗄️ Estructura de Base de Datos

### tipos_boleto
- id, evento_id, nombre, precio, cantidad_total, cantidad_vendida
- color_hex, descripcion, orden, activo
- fecha_creacion, fecha_actualizacion

### boletos_vendidos
- id, evento_id, tipo_boleto_id, vendedor_id
- comprador_nombres_apellidos, comprador_telefono, comprador_dni
- cantidad, precio_total, codigo_qr
- metodo_pago, comprobante_pago
- estado_pago: `pendiente` | `verificado` | `rechazado`
- estado_boleto: `activo` | `usado` | `cancelado`
- fecha_compra, fecha_validacion, fecha_uso

### vendedores
- id, nombre, tipo, codigo_vendedor
- telefono, email, comision_porcentaje, estado

### ventas_vendedor
- id, vendedor_id, boleto_id
- comision_monto, pagado, fecha_pago

---

## 🚀 Para Probar el Sistema

### 1. Crear más tipos de boleto (opcional)
Desde AdminBoletos → Pestaña "Tipos" → "Crear Tipo de Boleto"

### 2. Comprar un boleto de prueba
- Ir a Event → COMPRAR ENTRADAS
- Seleccionar "General" (S/30)
- DNI: `12345678`
- Teléfono: `987654321`
- Nombres: Tu Nombre

### 3. Aprobar el pago (como admin)
- AdminPanel → Gestionar Boletos
- Pestaña "Pendientes"
- Presionar "Aprobar"

### 4. Validar entrada
- Pestaña "Validar QR"
- Ingresar el código QR (ej: `BOX-JD-2026-000001`)
- Ver confirmación de entrada válida

### 5. Ver reportes
- Pestaña "Reportes"
- Ver estadísticas de ventas e ingresos

---

## 📝 Notas Importantes

- Los códigos QR son únicos por evento (incluyen siglas + año + número)
- Los boletos pendientes NO pueden usarse hasta ser aprobados
- Los boletos usados NO pueden escanearse nuevamente
- La vista `vista_boletos_disponibles` solo muestra boletos activos de eventos en estado "proximamente"
- El campo `cantidad_vendida` se incrementa al crear la solicitud (no al aprobar)
- Se pueden agregar vendedores externos con comisiones (tabla `vendedores`)

---

## 🔧 Mantenimiento

### Agregar nuevo evento
1. Insertar evento en tabla `eventos`
2. Crear tipos de boleto desde AdminBoletos o SQL
3. Los boletos aparecerán automáticamente en BuyTicketsScreen

### Cambiar precios
AdminBoletos → Tipos → Editar → Cambiar precio

### Desactivar tipo de boleto
AdminBoletos → Tipos → Desactivar
(No se eliminan, solo se ocultan de la vista pública)

---

✅ **Sistema listo para producción**
