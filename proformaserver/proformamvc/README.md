# ProformaMVC - Sistema de Gestión de Proformas

Sistema web profesional para crear, gestionar y generar proformas en PDF con múltiples templates personalizables. Desarrollado con arquitectura MVC en PHP puro.

---

## Descripción del Proyecto

**ProformaMVC** es una aplicación web para la empresa **Tradimacova** que permite:
- **Sistema de autenticación completo** (login, registro, logout)
- Gestionar clientes y productos
- Crear proformas comerciales con múltiples ítems
- Generar PDFs profesionales con 3 templates diferentes (orange, blue, simple)
- Incluir fichas técnicas y galerías de fotos de productos en los PDFs
- Administrar especificaciones técnicas de productos
- Control de acceso con PIN para clientes protegidos
- **Sistema global de notificaciones** tipo toast
- **Interfaz moderna** con Tailwind CSS y diseño responsive

---

## Tecnologías Utilizadas

### Backend
- **PHP 7.4+** - Lenguaje principal
- **MySQL/MariaDB** - Base de datos
- **Docker** - Contenedor `mi_mysql` para la base de datos
- **mPDF 8.2** - Librería para generación de PDFs

### Frontend
- HTML5, CSS3, JavaScript vanilla
- **Tailwind CSS 3.x** (CDN) - Framework de utilidades
- **Phosphor Icons** - Librería de iconos
- **Sistema de notificaciones** personalizado (toast notifications)
- Google Fonts (Outfit)

### Arquitectura
- **Patrón MVC** (Model-View-Controller)
- **PSR-4 Autoloading** con Composer
- **Routing personalizado** en `core/Router.php`

---

## Estructura del Proyecto

```
proformamvc/
│
├── app/                          # Lógica de la aplicación
│   ├── Controllers/              # Controladores MVC
│   │   ├── ProformaController.php    # Gestión de proformas (CRUD + PDF)
│   │   ├── ClienteController.php     # Gestión de clientes
│   │   ├── ProductoController.php    # Gestión de productos
│   │   ├── AuthController.php        # Autenticación
│   │   ├── HomeController.php        # Dashboard
│   │   └── SettingsController.php    # Configuraciones
│   │
│   ├── Models/                   # Modelos de datos
│   │   ├── Proforma.php             # Modelo de proformas
│   │   ├── Cliente.php              # Modelo de clientes
│   │   ├── Producto.php             # Modelo de productos
│   │   └── User.php                 # Modelo de usuarios
│   │
│   ├── Services/                 # Servicios de negocio
│   │   ├── PdfService.php           # Generación de PDFs
│   │   ├── ProformaService.php      # Lógica de proformas
│   │   └── ImageService.php         # Procesamiento de imágenes
│   │
│   ├── Pdf/                      # Motor de PDFs
│   │   ├── PdfEngine.php            # Motor principal de generación
│   │   └── ViewRenderer.php         # Renderizador de vistas
│   │
│   ├── Views/                    # Vistas (templates HTML)
│   │   ├── proformas/               # Vistas de proformas
│   │   ├── clientes/                # Vistas de clientes
│   │   ├── productos/               # Vistas de productos
│   │   ├── pdf/                     # TEMPLATES DE PDF
│   │   │   ├── orange/              # Template naranja (Tradimacova)
│   │   │   │   ├── styles.php           # Estilos CSS del PDF
│   │   │   │   ├── hoja1.php            # Página 1: Proforma principal
│   │   │   │   ├── hoja2_specs.php      # Página 2: Ficha técnica
│   │   │   │   ├── hoja3_fotos.php      # Página 3: Fotos del producto
│   │   │   │   ├── hoja4_galeria.php    # Página 4: Galería adicional
│   │   │   │   └── partials/            # Componentes reutilizables
│   │   │   ├── blue/                # Template azul
│   │   │   ├── simple/              # Template simple
│   │   │   └── *.png                # Fondos de PDFs (1.png - 6.png)
│   │   ├── layouts/                 # Layouts principales
│   │   ├── partials/                # Componentes compartidos
│   │   └── home/                    # Dashboard
│   │
│   └── Helpers/                  # Funciones auxiliares
│
├── core/                         # Núcleo del framework MVC
│   ├── Router.php                   # Sistema de rutas
│   ├── Controller.php               # Controlador base
│   ├── Model.php                    # Modelo base con PDO
│   ├── Database.php                 # Conexión a MySQL
│   └── helpers.php                  # Funciones globales
│
├── config/                       # Configuraciones
│   └── config.php                   # Config principal (DB, paths, PDF)
│
├── public/                       # Carpeta pública (webroot)
│   ├── index.php                    # Punto de entrada
│   ├── css/                         # Estilos globales
│   │   ├── global.css               # CSS centralizado (componentes, animaciones)
│   │   └── notifications.css        # Estilos del sistema de notificaciones
│   ├── js/                          # JavaScript
│   │   └── notifications.js         # Sistema global de notificaciones toast
│   ├── assets/                      # CSS, JS, imágenes legacy
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   ├── uploads/                     # Archivos subidos
│   │   ├── pdfs/                    # PDFs generados
│   │   ├── productos/               # Imágenes de productos
│   │   └── clientes/                # Fotos de clientes
│   └── tmp/                         # Archivos temporales
│
├── database/                     # Base de datos
│   └── schema.sql                   # Esquema de la BD (ver más abajo)
│
├── vendor/                       # Dependencias de Composer
│   ├── mpdf/                        # Librería mPDF
│   └── ...
│
├── composer.json                 # Dependencias PHP
└── README.md                     # Este archivo

```

