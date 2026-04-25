#!/bin/sh
# Smoke-тест REST API. Проверяет что все эндпоинты отдают ожидаемые коды ответа.
#
# Запуск: docker compose exec backend sh tests/smoke.sh
# Либо на хосте:  bash tests/smoke.sh http://localhost:18080

set -e
BASE="${1:-http://nginx}"
PASS=0
FAIL=0

check() {
    local name="$1"
    local expected="$2"
    local actual="$3"
    if [ "$actual" = "$expected" ]; then
        echo "  ✓ $name ($actual)"
        PASS=$((PASS+1))
    else
        echo "  ✗ $name - ожидали $expected, получили $actual"
        FAIL=$((FAIL+1))
    fi
}

echo "Smoke-тест API на $BASE"
echo

echo "[1] GET /api/health должен отдать 200"
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/api/health")
check "health" 200 "$code"

echo "[2] GET /api/vacancies должен отдать 200"
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/api/vacancies?per-page=5")
check "list" 200 "$code"

echo "[3] GET /api/vacancies/1 должен отдать 200"
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/api/vacancies/1")
check "view 1" 200 "$code"

echo "[4] GET /api/vacancies/99999 должен отдать 404"
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/api/vacancies/99999")
check "view 404" 404 "$code"

echo "[5] POST /api/vacancies без тела должен отдать 422"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST -H "Content-Type: application/json" -d "{}" "$BASE/api/vacancies")
check "create empty" 422 "$code"

echo "[6] POST /api/vacancies с корректным телом должен отдать 201"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST \
    -H "Content-Type: application/json" \
    -d '{"title":"Smoke test","description":"Автотест","salary":100000}' \
    "$BASE/api/vacancies")
check "create ok" 201 "$code"

echo
echo "Итого: $PASS passed, $FAIL failed"
exit $FAIL
