<?php

use PHPUnit\Framework\TestCase;
use app\models\Vacancy;

/**
 * Юнит-тесты модели Vacancy.
 * Проверяют правила валидации без обращения к БД.
 *
 * Запуск: docker compose exec backend vendor/bin/phpunit
 */
class VacancyTest extends TestCase
{
    public function testRequiredFields(): void
    {
        $v = new Vacancy();
        $this->assertFalse($v->validate(), 'Пустая модель не должна проходить валидацию');
        $errors = $v->errors;
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('description', $errors);
        $this->assertArrayHasKey('salary', $errors);
    }

    public function testValidVacancyPasses(): void
    {
        $v = new Vacancy([
            'title' => 'Middle PHP',
            'description' => 'Yii2 + MySQL',
            'salary' => 150000,
        ]);
        $this->assertTrue($v->validate(), 'Корректная модель должна проходить: ' . print_r($v->errors, true));
    }

    public function testTitleTooLong(): void
    {
        $v = new Vacancy([
            'title' => str_repeat('x', 300),
            'description' => 'ok',
            'salary' => 100000,
        ]);
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('title', $v->errors);
    }

    public function testSalaryMustBeInteger(): void
    {
        $v = new Vacancy([
            'title' => 'ok',
            'description' => 'ok',
            'salary' => 'not-a-number',
        ]);
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('salary', $v->errors);
    }

    public function testNegativeSalaryRejected(): void
    {
        $v = new Vacancy([
            'title' => 'ok',
            'description' => 'ok',
            'salary' => -100,
        ]);
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('salary', $v->errors);
    }

    public function testFieldsMethodReturnsExpectedKeys(): void
    {
        $v = new Vacancy();
        $fields = $v->fields();
        $this->assertContains('id', $fields);
        $this->assertContains('title', $fields);
        $this->assertContains('description', $fields);
        $this->assertContains('salary', $fields);
        $this->assertContains('created_at', $fields);
    }
}
