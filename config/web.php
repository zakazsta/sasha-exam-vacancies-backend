<?php
/**
 * Конфигурация веб-приложения Yii2.
 *
 * Аналоги в Laravel:
 *  - bootstrap → app/Providers + config/app.php
 *  - components → сервис-контейнер (DI)
 *  - urlManager → routes/api.php + RouteServiceProvider
 *  - yii\rest\UrlRule → Route::apiResource
 */
$params = require __DIR__ . '/params.php';
$db     = require __DIR__ . '/db.php';

$config = [
    'id' => 'vacancies-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower'   => '@vendor/bower-asset',
        '@npm'     => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: 'sasha-exam-dev-key-not-for-prod',
            'parsers' => [
                // Чтобы POST-body в JSON парсился автоматически (аналог Laravel middleware ConvertJsonBody)
                'application/json' => \yii\web\JsonParser::class,
            ],
            'enableCsrfValidation' => false, // API без CSRF (чужой SPA-фронт)
        ],
        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'db' => $db,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            ],
        ],
        'errorHandler' => [
            // JSON-ответ для API при ошибках (аналог Laravel handleExceptions)
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                // REST-роуты для Vacancy. Аналог Laravel Route::apiResource('vacancies', VacancyController::class)
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'vacancy',
                    'pluralize' => true,  // /vacancies вместо /vacancy
                    'prefix' => 'api',
                    'only' => ['index', 'view', 'create'],  // GET list, GET one, POST create
                ],
                // Прочие служебные роуты — можно не объявлять
                'GET /api/health' => 'site/health',
                'GET /site/error' => 'site/error',
            ],
        ],
    ],
    'controllerNamespace' => 'app\\controllers',
    'params' => $params,
];

if (YII_ENV_DEV || YII_DEBUG) {
    // Только в dev-режиме: Gii для кодогенерации и Debug для профайлера
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.*.*', '172.*.*.*'],
    ];
}

return $config;
