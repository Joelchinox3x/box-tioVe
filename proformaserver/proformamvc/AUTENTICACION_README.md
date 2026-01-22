# Sistema de Autenticación - Instrucciones

## ✅ Instalación Completa

He implementado un sistema completo de login y registro para proteger tu aplicación. Aquí están los pasos para activarlo:

### 1. Crear la tabla de usuarios

Ejecuta el siguiente SQL en tu base de datos (ya te lo proporcionaste):

```sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `rol` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'user',
  `activo` tinyint(1) NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE,
  INDEX `idx_username`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
```

### 2. Crear un usuario administrador inicial

Opción A: **Usar el archivo SQL** (recomendado)
```bash
mysql -u tu_usuario -p tu_base_de_datos < create_admin_user.sql
```

Credenciales por defecto:
- **Usuario:** admin
- **Contraseña:** admin123

Opción B: **Registrarte manualmente**
- Ve a `/register` en tu navegador
- Crea tu cuenta

### 3. Acceder a la aplicación

1. Ve a tu aplicación en el navegador
2. Serás redirigido automáticamente a `/login`
3. Ingresa tus credenciales
4. ¡Listo! Ahora puedes usar la aplicación

## 📁 Archivos Creados

### Modelo
- `app/Models/User.php` - Modelo para gestionar usuarios

### Controlador
- `app/Controllers/AuthController.php` - Controlador de autenticación con:
  - `showLogin()` - Muestra formulario de login
  - `login()` - Procesa el login
  - `showRegister()` - Muestra formulario de registro
  - `register()` - Procesa el registro
  - `logout()` - Cierra la sesión

### Vistas
- `app/Views/auth/login.php` - Vista de inicio de sesión
- `app/Views/auth/register.php` - Vista de registro

### Middleware
- `app/Middleware/AuthMiddleware.php` - Protege las rutas

### Configuración
- `public/index.php` - Actualizado con rutas y protección

## 🔒 Funcionalidades

### Login
- Validación de credenciales
- Verificación de usuario activo
- Actualización de último acceso
- Mensajes de error amigables

### Registro
- Validación de campos (username mínimo 3 caracteres, password mínimo 6)
- Verificación de usuario y email únicos
- Confirmación de contraseña
- Hash seguro de contraseñas (bcrypt)

### Protección de Rutas
- Todas las rutas están protegidas excepto `/login` y `/register`
- Usuarios no autenticados son redirigidos automáticamente al login
- Usuarios autenticados no pueden acceder a login/register

### Cerrar Sesión
- Botón en la página de Settings
- Confirmación antes de cerrar sesión

## 🎨 Diseño

Las vistas usan el mismo diseño moderno de tu aplicación:
- Tailwind CSS
- Phosphor Icons
- Diseño responsive
- Animaciones suaves
- Sin navbar en login/register

## 🔐 Seguridad

- Contraseñas hasheadas con `password_hash()` (bcrypt)
- Validación de sesiones
- Protección CSRF disponible (puedes agregar tokens)
- Verificación de usuarios activos

## 📝 Notas Importantes

1. **Cambia la contraseña del admin** después del primer login
2. El sistema usa sesiones PHP nativas
3. Puedes desactivar usuarios cambiando el campo `activo` a 0
4. Los roles están listos para implementar permisos en el futuro

## 🚀 Próximos pasos (opcional)

- Agregar recuperación de contraseña
- Implementar tokens CSRF
- Sistema de roles y permisos más avanzado
- Login con "Recordarme"
- Límite de intentos de login

## ❓ Problemas Comunes

**No puedo acceder después del login:**
- Verifica que la tabla `users` exista
- Confirma que el usuario esté `activo = 1`
- Revisa los permisos de sesión PHP

**Error al registrarse:**
- El username o email ya existe
- Verifica la conexión a la base de datos

**Me redirige siempre al login:**
- Verifica que la sesión esté iniciada en `index.php`
- Confirma que las credenciales sean correctas
