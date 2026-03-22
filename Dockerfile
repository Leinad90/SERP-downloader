FROM php:8.5-apache

# Instalace potřebných PHP rozšíření
RUN docker-php-ext-install pdo pdo_mysql \
    && apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-enable pdo_mysql

# Nainstalujte Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Nastavení pracovního adresáře
WORKDIR /var/www/html

# Kopírování zdrojových souborů
COPY . .

# Otevření portu
EXPOSE 80