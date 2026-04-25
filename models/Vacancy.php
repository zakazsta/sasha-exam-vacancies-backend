<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель вакансии.
 *
 * ==================================================================
 *  ВАЖНО ДЛЯ ЗАЩИТЫ: ЗАЧЕМ ACTIVE RECORD И КОГДА ОН УСТАРЕЛ
 * ==================================================================
 *
 * Active Record — паттерн, где одна PHP-модель одновременно:
 *   1) представляет строку таблицы (атрибуты = поля),
 *   2) умеет себя читать/писать в БД (save, delete, find).
 *
 * В Laravel это Eloquent, в Symfony — Doctrine (Data Mapper), в Django — тоже AR.
 *
 * Спор «AR устарел vs нет»:
 *   - Противники: AR смешивает доменную логику с инфраструктурной (SQL),
 *     трудно тестировать без БД, сложнее строить сложные домены (DDD).
 *   - Сторонники: на 80% CRUD-задач AR компактен и быстр, писать меньше кода,
 *     mockа подменять на SQLite или фикстуры.
 *
 * КОМПРОМИСС (мы делаем так): AR как тонкая обёртка над таблицей,
 * а всю нетривиальную логику выносим в Service-классы (@see services/VacancyService.php).
 * Получаем: читабельность AR + тестируемость и разделение ответственности.
 *
 * Если на защите спросят «почему не Repository+PDO?» — ответ:
 * «для CRUD с 3 полями это overengineering. Repository даёт выигрыш когда:
 *  (а) логика поверх нескольких моделей, (б) нужны строгие тесты домена,
 *  (в) источников данных больше одного (SQL + API + очередь).»
 *
 * ==================================================================
 *
 * @property int    $id
 * @property string $title
 * @property string $description
 * @property int    $salary
 * @property string $created_at
 * @property string $updated_at
 */
class Vacancy extends ActiveRecord
{
    /**
     * Имя таблицы. По умолчанию Yii делает snake_case от имени класса,
     * но явно указать — хорошая привычка (облегчает рефакторинг).
     */
    public static function tableName(): string
    {
        return 'vacancy';
    }

    /**
     * Автоматические поля created_at / updated_at.
     * Аналог Laravel — $timestamps = true в Eloquent.
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * Правила валидации.
     *
     * В Laravel эти правила обычно живут в FormRequest (StoreVacancyRequest).
     * В Yii2 есть два подхода:
     *   1) Хранить тут (компактно, но модель «знает слишком много»).
     *   2) Отдельный класс-форма `VacancyForm extends yii\base\Model`
     *      (чище, но больше кода).
     *
     * Для учебного примера держим в модели — быстро и понятно.
     */
    public function rules(): array
    {
        return [
            [['title', 'description', 'salary'], 'required',
                'message' => 'Поле «{attribute}» обязательно'],
            ['title', 'string', 'max' => 255,
                'tooLong' => 'Название слишком длинное (максимум 255 символов)'],
            ['description', 'string', 'min' => 1, 'max' => 10000],
            ['salary', 'integer', 'min' => 0, 'max' => 10_000_000,
                'message' => 'Зарплата должна быть целым числом',
                'tooSmall' => 'Зарплата не может быть отрицательной'],
        ];
    }

    /**
     * Подписи полей в ответах валидации (human-readable для фронта).
     */
    public function attributeLabels(): array
    {
        return [
            'id'          => 'ID',
            'title'       => 'Название',
            'description' => 'Описание',
            'salary'      => 'Зарплата',
            'created_at'  => 'Дата создания',
            'updated_at'  => 'Дата обновления',
        ];
    }

    /**
     * Явный список полей, которые отдаём в REST-ответе.
     * Без этого Yii вернёт всё, включая служебные. Аналог Laravel — Resource.
     */
    public function fields(): array
    {
        return [
            'id',
            'title',
            'description',
            'salary',
            'created_at',
            'updated_at',
        ];
    }
}
