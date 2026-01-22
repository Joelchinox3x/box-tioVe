# Documentación de Base de Datos - ProformaMVC

## Información de Conexión

```
Host: mi_mysql (contenedor Docker)
Puerto: 3306
Base de datos: proformamvc
Usuario: server_admin
Password: Cocacola123
Charset: utf8mb4
Collation: utf8mb4_unicode_ci
```

---

## Diagrama de Relaciones

```
users
  (tabla independiente - autenticación)

clientes (1) ←──── (N) proformas (1) ←──── (N) proforma_items (N) ──→ (1) productos (1) ──→ (N) producto_specs
```

---

## Descripción de Tablas

### 1. `clientes`
**Descripción**: Almacena información de los clientes de Tradimacova.

**Columnas**:
- `id` INT PK AUTO_INCREMENT
- `nombre` VARCHAR(255) NOT NULL - Nombre o razón social
- `dni_ruc` VARCHAR(20) - DNI o RUC del cliente
- `direccion` TEXT - Dirección completa
- `telefono` VARCHAR(20) - Teléfono de contacto
- `email` VARCHAR(100) - Email de contacto
- `foto_url` VARCHAR(255) - URL/path de la foto del cliente
- `latitud` DECIMAL(10,8) - Coordenada GPS (latitud)
- `longitud` DECIMAL(11,8) - Coordenada GPS (longitud)
- `protegido` TINYINT(1) DEFAULT 0 - Si es 1, requiere PIN para editar/eliminar
- `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `fecha_modificacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

**Índices**:
- PRIMARY KEY: `id`
- INDEX: `idx_dni_ruc` (dni_ruc)
- INDEX: `idx_nombre` (nombre)

**Relaciones**:
- → `proformas.cliente_id` (1:N)

---

### 2. `productos`
**Descripción**: Catálogo de productos/maquinaria que se ofertan.

**Columnas**:
- `id` INT PK AUTO_INCREMENT
- `nombre` VARCHAR(255) NOT NULL - Nombre del producto
- `modelo` VARCHAR(100) - Modelo o código del fabricante
- `sku` VARCHAR(50) - SKU interno
- `precio` DECIMAL(10,2) NOT NULL - Precio base
- `moneda` VARCHAR(3) DEFAULT 'PEN' - Moneda (PEN, USD)
- `descripcion` TEXT - Descripción del producto
- `imagenes` TEXT - JSON array de rutas de imágenes: ["img1.jpg", "img2.jpg"]
- `bloqueado` TINYINT(1) DEFAULT 0 - Si es 1, no se puede modificar
- `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `fecha_modificacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

**Índices**:
- PRIMARY KEY: `id`
- INDEX: `idx_sku` (sku)
- INDEX: `idx_nombre` (nombre)

**Relaciones**:
- → `producto_specs.producto_id` (1:N)
- → `proforma_items.producto_id` (1:N)

**Nota**: El campo `imagenes` es un JSON string, ejemplo:
```json
["uploads/productos/producto1_img1.jpg", "uploads/productos/producto1_img2.jpg"]
```

---

### 3. `producto_specs`
**Descripción**: Especificaciones técnicas de cada producto (ficha técnica).

**Columnas**:
- `id` INT PK AUTO_INCREMENT
- `producto_id` INT NOT NULL FK → productos(id)
- `atributo` VARCHAR(100) NOT NULL - Nombre del atributo (ej: "Potencia")
- `valor` TEXT NOT NULL - Valor del atributo (ej: "150 HP")
- `orden` INT DEFAULT 0 - Orden de aparición en la ficha

**Índices**:
- PRIMARY KEY: `id`
- INDEX: `idx_producto` (producto_id)
- FOREIGN KEY: `producto_id` → `productos(id)` ON DELETE CASCADE

**Ejemplo de datos**:
```
producto_id | atributo      | valor        | orden
------------|---------------|--------------|------
1           | Potencia      | 150 HP       | 0
1           | Voltaje       | 220V         | 1
1           | Peso          | 250 kg       | 2
```