---

## Base de Datos

### Configuración de Conexión
```php
Host: mi_mysql (contenedor Docker)
Database: proformamvc
User: server_admin
Password: Cocacola123
Charset: utf8mb4
```

### Tablas Principales

#### 1. `clientes`
Almacena información de clientes
- **Campos**: id, nombre, dni_ruc, direccion, telefono, email, foto_url, latitud, longitud, protegido, fecha_creacion, fecha_modificacion
- **Índices**: dni_ruc, nombre

#### 2. `productos`
Catálogo de productos
- **Campos**: id, nombre, modelo, sku, precio, moneda, descripcion, imagenes (JSON array), bloqueado, fecha_creacion, fecha_modificacion
- **Índices**: sku, nombre

#### 3. `producto_specs`
Especificaciones técnicas de productos (relación 1:N con productos)
- **Campos**: id, producto_id, atributo, valor, orden
- **FK**: producto_id → productos(id) ON DELETE CASCADE

#### 4. `proformas`
Datos principales de cada proforma
- **Campos**: id, cliente_id, correlativo (ej: PRF00001), fecha_creacion, vigencia_dias, moneda, subtotal, descuento, igv, total, pdf_path, observaciones, condiciones, **template** (orange/blue/simple)
- **FK**: cliente_id → clientes(id)
- **Índices**: correlativo (UNIQUE), fecha_creacion, cliente_id

#### 5. `proforma_items`
Ítems/líneas de cada proforma (relación 1:N con proformas)
- **Campos**: id, proforma_id, producto_id, descripcion, cantidad, precio_unitario, subtotal, orden, **incluir_ficha**, **incluir_fotos**, **incluir_galeria**
- **FK**:
  - proforma_id → proformas(id) ON DELETE CASCADE
  - producto_id → productos(id) ON DELETE SET NULL

#### 6. `users`
Usuarios del sistema (autenticación)
- **Campos**: id, username, password (bcrypt), nombre, email, rol, activo, fecha_creacion, ultimo_acceso
- **Índices**: username (UNIQUE), email (UNIQUE)
- **Roles**: admin, user
- **Autenticación**: Sistema de login/register con middleware de protección de rutas

---

## Flujo de Generación de PDFs

### 1. Proceso Principal
```
ProformaController::pdf($id)
    ↓
PdfService::generateProformaPdf($proforma)
    ↓
PdfEngine::generate($data, $totals, $outputPath)
    ↓
mPDF renderiza las hojas según el template
    ↓
PDF guardado en public/uploads/pdfs/
```

### 2. Templates Disponibles

