# Panel de Administración - BoxEvent

Panel completo de administración para gestionar peleadores, clubs y dueños.

## 📋 Características

### 1. **Dashboard Principal**
- Estadísticas en tiempo real
- Peleadores pendientes de aprobación
- Peleadores aprobados
- Clubs activos
- Usuarios activos
- Acceso rápido a todas las secciones

### 2. **Aprobar Peleadores**
- Lista de todos los peleadores pendientes
- Ver información completa de cada peleador:
  - Datos personales (nombre, DNI, edad)
  - Estadísticas (peso, altura, récord)
  - Información de contacto
  - Club al que pertenece
- Aprobar o rechazar solicitudes
- Agregar notas del administrador
- Interfaz intuitiva con confirmaciones

### 3. **Gestión de Clubs**
- Ver listado completo de clubs
- Crear nuevos clubs con formulario completo:
  - Nombre (obligatorio)
  - Dirección
  - Teléfono
  - Email
  - Descripción
- Ver estadísticas de cada club:
  - Cantidad de managers
  - Cantidad de peleadores
- Información de contacto de cada club

### 4. **Asignar Dueños**
- Buscar usuarios por DNI
- Ver información completa del usuario encontrado
- Seleccionar club para asignar
- Convertir automáticamente al usuario en manager
- Advertencias si el usuario ya es manager de otro club

## 🚀 Uso

### Acceso al Panel
```typescript
import { AdminPanel } from './screens/admin';
import ProtectedRoute from './components/ProtectedRoute';

// En tu router o navegación
<ProtectedRoute requiredRole="admin">
  <AdminPanel />
</ProtectedRoute>
```

### Protección de Rutas
El componente `ProtectedRoute` verifica:
- Si el usuario está autenticado
- Si tiene el rol requerido (admin por defecto)
- Muestra mensajes de error si no cumple los requisitos

## 🔧 API Endpoints Utilizados

### Estadísticas
```
GET /api/admin/estadisticas
```

### Peleadores
```
GET /api/admin/peleadores-pendientes
PUT /api/admin/peleadores/{id}
Body: { estado: 'aprobado' | 'rechazado', notas: string }
```

### Clubs
```
GET /api/admin/clubs
POST /api/admin/clubs
Body: { nombre, direccion?, telefono?, email?, descripcion? }
```

### Asignar Dueños
```
GET /api/admin/buscar-usuario?dni={dni}
POST /api/admin/asignar-duenio
Body: { usuario_id, club_id }
```

## 📦 Estructura de Archivos

```
screens/admin/
├── AdminPanel.tsx           # Componente principal con navegación
├── ApprovalFighters.tsx     # Aprobar/rechazar peleadores
├── ClubsManagement.tsx      # Gestión de clubs
├── AssignOwners.tsx         # Asignar dueños a clubs
├── index.ts                 # Exports
└── README.md                # Esta documentación

services/
└── AdminService.ts          # Servicio para llamadas API

components/
└── ProtectedRoute.tsx       # HOC para proteger rutas
```

## 🎨 Diseño

- Tema oscuro (#1a1a1a, #2c2c2c)
- Color primario: #e74c3c (rojo)
- Colores secundarios:
  - Verde: #27ae60 (aprobado, success)
  - Azul: #3498db (clubs)
  - Naranja: #f39c12 (warnings, managers)
  - Morado: #9b59b6 (estadísticas)

## ⚠️ Requisitos

1. El usuario debe tener rol `admin` (tipo_id = 1)
2. La API debe estar configurada y corriendo
3. La base de datos debe tener la estructura correcta

## 🔐 Seguridad

- Solo usuarios con rol `admin` pueden acceder
- Todas las acciones críticas requieren confirmación
- Validación de datos en frontend y backend
- Mensajes de error claros y seguros

## 🚧 Próximas Funcionalidades

- [ ] Generación de PDFs y reportes
- [ ] Estadísticas avanzadas con gráficos
- [ ] Gestión de eventos desde el panel
- [ ] Logs de actividad del administrador
- [ ] Exportar datos a Excel/CSV
- [ ] Sistema de notificaciones

## 💡 Ejemplos de Uso

### Aprobar un peleador
1. Ir a la sección "Peleadores"
2. Revisar la información del peleador
3. Opcionalmente agregar notas
4. Click en "Aprobar" o "Rechazar"
5. Confirmar la acción

### Crear un nuevo club
1. Ir a la sección "Clubs"
2. Click en "+ Nuevo Club"
3. Llenar el formulario (solo nombre es obligatorio)
4. Click en "Crear Club"

### Asignar dueño a un club
1. Ir a la sección "Dueños"
2. Ingresar el DNI del peleador
3. Click en "Buscar"
4. Revisar la información
5. Seleccionar el club
6. Click en "Asignar como Dueño"
7. Confirmar

## 🐛 Resolución de Problemas

### "No se pudieron cargar las estadísticas"
- Verificar que la API esté corriendo
- Verificar la conexión a la base de datos
- Revisar que exista la tabla `usuarios`, `peleadores`, `clubs`

### "No se encontró usuario con ese DNI"
- Verificar que el DNI esté correcto
- El usuario debe ser un peleador registrado
- El DNI debe existir en la tabla `peleadores`

### "Ya existe un club con ese nombre"
- Los nombres de clubs deben ser únicos
- Verificar que no exista el club en la base de datos

## 📞 Soporte

Para problemas o sugerencias, contactar al equipo de desarrollo.
