# LUISICHOCOLATES - Despliegue en Railway

## Requisitos previos
- Cuenta en [Railway.app](https://railway.app)
- Git instalado
- Docker instalado (opcional, para probar localmente)

## Pasos para desplegar

### 1. Subir a GitHub
```bash
cd /home/wolfkali/Documentos/New\ OpenCode\ Project/chocolatier
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/luisichocolates.git
git push -u origin main
```

### 2. Crear proyecto en Railway
1. Entra a [Railway.app](https://railway.app) y crea cuenta
2. Click en "New Project" → "Deploy from GitHub repo"
3. Selecciona tu repositorio
4. Railway detectará el `Dockerfile` automáticamente

### 3. Agregar base de datos PostgreSQL
1. En tu proyecto Railway, click en "New Service" → "Database" → "PostgreSQL"
2. Railway creará la variable `DATABASE_URL` automáticamente

### 4. Configurar variables de entorno (opcional)
En Railway → Variables, agrega:
- `SITE_URL=https://tu-app.up.railway.app` (la URL que te da Railway)

### 5. Desplegar
Railway hará build y deploy automáticamente. Verás la URL en "Settings" → "Domains".

### 6. Inicializar base de datos
En Railway → tu servicio → "Variables" → "Shell" (o Connect):
```bash
php setup_db.php
```

### 7. Acceder al admin
- URL: `https://tu-app.up.railway.app/admin/`
- Usuario: `admin`
- Contraseña: `chocolatier2026`

---

## Desarrollo local

### Con Docker
```bash
docker build -t luisichocolates .
docker run -p 8080:8080 -v $(pwd)/data:/var/www/html/data -v $(pwd)/uploads:/var/www/html/uploads luisichocolates
```
Luego abre `http://localhost:8080`

### Con PHP nativo (sin Docker)
```bash
# Instalar PHP 8.2+ y extensiones: pdo, pdo_sqlite, gd
cd chocolatier
php -S localhost:8000
# O para que funcione el routing:
php -S localhost:8000 -t .
```

Luego abre `http://localhost:8000` y admin en `http://localhost:8000/admin/`

---

## Estructura del proyecto
```
chocolatier/
├── Dockerfile              # Imagen para Railway
├── docker/apache.conf      # Config Apache
├── setup_db.php           # Inicializa BD (SQLite/PostgreSQL)
├── config.php             # Config DB dual (SQLite/PostgreSQL)
├── api/
│   ├── DB.php             # Wrapper unificado SQLite3/PDO
│   ├── productos.php
│   ├── categorias.php
│   ├── categorias_admin.php
│   ├── config_site.php
│   ├── slider.php
│   ├── update_config.php
│   └── upload.php
├── admin/index.php        # Panel admin
├── index.html             # Frontend
├── css/style.css
├── js/app.js
├── uploads/               # Imágenes subidas (volumen persistente)
└── data/                  # SQLite local (volumen persistente)
```

---

## Solución de problemas

### Error 500 en Railway
- Revisa logs en Railway → tu servicio → "Logs"
- Asegúrate de que `setup_db.php` se ejecutó

### Imágenes no se suben
- Verifica que el volumen `/var/www/html/uploads` esté montado
- En Railway: Settings → Volumes → Add Volume → Mount path: `/var/www/html/uploads`

### Base de datos no persiste
- En Railway usa PostgreSQL (gratis), no SQLite
- Si usas SQLite local, monta volumen en `data/`

---

## Cambiar contraseña admin
En el panel admin: pestaña "Admin" → nuevo usuario/contraseña → Guardar.

O desde Railway Shell:
```sql
php -r "require 'config.php'; \$db=new DB(); \$db->exec(\"UPDATE site_config SET value='nueva_pass' WHERE section='admin' AND key='admin_pass'\");"
```