#### **ORANGE** (Template principal - Tradimacova)
- Color brand: `#f37021` (Naranja)
- Fuente: Teko (títulos) + Sans-serif
- Fondo: Imagen de marca (1.png con `background-image-resize: 6`)
- Hojas:
  1. **hoja1.php**: Proforma principal con items y totales
  2. **hoja2_specs.php**: Ficha técnica (si `incluir_ficha = 1`)
  3. **hoja3_fotos.php**: Fotos del producto (si `incluir_fotos = 1`)
  4. **hoja4_galeria.php**: Galería adicional (si `incluir_galeria = 1`)

#### **BLUE** (Alternativo)
- Similar estructura pero con paleta azul

#### **SIMPLE** (Minimalista)
- Sin fondos, diseño limpio

### 3. Archivos Clave de PDF

#### `PdfEngine.php`
Motor principal que:
- Inicializa mPDF con configuración
- Carga el template seleccionado
- Renderiza las hojas condicionales (specs, fotos, galería)
- Exporta el PDF final

#### `styles.php` (por template)
Define:
- Variables de color dinámicas
- Estilos CSS para mPDF
- Configuración de fondos
- Tipografía y layout

#### Flags de Control (en `proforma_items`)
```php
incluir_ficha = 1    // Añade hoja2_specs.php al PDF
incluir_fotos = 1    // Añade hoja3_fotos.php al PDF
incluir_galeria = 1  // Añade hoja4_galeria.php al PDF
```

---

## Características Especiales

### 1. Sistema de Autenticación
- **Login/Register**: Formularios modernos con validación
- **Middleware**: `AuthMiddleware` protege todas las rutas excepto `/login` y `/register`
- **Sesiones PHP**: Manejo seguro de sesiones
- **Password Hashing**: bcrypt (PASSWORD_DEFAULT)
- **Control de Acceso**: Verificación de usuario activo
- **Logout**: Destrucción completa de sesión

### 2. Sistema Global de Notificaciones
- **Ubicación**: `public/js/notifications.js`
- **Funciones Disponibles**:
  ```javascript
  mostrarNotificacion(titulo, mensaje, tipo, duracion)
  notifySuccess(titulo, mensaje)
  notifyError(titulo, mensaje)
  notifyWarning(titulo, mensaje)
  notifyInfo(titulo, mensaje)
  ```
- **Tipos**: success (verde), error (rojo), warning (amarillo), info (azul)
- **Animaciones**: Slide-in desde la derecha, auto-dismiss
- **Estilos**: `public/css/notifications.css`

### 3. Arquitectura CSS Centralizada
- **`public/css/global.css`**: CSS principal con:
  - Animaciones (@keyframes): fadeInUp, fadeIn, fadeOut, slide-in, scale-in, pulse, spin
  - Componentes: botones (.btn-primary, .btn-secondary), cards, inputs, badges, modals
  - Utilidades: no-scrollbar, glassmorphism, modal-overlay
- **Beneficios**: Mejor rendimiento, mantenimiento centralizado, cacheo del navegador

### 4. Sistema de Correlativos
- Formato: `PRF00001`, `PRF00002`, ...
- Autogenerado en `Proforma::getNextCorrelativo()`

### 5. Clientes Protegidos
- Campo `protegido = 1` en tabla `clientes`
- Requiere PIN (123) para editar/eliminar
- Configurado en `config/config.php`

### 6. Productos con Galería
- Campo `imagenes` en JSON: `["img1.jpg", "img2.jpg", ...]`
- Procesado en `ImageService.php`
- Se muestran en hoja3 y hoja4 del PDF

### 7. Especificaciones Dinámicas
- Tabla `producto_specs` con atributos personalizables
- Renderizadas en hoja2_specs.php
- Estilo alternado automático con CSS `nth-child`

---

## Rutas Principales