---

### 4. `proformas`
**Descripción**: Datos principales de cada proforma comercial.

**Columnas**:
- `id` INT PK AUTO_INCREMENT
- `cliente_id` INT NOT NULL FK → clientes(id)
- `correlativo` VARCHAR(20) UNIQUE NOT NULL - Código único (ej: PRF00001)
- `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `vigencia_dias` INT DEFAULT 30 - Días de validez de la proforma
- `moneda` VARCHAR(3) DEFAULT 'PEN' - Moneda (PEN, USD)
- `subtotal` DECIMAL(10,2) DEFAULT 0.00
- `descuento` DECIMAL(10,2) DEFAULT 0.00
- `igv` DECIMAL(10,2) DEFAULT 0.00 - Impuesto (18%)
- `total` DECIMAL(10,2) DEFAULT 0.00
- `pdf_path` VARCHAR(255) - Ruta del PDF generado
- `observaciones` TEXT - Notas adicionales
- `condiciones` TEXT - Condiciones comerciales
- `template` VARCHAR(20) DEFAULT 'orange' - Template del PDF (orange/blue/simple)

**Índices**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `correlativo`
- INDEX: `idx_correlativo` (correlativo)
- INDEX: `idx_fecha` (fecha_creacion)
- INDEX: `idx_cliente` (cliente_id)
- FOREIGN KEY: `cliente_id` → `clientes(id)` ON DELETE RESTRICT

**Relaciones**:
- `cliente_id` ← clientes (N:1)
- → `proforma_items.proforma_id` (1:N)

**Notas**:
- El `correlativo` se genera automáticamente como PRF00001, PRF00002, etc.
- El campo `template` determina qué carpeta de vistas usar para el PDF

---

### 5. `proforma_items`
**Descripción**: Líneas/ítems de cada proforma (productos cotizados).

**Columnas**:
- `id` INT PK AUTO_INCREMENT
- `proforma_id` INT NOT NULL FK → proformas(id)
- `producto_id` INT NULL FK → productos(id) - Puede ser NULL si es item manual
- `descripcion` TEXT NOT NULL - Descripción del item
- `cantidad` INT NOT NULL
- `precio_unitario` DECIMAL(10,2) NOT NULL
- `subtotal` DECIMAL(10,2) NOT NULL - cantidad * precio_unitario
- `orden` INT DEFAULT 0 - Orden de aparición en la proforma
- `incluir_ficha` TINYINT(1) DEFAULT 0 - Si es 1, se añade hoja2_specs al PDF
- `incluir_fotos` TINYINT(1) DEFAULT 0 - Si es 1, se añade hoja3_fotos al PDF
- `incluir_galeria` TINYINT(1) DEFAULT 0 - Si es 1, se añade hoja4_galeria al PDF

**Índices**:
- PRIMARY KEY: `id`
- INDEX: `idx_proforma` (proforma_id)
- INDEX: `producto_id`
- FOREIGN KEY: `proforma_id` → `proformas(id)` ON DELETE CASCADE
- FOREIGN KEY: `producto_id` → `productos(id)` ON DELETE SET NULL

**Relaciones**:
- `proforma_id` ← proformas (N:1)
- `producto_id` ← productos (N:1, opcional)

**Flags de PDF**:
- `incluir_ficha = 1`: Genera página con especificaciones técnicas del producto
- `incluir_fotos = 1`: Genera página con fotos principales del producto
- `incluir_galeria = 1`: Genera página con galería completa de imágenes

---

### 6. `users`
**Descripción**: Usuarios del sistema (autenticación).

**Columnas**:
- `id` INT PK AUTO_INCREMENT
- `username` VARCHAR(50) UNIQUE NOT NULL
- `password` VARCHAR(255) NOT NULL - Hash de contraseña
- `nombre` VARCHAR(100) - Nombre completo
- `email` VARCHAR(100) - Email del usuario
- `rol` VARCHAR(20) DEFAULT 'user' - Rol (admin, user)
- `activo` TINYINT(1) DEFAULT 1 - Si es 0, no puede acceder
- `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- `ultimo_acceso` TIMESTAMP NULL

