FROM php:8.1-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo_mysql

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Рабочая директория
WORKDIR /var/www/html

# Копируем composer.json и composer.lock (если есть)
COPY app/composer.* ./

# Установка зависимостей (Silex и компоненты)
RUN composer install --no-dev --optimize-autoloader \
    && composer require "silex/silex:~2.0" \
    && composer require symfony/twig-bridge symfony/validator doctrine/dbal \
    && chown -R www-data:www-data /var/www/html

# Запуск PHP-FPM
CMD ["php-fpm"]

RUN composer install