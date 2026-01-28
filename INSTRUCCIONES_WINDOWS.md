# Guía de Configuración en Windows

Sigue estos pasos para clonar y ejecutar el proyecto en tu PC con Windows.

## 1. Prerrequisitos
Asegúrate de tener instalados:
- **Git**: [Descargar aquí](https://git-scm.com/download/win)
- **Node.js (LTS)**: [Descargar aquí](https://nodejs.org/en/download/)
- **VS Code**: [Descargar aquí](https://code.visualstudio.com/)
- **XAMPP** (Opcional, si necesitas MySQL localmente y no usas Docker)

## 2. Clonar el repositorio
1. Abre una terminal (PowerShell, CMD o Git Bash) en la carpeta donde quieras guardar el proyecto.
2. Ejecuta el siguiente comando:
   ```bash
   git clone https://github.com/Joelchinox3x/box-tioVe.git
   ```
3. Entra a la carpeta del proyecto:
   ```bash
   cd box-tioVe
   ```
4. Abre el proyecto en VS Code:
   ```bash
   code .
   ```

## 3. Configurar Variables de Entorno
Este paso es **CRITICO** porque el archivo `.env` no se descarga de GitHub (por seguridad).
Crea un archivo llamado `.env` en la raíz del proyecto y pega el siguiente contenido (ajusta las contraseñas si tu MySQL local es diferente):

```env
MYSQL_ROOT_PASSWORD=Cocacola@123
MYSQL_DATABASE=eventobox_db
MYSQL_USER=server_admin
MYSQL_PASSWORD=Cocacola123
```

## 4. Instalar Dependencias
Este proyecto es un "Monorepo" (contiene tanto el backend como el frontend).

### Backend (Node.js)
```bash
cd backend
npm install
```

### Frontend (React Native / Expo)
Abre una nueva terminal (o regresa a la raíz con `cd ..`) y ve a la carpeta frontend:
```bash
cd frontend
npm install
```

### MCP (Servidor de Contexto - Opcional)
Si vas a usar la IA localmente con MCP:
```bash
cd mcp
npm install
```

## 5. Ejecutar el Proyecto

### Backend
En la terminal del backend:
```bash
# Iniciar servidor
node server.js
# O si usas npm start si está configurado
npm start
```

### Frontend
En la terminal del frontend:
```bash
npx expo start
```
Esto abrirá un código QR. Escanéalo con tu celular (usando la app Expo Go) o presiona `w` para abrirlo en el navegador web.

## Notas Adicionales
- Si obtienes errores de conexión a base de datos, verifica que tu servicio MySQL (XAMPP o Docker) esté activo y que las credenciales en `.env` coincidan.
- Si usas Docker, puedes levantar todo el entorno con:
  ```bash
  docker-compose up -d
  ```
