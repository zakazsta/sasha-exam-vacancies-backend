<?php
/**
 * Bootstrap для юнит-тестов.
 * Грузит autoload + Yii + тестовую конфигурацию (без БД).
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

// Мини-приложение для тестов без БД - достаточно для unit-тестов моделей
new yii\console\Application([
    'id' => 'vacancies-test',
    'basePath' => dirname(__DIR__),
    'components' => [
        'cache' => ['class' => yii\caching\DummyCache::class],
    ],
]);
