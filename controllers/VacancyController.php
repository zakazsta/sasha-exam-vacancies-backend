<?php
namespace app\controllers;

use app\models\Vacancy;
use app\services\VacancyService;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\ContentNegotiator;
use yii\web\Response;

/**
 * REST-контроллер для вакансий.
 *
 * ==================================================================
 *  ПАРАЛЛЕЛИ С LARAVEL
 * ==================================================================
 *  - yii\rest\ActiveController  ↔  Laravel API resource controller
 *    (index, view, create, update, delete из коробки — как Route::apiResource)
 *
 *  - ActiveDataProvider         ↔  $model->paginate(10)->sortable()
 *    (сортировка + пагинация автоматом из query-параметров)
 *
 *  - actions()/$modelClass      ↔  определяет что именно контроллер умеет
 *
 *  - verbs()                    ↔  middleware method-check в роутах
 *
 * ==================================================================
 *
 *  ENDPOINTS
 *  --------
 *  GET  /api/vacancies?sort=-created_at&page=2&per-page=10 — список
 *  GET  /api/vacancies/{id}                               — одна вакансия
 *  POST /api/vacancies   body: {title, description, salary} — создание
 *
 * ==================================================================
 */
class VacancyController extends ActiveController
{
    public $modelClass = Vacancy::class;

    /**
     * Переопределяем набор действий — выключаем update/delete
     * (в ТЗ экзамена они не требуются).
     * В Laravel аналогичный эффект: Route::apiResource(...)->only(['index','show','store']).
     */
    public function actions(): array
    {
        $actions = parent::actions();

        // Убираем PUT/DELETE
        unset($actions['update'], $actions['delete'], $actions['options']);

        // Кастомная data-provider для index — чтобы задать правила сортировки и пагинации.
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * Разрешённые глаголы на каждое действие.
     * Если придёт PUT на /api/vacancies/5 — Yii вернёт 405 Method Not Allowed.
     */
    protected function verbs(): array
    {
        return [
            'index'  => ['GET', 'HEAD'],
            'view'   => ['GET', 'HEAD'],
            'create' => ['POST'],
        ];
    }

    /**
     * Настройка ActiveDataProvider — фабрика списка с сортировкой и пагинацией.
     *
     * По ТЗ на фронте: сортировка через dropdown + переключатель направления.
     * Yii2 автоматически понимает параметр ?sort=field или ?sort=-field (минус = DESC).
     * Пагинация: ?page=N&per-page=M.
     */
    public function prepareDataProvider(): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => Vacancy::find(),
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'title',
                    'salary',
                    'created_at',
                ],
            ],
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1, \Yii::$app->params['maxPageSize']],
            ],
        ]);
    }

    /**
     * ContentNegotiator по умолчанию в ActiveController выбирает формат по заголовку.
     * Явно фиксируем JSON, чтоб не было сюрпризов в браузере (text/html отдаст XML).
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        // У нас нет авторизации и rate-limit (учебный стенд) — отключаем встроенные
        // фильтры yii\rest\Controller, иначе они требуют настроенный `user`-компонент.
        unset($behaviors['authenticator'], $behaviors['rateLimiter']);
        return $behaviors;
    }
}
