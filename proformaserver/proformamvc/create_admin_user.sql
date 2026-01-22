-- Script para crear un usuario administrador inicial
-- Usuario: admin
-- Contraseña: admin123

INSERT INTO users (username, password, nombre, email, rol, activo) 
VALUES (
  'admin',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Administrador',
  'admin@ejemplo.com',
  'admin',
  1
);

-- NOTA: La contraseña hasheada corresponde a "admin123"
-- Cambia la contraseña después del primer inicio de sesión
