#!/bin/sh
set -e

echo "🚀 Iniciando PuntoKampus en Render..."

# Configurar puerto dinámico de Apache según variable de entorno de Render
PORT_TO_USE=${PORT:-80}
echo "Listen ${PORT_TO_USE}" > /etc/apache2/ports.conf
sed -ri -e "s!<VirtualHost \*:.*>!<VirtualHost \*:${PORT_TO_USE}>!g" /etc/apache2/sites-available/*.conf

# Verificar si APP_KEY está definida; si no, generar una
if [ -z "$APP_KEY" ]; then
    echo "🔑 APP_KEY no detectada, generando clave de cifrado..."
    php artisan key:generate --force
fi

# Enlace de almacenamiento asegurado
echo "📦 Asegurando symlink de storage..."
mkdir -p /var/www/html/storage/app/public/logos \
         /var/www/html/storage/app/public/productos \
         /var/www/html/storage/app/public/portadas
chmod -R 775 /var/www/html/storage/app/public
rm -f /var/www/html/public/storage || true
php artisan storage:link --force || true

# Publicar assets de Filament
echo "🎨 Publicando assets de Filament..."
php artisan filament:assets || true

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
