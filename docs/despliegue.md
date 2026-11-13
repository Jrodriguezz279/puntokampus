# Guía de Despliegue con HTTPS — PuntoKampus

Este documento detalla los requerimientos, pasos de instalación, configuración y mantenimiento para desplegar el servidor backend de PuntoKampus en producción con HTTPS bajo el presupuesto objetivo (servidor pequeño con PHP 8.2+/8.3 y PostgreSQL, máx. $15/mes).

---

## 1. Requerimientos del Servidor

- **OS:** Ubuntu 22.04 LTS o 24.04 LTS (o Debian 12).
- **CPU / RAM:** 1 vCPU, 1 GB a 2 GB RAM (e.g., VPS en Hetzner Cloud, DigitalOcean, Linode).
- **PHP:** 8.2 o 8.3 con extensiones: `php-cli`, `php-pgsql`, `php-mbstring`, `php-xml`, `php-bcmath`, `php-curl`, `php-zip`, `php-intl`, `php-gd`.
- **Base de Datos:** PostgreSQL 14+ (con extensión `unaccent` activa).
- **Servidor Web:** Nginx + Certbot (Let's Encrypt para certificados SSL/HTTPS gratuitos).
- **Composer:** 2.x.
- **Node.js:** 20+ (para compilar assets de Filament si fuera necesario).

---

## 2. Pasos de Instalación en el VPS

### Paso 2.1: Paquetes del Sistema
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx postgresql postgresql-contrib git curl unzip \
    php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-intl php8.3-gd \
    certbot python3-certbot-nginx supervisor
```

### Paso 2.2: Configurar PostgreSQL
```bash
sudo -u postgres psql
```
```sql
CREATE DATABASE puntokampus;
CREATE USER puntokampus_user WITH ENCRYPTED PASSWORD 'TU_PASSWORD_SEGURO';
GRANT ALL PRIVILEGES ON DATABASE puntokampus TO puntokampus_user;
\c puntokampus
CREATE EXTENSION IF NOT EXISTS unaccent;
GRANT ALL ON SCHEMA public TO puntokampus_user;
\q
```

### Paso 2.3: Clonar el Repositorio e Instalar Dependencias
```bash
cd /var/www
git clone <URL_REPO> puntokampus
cd puntokampus/servidor
cp .env.example .env

composer install --no-dev --optimize-autoloader
php artisan key:generate
```

### Paso 2.4: Variables de Entorno (`.env`)
Configurar en `/var/www/puntokampus/servidor/.env`:

```env
APP_NAME=PuntoKampus
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://api.puntokampus.utb.edu.ec

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=puntokampus
DB_USERNAME=puntokampus_user
DB_PASSWORD=TU_PASSWORD_SEGURO

# Enlace simbólico de archivos públicos
FILESYSTEM_DISK=public

# Configuración de Correo (SMTP institucional o servicio transaccional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com # o smtp institucional
MAIL_PORT=587
MAIL_USERNAME=notificaciones@puntokampus.utb.edu.ec
MAIL_PASSWORD=TU_TOKEN_O_APP_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notificaciones@puntokampus.utb.edu.ec
MAIL_FROM_NAME="PuntoKampus UTB"

# Sesiones y Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Paso 2.5: Migraciones, Seeders y Enlace de Storage
```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

# Permisos de carpetas
sudo chown -R www-data:www-data /var/www/puntokampus/servidor/storage /var/www/puntokampus/servidor/bootstrap/cache
sudo chmod -R 775 /var/www/puntokampus/servidor/storage /var/www/puntokampus/servidor/bootstrap/cache
```

### Paso 2.6: Configurar Nginx con HTTPS
Crear `/etc/nginx/sites-available/puntokampus`:
```nginx
server {
    server_name api.puntokampus.utb.edu.ec;
    root /var/www/puntokampus/servidor/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php;
    charset utf-8;

    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activar sitio y emitir certificado SSL:
```bash
sudo ln -s /etc/nginx/sites-available/puntokampus /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d api.puntokampus.utb.edu.ec
```

---

## 3. Tareas Programadas y Cron (Laravel Scheduler)

PuntoKampus requiere que el Scheduler de Laravel se ejecute cada minuto para gestionar las 3 tareas automáticas:
1. `puntokampus:vencer-pedidos` (cada 15 minutos): Vence pedidos pendientes con más de 24 horas.
2. `puntokampus:vencer-premium` (diario a las 00:05): Degrada a plan gratis tiendas cuyo plazo de Premium finalizó.
3. `puntokampus:purgar-comprobantes` (diario a las 02:00): Elimina comprobantes resueltos con más de 30 días.

Agregar la siguiente línea al crontab del usuario `www-data`:
```bash
sudo crontab -u www-data -e
```
```cron
* * * * * cd /var/www/puntokampus/servidor && php artisan schedule:run >> /dev/null 2>&1
```

---

## 4. Respaldos Diarios de Base de Datos

El script automatizado realiza un volcado comprimido de la base de datos `puntokampus` y conserva los últimos 30 días de copias:

```bash
sudo mkdir -p /var/backups/puntokampus
sudo chown -R postgres:postgres /var/backups/puntokampus
```

Agregar al crontab de `postgres` o `root`:
```bash
sudo crontab -e
```
```cron
# Respaldo todos los días a las 03:00 AM UTC
0 3 * * * /var/www/puntokampus/servidor/scripts/respaldo-bd.sh
```

---

## 5. Endpoints y URLs Públicas de Producción

| URL | Acceso | Propósito |
|---|---|---|
| `https://api.puntokampus.utb.edu.ec/api/v1/salud` | Público | Healthcheck del API |
| `https://api.puntokampus.utb.edu.ec/privacidad` | Público | Política de privacidad (enlace para Google Play y web) |
| `https://api.puntokampus.utb.edu.ec/borrar-cuenta` | Público | Formulario web de solicitud de borrado de cuenta |
| `https://api.puntokampus.utb.edu.ec/admin` | Administradores | Panel de control Filament (moderación, reportes, premium) |
