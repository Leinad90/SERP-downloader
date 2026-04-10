FROM php:8.4-apache

# Instalace potřebných PHP rozšíření
RUN apt update && apt upgrade libicu-dev git zip sudo -y && docker-php-ext-configure intl && docker-php-ext-install intl && a2enmod rewrite

# Nainstalujte Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Nastavení pracovního adresáře
WORKDIR /var/www/html

# Vytvoření uživatele a nastavení oprávnění
RUN useradd -G www-data,root -u 1000 -m composeruser

# Otevření portu
EXPOSE 80