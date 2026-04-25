#!/bin/sh
# Entrypoint контейнера backend.
# 1) Ждём MySQL
# 2) composer install (если vendor пуст после bind-mount)
# 3) миграции + seed
# 4) Запускаем встроенный PHP-сервер

set -e

cd /app

echo "[entrypoint] Проверяю composer-зависимости..."
if [ ! -d /app/vendor/yiisoft ]; then
    composer install --no-interaction --prefer-dist
fi

echo "[entrypoint] Ожидаю MySQL ($DB_HOST:${DB_PORT:-3306})..."
for i in $(seq 1 60); do
    if php -r "try { new PDO('mysql:host=$DB_HOST;port=${DB_PORT:-3306};dbname=$DB_NAME', '$DB_USER', '$DB_PASSWORD'); exit(0); } catch (Throwable \$e) { exit(1); }"; then
        echo "[entrypoint] MySQL готов."
        break
    fi
    sleep 2
done

mkdir -p /app/runtime/logs /app/runtime/cache
chmod -R 777 /app/runtime || true

echo "[entrypoint] Применяю миграции..."
php /app/yii migrate/up --interactive=0 || true

# Seed только если таблица пуста (чтоб перезапуск не затирал ручные правки)
COUNT=$(php -r "
\$pdo = new PDO('mysql:host=$DB_HOST;port=${DB_PORT:-3306};dbname=$DB_NAME', '$DB_USER', '$DB_PASSWORD');
echo (int)\$pdo->query('SELECT COUNT(*) FROM vacancy')->fetchColumn();
" 2>/dev/null || echo 0)

if [ "$COUNT" = "0" ]; then
    echo "[entrypoint] Таблица пуста - запускаю seed..."
    php /app/yii seed/run 50 --interactive=0 || true
else
    echo "[entrypoint] В таблице уже $COUNT записей, seed пропускаю."
fi

echo "[entrypoint] Стартую PHP built-in сервер на 0.0.0.0:8080..."
exec php -S 0.0.0.0:8080 -t /app/web /app/web/router.php
