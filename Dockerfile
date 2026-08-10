FROM composer:2 as build
WORKDIR /app
COPY . /app
RUN composer install --no-dev --optimize-autoloader --no-interaction

FROM php:8.2-apache
WORKDIR /var/www/html
COPY --from=build /app /var/www/html

# Instalar dependencias del sistema para extensiones PHP
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_sqlite mbstring xml exif

# Configurar Apache para Laravel
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar DocumentRoot para apuntar a public/
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' /etc/apache2/sites-available/000-default.conf

# Crear archivo .env si no existe
RUN cp .env.example .env || echo "APP_NAME=AutoGest\nAPP_ENV=production\nAPP_DEBUG=false\nAPP_KEY=\nAPP_URL=https://autogest-taller-management-api.onrender.com\nDB_CONNECTION=sqlite\nDB_DATABASE=/var/www/html/database/database.sqlite\nCACHE_DRIVER=array\nSESSION_DRIVER=cookie\nQUEUE_CONNECTION=sync\nLOG_CHANNEL=errorlog" > .env

# Generar APP_KEY automáticamente
RUN php artisan key:generate --ansi

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Crear directorio para base de datos SQLite
RUN mkdir -p /var/www/html/database && touch /var/www/html/database/database.sqlite \
    && chown www-data:www-data /var/www/html/database/database.sqlite

EXPOSE 80
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
CMD ["apache2-foreground"]