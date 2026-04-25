FROM php:8.1-cli-alpine

# Системные зависимости
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    $PHPIZE_DEPS

# PHP-расширения: pdo_mysql для работы с БД, intl для i18n, zip для composer
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip intl bcmath

# Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Сначала только composer.json/lock — кешируем слой зависимостей
COPY composer.json composer.lock* /app/
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction || true

# Потом весь код
COPY . /app

RUN composer dump-autoload --optimize --no-dev --no-interaction || true \
    && chmod +x /app/yii 2>/dev/null || true

EXPOSE 8080

# Встроенный PHP веб-сервер Yii раздаёт из /app/web
# entrypoint.sh делает миграции и seed перед запуском
CMD ["/bin/sh", "-c", "/app/entrypoint.sh"]
