# Backend сервиса вакансий - Yii2 + MySQL

REST API на Yii2 для сервиса управления вакансиями. Разворачивается одной командой в Docker.

## Что внутри

- **Yii2** (PHP 8.1+) - REST API
- **MySQL 8** - база данных
- **Docker Compose** - оркестрация контейнеров
- **Faker (русская локаль)** - генерация тестовых вакансий
- **OpenAPI / Swagger UI** - интерактивная документация API (`/swagger/`)
- **PHPUnit** - юнит-тесты + smoke-скрипт для API

## Endpoints

| Method | Path | Что делает |
|--------|------|------------|
| GET    | `/api/health`            | Проверка живости сервиса |
| GET    | `/api/vacancies`         | Список вакансий с сортировкой и пагинацией |
| GET    | `/api/vacancies/{id}`    | Одна вакансия по id |
| POST   | `/api/vacancies`         | Создание новой вакансии |

Параметры списка:
- `?sort=field` или `?sort=-field` (минус = по убыванию). Допустимо: `title`, `salary`, `created_at`
- `?page=N&per-page=M`
- В заголовках ответа: `X-Pagination-Total-Count`, `-Page-Count`, `-Current-Page`, `-Per-Page`

## Где разворачивать

**Локально - на своей машине.** Все команды ниже работают на вашем компьютере
через Docker. На выходе сервис будет доступен по `http://localhost:8080`.

Если нужно выложить в интернет (production) - поднимаете любой VPS с Docker
(Selectel, Timeweb, Reg.ru, Yandex Cloud и т.д.) и накатываете тот же
`docker compose up -d` поверх него. Никакой специфической нашей инфраструктуры
проект не использует - только Docker и публичный порт.

## Запуск с нуля

```bash
# 1. Клонируем и переходим в папку
git clone https://github.com/zakazsta/sasha-exam-vacancies-backend.git
cd sasha-exam-vacancies-backend

# 2. Готовим .env (поменяйте пароли на свои)
cp .env.example .env

# 3. Поднимаем стек
docker compose up -d --build

# 4. Смотрим логи (entrypoint ждёт MySQL, гонит миграции, делает seed)
docker compose logs -f backend

# 5. Проверяем что API живой
curl http://localhost:8080/api/health
curl 'http://localhost:8080/api/vacancies?per-page=3'

# 6. Открываем Swagger UI в браузере
# http://localhost:8080/swagger/
```

### Если порт 3306 уже занят

Ошибка `Bind for 0.0.0.0:3306 failed: port is already allocated`
означает, что у вас уже работает локальный MySQL на 3306. Решение:
поменяйте порт хоста в `.env`:

```
MYSQL_HOST_PORT=3307
```

И перезапустите: `docker compose down && docker compose up -d`. Бэк
внутри контейнера всё равно будет ходить к MySQL на 3306, наружу
порт пробрасывается на 3307. ТЗ при этом не нарушается, в задании
сказано что БД на порту 3306, и она там и есть, просто внутри
docker-сети.

## Структура проекта

```
backend/
├── commands/SeedController.php     CLI-команда seed/run для заполнения БД
├── config/
│   ├── web.php                     конфиг веб-приложения (компоненты, роуты)
│   ├── db.php                      подключение к БД через env
│   ├── console.php                 конфиг для CLI (миграции, seed)
│   └── params.php                  пользовательские параметры (defaultPageSize и т.п.)
├── controllers/
│   ├── VacancyController.php       REST-контроллер extends ActiveController
│   └── SiteController.php          служебный (/api/health, /site/error)
├── models/Vacancy.php              Active Record модель + rules() + fields()
├── migrations/m260424_*.php        схема таблицы vacancy
├── services/VacancyService.php     слой бизнес-логики (тонкий)
├── web/
│   ├── index.php                   точка входа HTTP-приложения
│   └── router.php                  роутер для встроенного php -S сервера
├── runtime/                        логи, кеш (создаётся автоматически)
├── composer.json                   PHP-зависимости (аналог package.json)
├── Dockerfile                      сборка образа PHP 8.1-cli + extensions
├── entrypoint.sh                   стартовый скрипт: ждёт MySQL → migrate → seed → run
├── yii                             CLI-точка для миграций и команд
└── docker-compose.yml              оркестрация: mysql + backend
```

## Как поднимали с нуля (для повтора на другом компьютере)

Если хотите воспроизвести проект руками без git clone, по шагам:

```bash
# 1. Создаём папку проекта
mkdir backend && cd backend

# 2. Инициализируем composer (аналог npm init -y)
composer init -n --name="vacancies/backend" --type=project
composer config minimum-stability dev
composer config prefer-stable true

# 3. Устанавливаем Yii2 и Faker
composer require yiisoft/yii2:~2.0.48 fakerphp/faker

# 4. Создаём базовую структуру папок
mkdir -p commands config controllers migrations models services web

# 5. Создаём web/index.php (точка входа), config/web.php (конфиг),
#    config/db.php (БД), yii (CLI), Dockerfile, entrypoint.sh,
#    docker-compose.yml - содержимое можно скопировать из этого репо.

# 6. Пишем модель Vacancy с rules() - backend/models/Vacancy.php
# 7. Создаём миграцию командой: ./yii migrate/create create_vacancy_table
# 8. Описываем VacancyController extends yii\rest\ActiveController
# 9. Прописываем роут в config/web.php → urlManager.rules
# 10. Создаём SeedController с генерацией тестовых данных через Faker
# 11. cp .env.example .env, docker compose up -d --build
```

## Полезные команды

```bash
# Войти в контейнер бэкенда
docker compose exec backend sh

# Внутри контейнера - миграции
docker compose exec backend ./yii migrate

# Перегенерация тестовых данных (50 вакансий)
docker compose exec backend ./yii seed/run 50

# Смотрим логи приложения
docker compose exec backend tail -f runtime/logs/app.log

# Подключаемся к БД из командной строки
docker compose exec mysql mysql -uvacancies -p vacancies

# Запуск тестов (PHPUnit) внутри контейнера
docker compose exec backend ./vendor/bin/phpunit

# Smoke-проверка API (bash + curl)
bash tests/smoke.sh
```

## Документация API

Swagger UI доступен сразу после запуска: http://localhost:8080/swagger/
Спецификация в `web/swagger.yaml` (формат OpenAPI 3.0). Try it out
работает прямо в браузере, можно тестировать эндпоинты без curl и Postman.

## Тесты

- **Юнит-тесты** в `tests/unit/VacancyTest.php` (PHPUnit).
  Проверяют валидацию модели Vacancy без обращения к БД.
- **Smoke-тесты API** в `tests/smoke.sh` (bash + curl).
  Прогоняют все эндпоинты и сравнивают коды ответа.

Запуск:
```bash
docker compose exec backend ./vendor/bin/phpunit
bash tests/smoke.sh
```

## Стек на стороне фронта

Фронт лежит в отдельном репозитории: https://github.com/zakazsta/sasha-exam-vacancies-frontend
Он ходит в этот бэк по `http://localhost:8080/api`.
