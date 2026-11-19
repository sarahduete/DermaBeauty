FROM php:8.2-apache

COPY . /var/www/html/

# Permissões necessárias para o SQLite
RUN mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html/database \
    && find /var/www/html/database -type d -exec chmod 775 {} \; \
    && find /var/www/html/database -type f -exec chmod 664 {} \;

EXPOSE 80
