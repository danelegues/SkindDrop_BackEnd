FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpng-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite
WORKDIR /var/www/html
COPY . .
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install
CMD ["apache2-foreground"]
