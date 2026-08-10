FROM composer:2 as build
WORKDIR /app
COPY . /app
RUN composer install --no-dev --optimize-autoloader --no-interaction

FROM php:8.2-apache
WORKDIR /var/www/html
COPY --from=build /app /var/www/html

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_sqlite

# Configurar Apache
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

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