<?php
/**
 * Router-скрипт для встроенного PHP-сервера.
 *
 * Вызывается при `php -S ... web/router.php` и решает:
 *   - если запрошен существующий файл (например /favicon.ico) → отдать его,
 *   - иначе → направить запрос в web/index.php (Yii2 обрабатывает URL).
 *
 * Nginx этого не требует, там `try_files` делает то же самое.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = __DIR__ . $uri;

if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
    return false;  // отдать статический файл как есть
}

require __DIR__ . '/index.php';
