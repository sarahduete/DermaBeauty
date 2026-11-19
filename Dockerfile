
FROM php:8.2-apache

# Instala suporte ao banco de dados SQLite
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Copia os arquivos do projeto
COPY . /var/www/html/

# Permissões para o banco de dados
RUN chown -R www-data:www-data /var/www/html/database \
    && chmod -R 775 /var/www/html/database

EXPOSE 80