**Índices**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `username`
- INDEX: `idx_username` (username)

**Nota**: Las contraseñas deben guardarse hasheadas con `password_hash()` de PHP.

---

## Scripts SQL Útiles

### Importar esquema
```bash
sudo docker exec -i mi_mysql mysql -userver_admin -pCocacola123 proformamvc < database/schema.sql
```

### Backup completo (estructura + datos)
```bash
sudo docker exec mi_mysql mysqldump -userver_admin -pCocacola123 proformamvc > backup_completo.sql
```

### Backup solo estructura
```bash
sudo docker exec mi_mysql mysqldump -userver_admin -pCocacola123 --no-data proformamvc > schema.sql
```

### Backup solo datos
```bash
sudo docker exec mi_mysql mysqldump -userver_admin -pCocacola123 --no-create-info proformamvc > datos.sql
```

### Consultar proformas con items
```sql
SELECT
    p.correlativo,
    p.fecha_creacion,
    c.nombre AS cliente,
    p.total,
    COUNT(pi.id) AS total_items
FROM proformas p
LEFT JOIN clientes c ON p.cliente_id = c.id
LEFT JOIN proforma_items pi ON p.proforma_id = pi.id
GROUP BY p.id
ORDER BY p.fecha_creacion DESC;
```

### Consultar productos con specs
```sql
SELECT
    pr.nombre,
    pr.modelo,
    ps.atributo,
    ps.valor
FROM productos pr
LEFT JOIN producto_specs ps ON pr.id = ps.producto_id
WHERE pr.id = 1
ORDER BY ps.orden;
```

---

## Migración y Mantenimiento

### Crear nueva migración
1. Crear archivo en `database/migrations/` con formato: `YYYY_MM_DD_nombre_migracion.sql`
2. Ejemplo: `2024_12_28_add_campo_nuevo.sql`

### Limpiar PDFs antiguos
```sql
-- Listar proformas con PDFs generados
SELECT id, correlativo, pdf_path, fecha_creacion
FROM proformas
WHERE pdf_path IS NOT NULL;

-- Si quieres eliminar referencias a PDFs antiguos
UPDATE proformas SET pdf_path = NULL WHERE fecha_creacion < '2024-01-01';
```

### Resetear correlativos
```sql
-- Ver último correlativo
SELECT MAX(CAST(SUBSTRING(correlativo, 4) AS UNSIGNED)) AS max_num
FROM proformas
WHERE correlativo LIKE 'PRF%';

-- No se recomienda resetear, pero si es necesario:
-- ALTER TABLE proformas AUTO_INCREMENT = 1;
```

---

## Notas Técnicas

### Charset y Collation
- Se usa `utf8mb4` para soportar emojis y caracteres especiales
- Collation `utf8mb4_unicode_ci` para ordenamiento correcto en español

### Foreign Keys
- `ON DELETE CASCADE`: Al eliminar padre, se eliminan hijos (proforma → items, producto → specs)
- `ON DELETE SET NULL`: Al eliminar padre, se pone NULL en hijo (producto eliminado, item permanece)
- `ON DELETE RESTRICT`: No permite eliminar padre si tiene hijos (cliente con proformas)

### Timestamps
- `fecha_creacion`: Se establece automáticamente al INSERT
- `fecha_modificacion`: Se actualiza automáticamente al UPDATE

### JSON en MySQL
- El campo `imagenes` en `productos` es TEXT, no JSON nativo
- Se almacena como string JSON y se decodifica en PHP con `json_decode()`
- Ejemplo: `'["img1.jpg", "img2.jpg"]'`

---

*Última actualización: 28 de diciembre de 2024*
