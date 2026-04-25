<?php
/**
 * Подключение к БД. Параметры берём из переменных окружения (DOCKER-контейнер).
 * В Laravel аналог - config/database.php + .env.
 */
return [
    'class' => \yii\db\Connection::class,
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: 'mysql',
        getenv('DB_PORT') ?: '3306',
        getenv('DB_NAME') ?: 'vacancies'
    ),
    'username' => getenv('DB_USER') ?: 'vacancies',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
