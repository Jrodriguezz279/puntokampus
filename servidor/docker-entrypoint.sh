#!/bin/sh
set -e

echo "🚀 Iniciando PuntoKampus en Render..."

# Configurar puerto dinámico de Apache según variable de entorno de Render
PORT_TO_USE=${PORT:-80}
sed -ri -e "s!Listen [0-9]+!Listen ${PORT_TO_USE}!g" /etc/apache2/ports.conf
sed -ri -e "s!<VirtualHost \*:[0-9]+>!<VirtualHost \*:${PORT_TO_USE}>!g" /etc/apache2/sites-available/*.conf

# Verificar si APP_KEY está definida; si no, generar una
if [ -z "$APP_KEY" ]; then
    echo "🔑 APP_KEY no detectada, generando clave de cifrado..."
    php artisan key:generate --force
fi

# Enlace de almacenamiento si no existe
if [ ! -L /var/www/html/public/storage ]; then
    echo "📦 Creando symlink de storage..."
    php artisan storage:link || true
fi

# Optimización y caché de rutas y vistas
echo "⚡ Optimizando caché de Laravel..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Ejecutar migraciones y seeders automáticos en producción
echo "🗄️ Ejecutando migraciones y seeders en PostgreSQL..."
php artisan migrate --force --seed || echo "⚠️ Advertencia en migraciones, continuando..."

echo "✅ PuntoKampus listo para servir peticiones en el puerto ${PORT_TO_USE}!"

# Iniciar Apache en primer plano
exec apache2-foreground
