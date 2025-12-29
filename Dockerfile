# Usa uma imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala dependências do sistema e extensões PHP necessárias para Laravel/Postgres
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Habilita o mod_rewrite do Apache (essencial para rotas do Laravel)
RUN a2enmod rewrite

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto para dentro do container
COPY . /var/www/html

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala dependências do PHP
RUN composer install --no-dev --optimize-autoloader

# Ajusta permissões das pastas de armazenamento
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configura o Apache para apontar para a pasta public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \ /etc/apache2/conf-available/*.conf

# Instala Node.js e NPM para compilar o Tailwind (Build do Frontend)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run build

# Expõe a porta 80
EXPOSE 80

# Comando para iniciar o servidor
CMD ["apache2-foreground"]