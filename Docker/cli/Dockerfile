FROM php:8.5-cli

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    $PHPIZE_DEPS \
    && docker-php-ext-install sockets \
    && rm -rf /var/lib/apt/lists/*

# Установка Composer (только в CLI)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www/html

# Права
RUN chown -R www-data:www-data /var/www/html