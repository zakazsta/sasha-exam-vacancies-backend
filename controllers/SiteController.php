<?php
namespace app\controllers;

use yii\rest\Controller;
use yii\web\Response;

/**
 * Служебный контроллер — health-check и обработчик ошибок.
 * /api/health — для проверки живости сервиса
 * /site/error — куда errorHandler отправляет неперехваченные ошибки
 */
class SiteController extends Controller
{
    /**
     * Отключаем встроенные authenticator / rateLimiter из yii\rest\Controller —
     * в учебном стенде нет авторизации, а RateLimiter требует настроенный user-компонент.
     */
    public function behaviors(): array
    {
        $b = parent::behaviors();
        unset($b['authenticator'], $b['rateLimiter']);
        return $b;
    }

    public function actionHealth(): array
    {
        return [
            'status' => 'ok',
            'service' => 'vacancies-api',
            'time' => date('c'),
            'php' => PHP_VERSION,
        ];
    }

    public function actionError(): array
    {
        $exception = \Yii::$app->errorHandler->exception;
        \Yii::$app->response->statusCode = $exception ? ($exception->statusCode ?? 500) : 500;

        return [
            'error' => true,
            'code'  => \Yii::$app->response->statusCode,
            'message' => $exception ? $exception->getMessage() : 'Unknown error',
        ];
    }
}
