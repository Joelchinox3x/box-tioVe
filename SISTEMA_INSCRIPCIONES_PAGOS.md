# ✅ Sistema de Inscripciones y Pagos - Implementado

## 📋 Resumen

Se ha implementado un sistema completo para gestionar las inscripciones de peleadores a eventos y el control de pagos.

---

## 🗄️ Base de Datos

### Cambios en tabla `eventos`
```sql
ALTER TABLE eventos
ADD COLUMN precio_inscripcion_peleador DECIMAL(10,2) DEFAULT 20.00
```
- Cada evento tiene su propio precio de inscripción
- Por defecto: 20.00 soles
- Puede variar según el evento

### Nueva tabla `inscripciones_eventos`
```sql
CREATE TABLE inscripciones_eventos (
    id INT PRIMARY KEY,
    peleador_id INT,
    evento_id INT,
    estado_pago ENUM('pendiente', 'pagado'),
    monto_pagado DECIMAL(10,2),
    fecha_inscripcion TIMESTAMP,
    fecha_pago TIMESTAMP,
    metodo_pago ENUM('efectivo', 'transferencia', 'yape', 'plin', 'deposito', 'otro'),
    comprobante_pago VARCHAR(500),
    notas_admin TEXT
)
```

**Relación única:** Un peleador solo puede inscribirse una vez por evento

---

## 🔧 Backend - Endpoints API

### 1. Obtener inscripciones
```
GET /api/admin/inscripciones
GET /api/admin/inscripciones?estado_pago=pendiente
GET /api/admin/inscripciones?evento_id=1
```

**Respuesta:**
```json
{
  "success": true,
  "count": 5,
  "inscripciones": [
    {
      "id": 1,
      "peleador_id": 1,
      "evento_id": 1,
      "estado_pago": "pendiente",
      "monto_pagado": 20.00,
      "peleador_nombre": "Juan Pérez",
      "peleador_email": "juan@example.com",
      "peleador_telefono": "+51 999999999",
      "peleador_apodo": "El Martillo",
      "peleador_dni": "12345678",
      "evento_titulo": "Torneo de Boxeo 2025",
      "fecha_evento": "2025-02-15",
      "precio_evento": 20.00,
      "club_nombre": "Gimnasio El Campeón"
    }
  ]
}
```

### 2. Obtener inscripciones pendientes
```
GET /api/admin/inscripciones-pendientes
```

### 3. Crear inscripción
```
POST /api/admin/inscripciones
Content-Type: application/json

{
  "peleador_id": 1,
  "evento_id": 1
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Inscripción creada exitosamente",
  "inscripcion_id": 1,
  "monto_a_pagar": 20.00
}
```

### 4. Confirmar pago
```
PUT /api/admin/inscripciones/{id}
Content-Type: application/json

{
  "monto_pagado": 20.00,
  "metodo_pago": "yape",
  "comprobante_pago": "https://ejemplo.com/comprobante.jpg",
  "notas_admin": "Pagó el 15/12/2025"
}
```

### 5. Actualizar precio de evento
```
PUT /api/admin/eventos/{id}/precio
Content-Type: application/json

{
  "precio_inscripcion_peleador": 30.00
}
```

---

## 💻 Frontend - AdminService

### Métodos disponibles

```typescript
// Obtener todas las inscripciones con filtros
AdminService.getInscripciones({
  estado_pago: 'pendiente',
  evento_id: 1
})

// Obtener solo las pendientes
AdminService.getInscripcionesPendientes()

// Crear nueva inscripción
AdminService.crearInscripcion(peleadorId, eventoId)

// Confirmar pago
AdminService.confirmarPago(inscripcionId, {
  monto_pagado: 20.00,
  metodo_pago: 'yape',
  comprobante_pago: 'url...',
  notas_admin: 'Notas...'
})

// Actualizar precio de evento
AdminService.actualizarPrecioEvento(eventoId, 30.00)
```

---

## 🔄 Flujo Completo

### 1. Registro del Peleador
```
Usuario se registra → peleadores.estado_inscripcion = 'pendiente'
```

### 2. Aprobación del Admin
```
Admin aprueba → peleadores.estado_inscripcion = 'aprobado'
```

### 3. Inscripción a Evento
```
Peleador se inscribe a evento
→ Se crea registro en inscripciones_eventos
→ estado_pago = 'pendiente'
→ monto_pagado = eventos.precio_inscripcion_peleador
```

### 4. Pago
```
Peleador paga por WhatsApp/Transferencia
→ Envía comprobante al admin
```

### 5. Confirmación
```
Admin confirma pago
→ estado_pago = 'pagado'
→ fecha_pago = NOW()
→ metodo_pago = 'yape'/'transferencia'/etc
→ comprobante_pago = URL
```

### 6. Participación
```
Solo los peleadores con estado_pago = 'pagado' pueden participar
```

---

## 📂 Archivos Modificados/Creados

### Base de Datos
- ✅ `/backend/database/add_inscripciones_eventos.sql` (NUEVO)

### Backend
- ✅ `/backend/controllers/AdminController.php` (MODIFICADO)
  - `getInscripciones($filters)`
  - `getInscripcionesPendientes()`
  - `crearInscripcion($data)`
  - `confirmarPago($inscripcion_id, $data)`
  - `actualizarPrecioEvento($evento_id, $data)`

- ✅ `/backend/public/index.php` (MODIFICADO)
  - Rutas agregadas para inscripciones

### Frontend
- ✅ `/frontend/src/services/AdminService.ts` (MODIFICADO)
  - Métodos para gestión de inscripciones

---

## 🎯 Próximos Pasos

### Para completar el sistema necesitas:

1. **Ejecutar la migración SQL:**
   ```bash
   sudo docker exec -i mi_mysql mysql -u root -p'Cocacola@123' boxevent < /home/server/evento-box/backend/database/add_inscripciones_eventos.sql
   ```

2. **Crear pantalla de admin para gestionar pagos:**
   - Ver inscripciones pendientes
   - Confirmar pagos
   - Ver historial

3. **Permitir que peleadores se inscriban a eventos:**
   - Agregar botón "Inscribirse" en detalle de evento
   - Mostrar precio de inscripción
   - Mostrar estado de pago

---

## 💡 Características del Sistema

✅ **Flexible:** Cada evento puede tener diferente precio
✅ **Trazable:** Historial completo de inscripciones y pagos
✅ **Simple:** Solo requiere confirmación manual del admin
✅ **Escalable:** Preparado para integrar pasarelas de pago en el futuro
✅ **Seguro:** Validaciones en backend

---

## 🔐 Validaciones Implementadas

- ✅ El peleador debe estar aprobado para inscribirse
- ✅ No se puede inscribir dos veces al mismo evento
- ✅ El monto pagado debe ser mayor a 0
- ✅ El método de pago debe ser uno de los valores ENUM

---

**Sistema listo para usar!** 🎉
