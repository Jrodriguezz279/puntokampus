FROM php:8.2-apache

# 1. Instalar dependencias del sistema y extensiones de PHP requeridas
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        intl \
        gd \
        zip \
        bcmath \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Habilitar mod_rewrite de Apache para Laravel
RUN a2enmod rewrite

# 3. Configurar Apache DocumentRoot a /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. Copiar código de la aplicación
WORKDIR /var/www/html
COPY . /var/www/html

# Asegurar carpetas y permisos antes de composer install
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Instalar dependencias de producción de Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. Permisos de carpetas de almacenamiento y caché para Apache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Script de inicio
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
