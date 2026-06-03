FROM ghcr.io/roadrunner-server/roadrunner:2025.1.0 AS roadrunner
FROM php:8.5-cli

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    $PHPIZE_DEPS \
    && docker-php-ext-install sockets mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://pecl.php.net/get/redis -o /tmp/redis.tgz \
    || curl -fsSL http://pecl.php.net/get/redis -o /tmp/redis.tgz \
    && pecl install /tmp/redis.tgz \
    && docker-php-ext-enable redis \
    && rm -f /tmp/redis.tgz

WORKDIR /var/www/html

CMD ["rr", "serve", "-c", ".rr.yaml"]
