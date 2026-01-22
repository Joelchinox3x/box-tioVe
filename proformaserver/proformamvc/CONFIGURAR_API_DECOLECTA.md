# Configuración API Decolecta

## ✓ CONFIGURACIÓN COMPLETA

Los endpoints de Decolecta han sido verificados con la documentación oficial y están completamente configurados.

**Referencias:**
- **Documentación oficial**: https://decolecta.gitbook.io/docs/
- **Postman Collection**: https://www.postman.com/decolecta-api/decolecta-api/overview
- **Soporte**: dev@decolecta.com o WhatsApp +51 918 510 800

## Endpoints VERIFICADOS ✓

### DNI (RENIEC) ✓
```
GET https://api.decolecta.com/v1/reniec/dni?numero=46027897
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

**Respuesta exitosa (200):**
```json
{
  "first_name": "ROXANA KARINA",
  "first_last_name": "DELGADO",
  "second_last_name": "CUELLAR",
  "full_name": "DELGADO CUELLAR ROXANA KARINA",
  "document_number": "46027896"
}
```

### RUC (SUNAT) ✓
```
GET https://api.decolecta.com/v1/sunat/ruc?numero=20601030013
Headers:
  Authorization: Bearer {token}
  Content-Type: application/json
```

**Respuesta exitosa (200):**
```json
{
  "razon_social": "REXTIE S.A.C.",
  "numero_documento": "20601030013",
  "estado": "ACTIVO",
  "condicion": "HABIDO",
  "direccion": "AV. JOSE GALVEZ BARRENECHEA NRO 566 INT. 101 URB. CORPAC ",
  "ubigeo": "150131",
  "via_tipo": "AV.",
  "via_nombre": "JOSE GALVEZ BARRENECHEA",
  "zona_codigo": "URB.",
  "zona_tipo": "CORPAC",
  "numero": "566",
  "interior": "101",
  "distrito": "SAN ISIDRO",
  "provincia": "LIMA",
  "departamento": "LIMA",
  "es_agente_retencion": false,
  "es_buen_contribuyente": false
}
```

## Cómo Configurar

1. **Obtener Token**:
   - Visita https://decolecta.com/profile/
   - Copia tu token API

2. **Configurar en Settings**:
   - Ve a la sección "Configuración" en el sistema
   - Activa "Habilitar Búsqueda DNI/RUC"
   - Selecciona "Decolecta" como proveedor
   - Pega tu token (se mostrará como ✓ cuando esté configurado)

3. **Verificar Funcionamiento**:
   - Ve a "Crear Cliente"
   - Ingresa un DNI o RUC válido
   - Haz clic en el botón de búsqueda (lupa)
   - Debe rellenar automáticamente los campos

## Mapeo de Campos

### DNI (RENIEC)
| Campo Decolecta      | Campo Sistema       | Descripción                    |
|---------------------|---------------------|--------------------------------|
| `full_name`         | `nombre_completo`   | Nombre completo de la persona |
| `first_name`        | -                   | Nombres                        |
| `first_last_name`   | -                   | Apellido paterno              |
| `second_last_name`  | -                   | Apellido materno              |
| `document_number`   | -                   | Número de DNI                 |

### RUC (SUNAT)
| Campo Decolecta     | Campo Sistema       | Descripción                    |
|---------------------|---------------------|--------------------------------|
| `razon_social`      | `nombre_completo`   | Razón social de la empresa    |
| `direccion`         | `direccion`         | Dirección completa            |
| `distrito`          | -                   | Distrito                       |
| `provincia`         | -                   | Provincia                      |
| `departamento`      | -                   | Departamento                   |
| `estado`            | -                   | Estado del RUC (ACTIVO, etc)  |
| `condicion`         | -                   | Condición (HABIDO, etc)       |

## Archivos Configurados

El servicio está en:
`app/Services/ApiDniRuc/DecolectaService.php`

**Estado actual:**
- ✓ DNI (RENIEC): Completamente configurado y funcional
- ✓ RUC (SUNAT): Completamente configurado y funcional

**Características:**
- Método: GET con query parameter `?numero={documento}`
- Headers: `Authorization: Bearer {token}`, `Content-Type: application/json`
- Logging detallado en `/tmp/decolecta_debug.log`
- Manejo de errores HTTP con mensajes descriptivos

**Logs de depuración:**
Todos los requests se registran en `/tmp/decolecta_debug.log` con información detallada:
- Token usado (primeros 20 caracteres por seguridad)
- Documento consultado
- Tipo de documento (DNI o RUC)
- Endpoint llamado
- Código HTTP de respuesta
- Errores de CURL (si los hay)
- Respuesta completa del API (raw y decodificada)
