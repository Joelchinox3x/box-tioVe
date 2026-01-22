# Configuración de GitHub para ProformaMVC

## Estado Actual

✅ Repositorio Git inicializado
✅ Archivo `.gitignore` configurado
✅ Primer commit creado (127 archivos)
✅ README actualizado
✅ Rama principal: `main`

## Pasos para Conectar con GitHub

### 1. Crear Repositorio en GitHub

Ve a GitHub y crea un nuevo repositorio:
- **Opción 1**: https://github.com/new
- **Nombre sugerido**: `proforma-mvc` o `tradimacova-proformas`
- **Visibilidad**: Privado (recomendado) o Público
- **NO inicialices** con README, .gitignore o licencia (ya los tienes localmente)

### 2. Conectar tu Repositorio Local con GitHub

Después de crear el repositorio en GitHub, ejecuta estos comandos:

```bash
# Navegar a tu proyecto
cd /home/server/evento-box/proformaserver/proformamvc

# Agregar el repositorio remoto (reemplaza TU_USUARIO con tu nombre de usuario de GitHub)
git remote add origin https://github.com/TU_USUARIO/proforma-mvc.git

# O si usas SSH:
git remote add origin git@github.com:TU_USUARIO/proforma-mvc.git

# Verificar que se agregó correctamente
git remote -v

# Subir tu código a GitHub
git push -u origin main
```

### 3. Autenticación

Si GitHub te pide credenciales:

#### Opción A: HTTPS con Personal Access Token (Recomendado)
1. Ve a GitHub Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Crea un nuevo token con permisos `repo`
3. Copia el token
4. Úsalo como contraseña cuando hagas `git push`

#### Opción B: SSH (Más seguro para uso continuo)
```bash
# Generar clave SSH (si no tienes una)
ssh-keygen -t ed25519 -C "tu_email@ejemplo.com"

# Copiar la clave pública
cat ~/.ssh/id_ed25519.pub

# Agregar la clave en GitHub:
# Settings → SSH and GPG keys → New SSH key
# Pega el contenido de id_ed25519.pub
```

### 4. Comandos Git Útiles para el Futuro

```bash
# Ver estado de cambios
git status

# Ver diferencias
git diff

# Agregar cambios
git add .
git add archivo_especifico.php

# Hacer commit
git commit -m "Descripción del cambio"

# Subir cambios a GitHub
git push

# Descargar cambios desde GitHub
git pull

# Ver historial
git log --oneline

# Crear una nueva rama
git checkout -b nombre-rama

# Cambiar de rama
git checkout main

# Ver ramas
git branch -a
```

## Estructura de Commits Recomendada

Usa mensajes descriptivos:

```bash
# Características nuevas
git commit -m "feat: Agregar búsqueda de productos por SKU"

# Correcciones
git commit -m "fix: Corregir cálculo de IGV en proformas"

# Documentación
git commit -m "docs: Actualizar guía de instalación"

# Estilos/formato
git commit -m "style: Mejorar diseño de cards de proformas"

# Refactorización
git commit -m "refactor: Optimizar queries de base de datos"
```

## .gitignore Configurado

Ya tienes un `.gitignore` que excluye:
- ✅ `/vendor/` - Dependencias de Composer
- ✅ `.env*` - Archivos de configuración sensibles
- ✅ Archivos IDE (.vscode, .idea)
- ✅ Logs y cache
- ✅ Uploads (opcional, puedes modificarlo)

## Archivos Importantes a Revisar Antes de Subir

Asegúrate de **NO** subir información sensible:

```bash
# Revisa estos archivos:
grep -r "password" config/
grep -r "Cocacola123" .
```

Si encuentras contraseñas hardcodeadas:
1. Muévelas a un archivo `.env` (ya está en .gitignore)
2. Usa variables de entorno en `config/config.php`

Ejemplo de `.env`:
```
DB_HOST=mi_mysql
DB_NAME=proformamvc
DB_USER=server_admin
DB_PASS=Cocacola123
```

## Comandos de Verificación

```bash
# Ver qué archivos se van a subir
git ls-files

# Contar archivos
git ls-files | wc -l

# Ver tamaño del repositorio
du -sh .git

# Ver ramas
git branch -a

# Ver último commit
git log -1
```

## Colaboración (Opcional)

Si trabajarás en equipo:

```bash
# Agregar colaboradores:
# GitHub → Settings → Manage access → Invite a collaborator

# Clonar el repositorio (para otros desarrolladores)
git clone https://github.com/TU_USUARIO/proforma-mvc.git
cd proforma-mvc
composer install
# Configurar base de datos local
```

## Backup Adicional

Además de GitHub, considera:

```bash
# Backup de base de datos
sudo docker exec mi_mysql mysqldump -userver_admin -pCocacola123 proformamvc > backup_$(date +%Y%m%d).sql

# Comprimir todo el proyecto
tar -czf proformamvc_backup_$(date +%Y%m%d).tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='.git' \
  /home/server/evento-box/proformaserver/proformamvc
```

## Resolución de Problemas

### Error: "remote origin already exists"
```bash
git remote remove origin
git remote add origin https://github.com/TU_USUARIO/proforma-mvc.git
```

### Error: "failed to push some refs"
```bash
git pull origin main --rebase
git push origin main
```

### Error: "Authentication failed"
- Verifica tu token de acceso personal
- O configura SSH correctamente

### Ver archivo ignorado por error
```bash
git rm --cached archivo.php
echo "archivo.php" >> .gitignore
git commit -m "Remover archivo sensible del repositorio"
```

## Commits Actuales

Tienes 2 commits listos para subir:
1. `Initial commit: Sistema completo de gestión de proformas`
2. `Actualizar README con nuevas funcionalidades`

Total: 127 archivos, 17,276 líneas insertadas

## Siguiente Paso

**Ejecuta ahora**:
```bash
# 1. Crear repositorio en GitHub.com
# 2. Copiar la URL del repositorio
# 3. Ejecutar:
git remote add origin https://github.com/TU_USUARIO/nombre-repo.git
git push -u origin main
```

¡Tu código estará respaldado en GitHub! 🚀
