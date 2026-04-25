<?php
namespace app\services;

use app\models\Vacancy;
use yii\base\BaseObject;

/**
 * Сервис вакансий - слой бизнес-логики между контроллером и моделью.
 *
 * ==================================================================
 *  ЗАЧЕМ ЭТОТ СЛОЙ (НА ЗАЩИТУ)
 * ==================================================================
 * На CRUD из 3 полей слой выглядит избыточным - но именно это
 * правильное место куда вы добавите: транзакции, рассылку уведомлений
 * после создания, логирование, инвалидацию кэша, интеграцию с поиском.
 *
 * В Laravel это был бы `App\Services\VacancyService`.
 * Контроллер его получает через DI (конструктор).
 *
 * Yii2 сервис-контейнер - `Yii::$container` (аналог Laravel service-container).
 * В простых контроллерах достаточно `new VacancyService()`.
 *
 * ==================================================================
 */
class VacancyService extends BaseObject
{
    /**
     * Создание вакансии с валидацией.
     *
     * @param array $data Пришедшие поля из body
     * @return array{model: Vacancy|null, errors: array|null}
     */
    public function create(array $data): array
    {
        $model = new Vacancy();
        $model->setAttributes($data, /*safeOnly*/ true);

        if (!$model->save()) {
            // save() сам запускает validate() - если ошибки, они в $model->errors.
            return ['model' => null, 'errors' => $model->errors];
        }

        // Сюда в будущем: отправка в очередь, событие «vacancy.created», лог аудита.

        return ['model' => $model, 'errors' => null];
    }

    /**
     * Пример метода для будущего расширения: получение вакансий с
     * дополнительной логикой (например, скрыть устаревшие, применить права).
     * Сейчас контроллер читает напрямую через ActiveDataProvider - когда появятся
     * бизнес-правила, перенесём чтение сюда.
     */
    public function findOneOrFail(int $id): Vacancy
    {
        $model = Vacancy::findOne($id);
        if ($model === null) {
            throw new \yii\web\NotFoundHttpException("Вакансия #{$id} не найдена");
        }
        return $model;
    }
}