```php
// Autenticación
GET  /login                  → showLogin (formulario)
POST /login                  → login (procesar login)
GET  /register               → showRegister (formulario)
POST /register               → register (crear cuenta)
GET  /logout                 → logout (cerrar sesión)

// Proformas (requieren autenticación)
GET  /proformas              → index (listado)
GET  /proformas/create       → create (formulario)
POST /proformas/store        → store (guardar)
GET  /proformas/show/{id}    → show (detalle)
GET  /proformas/pdf/{id}     → pdf (generar PDF)
GET  /proformas/viewPdf/{id} → viewPdf (ver PDF en navegador)
GET  /proformas/edit/{id}    → edit (formulario edición)
POST /proformas/update/{id}  → update (actualizar)
GET  /proformas/delete/{id}  → delete (eliminar)

// Clientes (requieren autenticación)
GET  /clientes               → index
GET  /clientes/create        → create
POST /clientes/store         → store
GET  /clientes/edit/{id}     → edit
POST /clientes/update/{id}   → update
GET  /clientes/delete/{id}   → delete
...

// Inventario/Productos (requieren autenticación)
GET  /inventario             → index
GET  /inventario/create      → create
POST /inventario/store       → store
...

// Configuración (requiere autenticación)
GET  /settings               → index
POST /settings/change-theme  → changeTheme
POST /settings/change-navbar → changeNavbar
...
```

---

## Configuración Inicial

### 1. Dependencias
```bash
composer install
```

### 2. Base de Datos
Importar el esquema desde `database/schema.sql`:
```bash
sudo docker exec -i mi_mysql mysql -userver_admin -pCocacola123 proformamvc < database/schema.sql
```

### 3. Permisos
```bash
chmod -R 777 public/uploads
chmod -R 777 public/pdfs
chmod -R 777 public/tmp
```

### 4. Configuración
Editar `config/config.php` si es necesario:
- Credenciales de BD
- Rutas de archivos
- PIN de seguridad
- Template por defecto

---

## Últimas Modificaciones

**Fecha**: 27 de diciembre de 2024, 06:15 AM

**Archivos modificados**:
1. `app/Views/pdf/orange/styles.php` - Estilos del template naranja
2. `app/Views/pdf/blue/hoja2_specs.php` - Ficha técnica template azul
3. `app/Views/pdf/simple/styles.php` - Estilos template simple
4. `app/Views/proformas/index.php` - Vista de listado
5. `app/Controllers/ProformaController.php` - Controlador principal
6. `app/Pdf/PdfEngine.php` - Motor de PDFs
7. `app/Services/PdfService.php` - Servicio de generación

**Cambios recientes**:
- Ajustes en los estilos CSS del template orange
- Optimización de la renderización de PDFs
- Mejoras en el motor de generación

---

## Notas para Desarrollo Futuro

### Si necesitas modificar el Template ORANGE:

1. **Estilos**: Edita `app/Views/pdf/orange/styles.php`
   - Variables de color al inicio del archivo
   - Clases CSS para mPDF
   - Configuración de fondo (background-image-resize)

2. **Layout Hoja 1**: Edita `app/Views/pdf/orange/hoja1.php`
   - Encabezado con logo y RUC
   - Tabla de items
   - Totales y condiciones

3. **Ficha Técnica**: Edita `app/Views/pdf/orange/hoja2_specs.php`
   - Tabla de especificaciones
   - Datos del motor (si aplica)

4. **Imágenes de Fondo**: Reemplaza los archivos PNG en `app/Views/pdf/`
   - 1.png, 2.png, etc.

### Si necesitas crear un nuevo template:

1. Copia la carpeta `app/Views/pdf/orange/` y renómbrala (ej: `green`)
2. Modifica los archivos dentro:
   - `styles.php` → Cambia colores y estilos
   - `hoja*.php` → Ajusta layouts
3. Actualiza `config/config.php`:
   ```php
   'pdf' => [
       'default_template' => 'green',
       ...
   ]
   ```

### Base de Datos
- El esquema completo está en `database/schema.sql`
- Para hacer backup:
  ```bash
  sudo docker exec mi_mysql mysqldump -userver_admin -pCocacola123 proformamvc > backup.sql
  ```

### Debugging de PDFs
- mPDF escribe errores en excepciones
- Revisa los archivos generados en `public/uploads/pdfs/`
- Los estilos CSS de mPDF tienen limitaciones (no soporta todo CSS3)

---

## Soporte y Contacto

**Empresa**: Tradimacova
**Proyecto**: ProformaMVC
**Versión**: 1.0
**Framework**: MVC Custom PHP
**Librería PDF**: mPDF 8.2

---

*Este README ha sido creado para facilitar el entendimiento del proyecto en futuras sesiones de desarrollo.*
