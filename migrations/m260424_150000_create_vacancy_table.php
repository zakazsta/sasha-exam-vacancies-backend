<?php

use yii\db\Migration;

/**
 * Миграция: создание таблицы vacancy.
 *
 * Запуск: ./yii migrate
 *
 * Аналог Laravel - database/migrations/2026_04_24_150000_create_vacancies_table.php
 * с методами up()/down().
 */
class m260424_150000_create_vacancy_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('vacancy', [
            'id'          => $this->primaryKey(),
            'title'       => $this->string(255)->notNull()->comment('Название вакансии'),
            'description' => $this->text()->notNull()->comment('Описание'),
            'salary'      => $this->integer()->notNull()->comment('Зарплата, руб. целое'),
            'created_at'  => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'  => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        // Индекс по дате создания - сортировка списка будет быстрой
        $this->createIndex('idx-vacancy-created_at', 'vacancy', 'created_at');
        // Индекс по salary - если будет сортировка по зарплате
        $this->createIndex('idx-vacancy-salary', 'vacancy', 'salary');
    }

    public function safeDown(): bool
    {
        $this->dropTable('vacancy');
        return true;
    }
}
