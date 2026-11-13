# Guía de Despliegue con HTTPS — PuntoKampus

Este documento detalla los requerimientos y pasos para desplegar el servidor backend de PuntoKampus en un servidor en producción con HTTPS bajo el presupuesto objetivo (servidor pequeño con PHP 8.2+/8.3 y PostgreSQL, máx. $15/mes).

---

## 1. Requerimientos del Servidor
- **OS:** Ubuntu 22.04 LTS o 24.04 LTS (o Debian 12).
- **CPU / RAM:** 1 vCPU, 1 GB a 2 GB RAM (e.g., VPS en Hetzner Cloud, DigitalOcean, Linode).
- **PHP:** 8.2 o 8.3 con extensiones: `php-cli`, `php-pgsql`, `php-mbstring`, `php-xml`, `php-bcmath`, `php-curl`, `php-zip`, `php-intl`.
- **Base de Datos:** PostgreSQL 14+ (con extensión `unaccent`).
- **Servidor Web:** Nginx + Certbot (Let's Encrypt para SSL/HTTPS gratuito).
- **Composer:** 2.x.

---

## 2. Pasos de Instalación en el VPS

### Paso 2.1: Paquetes del Sistema
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx postgresql postgresql-contrib git curl unzip \
    php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-intl certbot python3-certbot-nginx
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

Configurar en `.env`:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://api.tudominio.com`
- `DB_CONNECTION=pgsql`
- `DB_HOST=127.0.0.1`
- `DB_DATABASE=puntokampus`
- `DB_USERNAME=puntokampus_user`
- `DB_PASSWORD=TU_PASSWORD_SEGURO`

Ejecutar migraciones y seeders:
```bash
php artisan migrate --force
php artisan db:seed --force
```

Ajustar permisos de almacenamiento:
```bash
sudo chown -R www-data:www-data /var/www/puntokampus/servidor/storage /var/www/puntokampus/servidor/bootstrap/cache
sudo chmod -R 775 /var/www/puntokampus/servidor/storage /var/www/puntokampus/servidor/bootstrap/cache
```

### Paso 2.4: Configurar Nginx con HTTPS
Crear `/etc/nginx/sites-available/puntokampus`:
```nginx
server {
    server_name api.tudominio.com;
    root /var/www/puntokampus/servidor/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

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
Activar y solicitar certificado SSL:
```bash
sudo ln -s /etc/nginx/sites-available/puntokampus /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d api.tudominio.com
```

### Paso 2.5: Respaldos Diarios de Base de Datos
Configurar un cron diario para volcar la BD fuera del servidor:
```bash
crontab -e
# Respaldar todos los días a las 02:00 UTC
0 2 * * * pg_dump -U puntokampus_user -h 127.0.0.1 puntokampus | gzip > /backups/puntokampus_$(date +\%F).sql.gz
```

