# MCP Antigravity Server

Este servidor MCP proporciona acceso a la base de datos y API de EventoBox.

## Herramientas Disponibles (Tools)

### 1. `db_query`
Ejecuta consultas SQL de lectura (SELECT) en la base de datos `eventobox_db`.
- **Argumentos**:
  - `query` (string): La consulta SQL a ejecutar.
- **Uso**: Consultar datos de usuarios, fighters, etc.

### 2. `db_list_tables`
Lista todas las tablas disponibles en la base de datos.
- **Argumentos**: Ninguno.

### 3. `db_describe_table`
Muestra la estructura (columnas, tipos) de una tabla específica.
- **Argumentos**:
  - `tableName` (string): Nombre de la tabla.

### 4. `api_request`
Realiza peticiones HTTP a la API local (`http://localhost:8080/api`).
- **Argumentos**:
  - `method` (string): 'GET', 'POST', 'PUT', 'DELETE'.
  - `endpoint` (string): Ruta de la API (ej. `/usuarios`).
  - `data` (object, opcional): Datos para el cuerpo de la petición (POST/PUT).

### 5. `search_github_repos`
Busca repositorios en GitHub.
- **Argumentos**:
  - `query` (string): Palabras clave de búsqueda.
  - `limit` (number, opcional): Máximo de resultados (default 5).

### 6. `scan_api_docs`
Escanea el código del backend (`index.php`) en busca de rutas documentadas automáticamente.
- **Argumentos**: Ninguno.
- **Retorno**: Lista de endpoints encontrados (Método, Ruta, Descripción).

## Configuración
- **Database Host**: `mi_mysql`
- **Database Name**: `eventobox_db`
- **API URL**: `http://localhost:8080/api`
