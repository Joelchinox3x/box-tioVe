# 🎯 Cómo Acceder al Panel de Administración

## 📱 Desde la Aplicación

### Paso 1: Iniciar Sesión como Admin
1. Abre la aplicación
2. Ve a la pestaña **"Perfil"** (última pestaña)
3. Click en **"INICIAR SESIÓN"**
4. Ingresa las credenciales de administrador:
   - **Email**: `admin@boxevent.com`
   - **Password**: `password`
5. Click en **"INICIAR SESIÓN"**

### Paso 2: Acceder al Panel Admin
Una vez iniciada la sesión como admin, verás un botón especial en tu perfil:

🛡️ **"PANEL DE ADMINISTRACIÓN"** (botón rojo con sombra)

Este botón **SOLO es visible para usuarios con rol de administrador** (tipo_id = 1).

Click en el botón y serás redirigido al Panel de Administración completo.

---

## 🎛️ Funciones del Panel Admin

Una vez dentro del panel, tendrás acceso a:

### 1️⃣ Dashboard
- Estadísticas en tiempo real
- Acceso rápido a todas las secciones

### 2️⃣ Peleadores
- Aprobar o rechazar peleadores pendientes
- Ver información completa de cada peleador
- Agregar notas del administrador

### 3️⃣ Clubs
- Ver todos los clubs registrados
- Crear nuevos clubs
- Ver estadísticas de cada club

### 4️⃣ Dueños
- Buscar usuarios por DNI
- Asignar dueños/managers a clubs
- Convertir peleadores en managers

---

## 👥 Usuarios de Prueba

### Administrador Principal
```
Email: admin@boxevent.com
Password: password
Rol: admin (tipo_id = 1)
```

### Manager de Club (Ejemplo)
```
Email: juan@elcampeon.com
Password: password
Rol: manager_club (tipo_id = 4)
Club: Gimnasio El Campeón
```

### Espectador (Ejemplo)
```
Email: carlos@test.com
Password: password
Rol: espectador (tipo_id = 3)
```

---

## 🔐 Seguridad

### El botón del Panel Admin:
✅ Solo aparece si `user.tipo_id === 1`
✅ Usa verificación en el backend también
✅ Requiere autenticación válida
✅ Incluye feedback háptico al presionar

### Comportamiento:
- Si no eres admin → El botón no aparece
- Si no has iniciado sesión → No puedes acceder al perfil
- Si intentas acceder directamente a la ruta → Protección de ruta (ProtectedRoute)

---

## 📊 Verificar Rol en Base de Datos

Si necesitas verificar o cambiar el rol de un usuario:

```sql
-- Ver todos los usuarios y sus roles
SELECT u.id, u.nombre, u.email, t.nombre as rol
FROM usuarios u
JOIN tipos_usuario t ON u.tipo_id = t.id;

-- Hacer a un usuario administrador
UPDATE usuarios
SET tipo_id = 1
WHERE email = 'tu-email@ejemplo.com';

-- Ver tipos de roles disponibles
SELECT * FROM tipos_usuario;
```

### Roles disponibles:
- `1` = admin
- `2` = peleador
- `3` = espectador
- `4` = manager_club

---

## 🎨 Diseño del Botón

El botón de Panel Admin tiene un diseño especial:
- **Color**: Rojo (#e74c3c)
- **Icono**: Escudo con check (shield-checkmark)
- **Sombra**: Efecto de elevación
- **Posición**: Justo antes del botón de cerrar sesión
- **Texto**: "PANEL DE ADMINISTRACIÓN"

---

## 🔄 Flujo Completo

```
1. Abrir App
   ↓
2. Ir a Perfil
   ↓
3. Iniciar Sesión (admin@boxevent.com / password)
   ↓
4. Ver botón "PANEL DE ADMINISTRACIÓN"
   ↓
5. Click en el botón
   ↓
6. Acceder al Dashboard del Panel Admin
   ↓
7. Navegar entre: Dashboard, Peleadores, Clubs, Dueños
```

---

## 🐛 Solución de Problemas

### "No veo el botón del Panel Admin"
✅ Verifica que iniciaste sesión
✅ Verifica que tu usuario tenga `tipo_id = 1`
✅ Cierra sesión y vuelve a iniciar

### "El botón no hace nada al presionarlo"
✅ Verifica que AdminPanel esté importado en AppNavigator
✅ Verifica que la ruta 'AdminPanel' exista
✅ Revisa la consola por errores

### "Error al cargar estadísticas"
✅ Verifica que el backend esté corriendo
✅ Verifica la conexión a la base de datos
✅ Verifica que las rutas API estén configuradas

---

## 📝 Archivos Modificados

Para habilitar el acceso al panel se modificaron:

1. **ProfileScreen.tsx**
   - Agregado botón condicional para admin
   - Navegación al AdminPanel
   - Estilos del botón

2. **AppNavigator.tsx**
   - Importado AdminPanel
   - Agregada ruta oculta 'AdminPanel'

3. **Todos los componentes del admin/** ya estaban creados

---

## ✅ Listo para Usar

El panel está **100% funcional** y accesible desde el perfil de cualquier usuario administrador.

**¡Disfruta gestionando tu aplicación de boxeo! 🥊**
