# Инструкция по установке

## Установка через Docker (рекомендуется)

### 1. Запустить контейнеры

```bash
make up
```

Или без Makefile:

```bash
docker-compose up -d
```

### 2. Установить зависимости

```bash
make install
```

Или:

```bash
docker-compose exec php composer install
```

### 3. Применить миграции

```bash
make migrate
```

Или:

```bash
docker-compose exec php php yii migrate --interactive=0
```

### 4. Готово!

Откройте в браузере: http://localhost:8081

**Учетные данные по умолчанию:**
- Username: `admin`
- Password: `admin123`

## Установка без Docker

### 1. Установить зависимости

```bash
cd app
composer install
```

### 2. Настроить базу данных

Создайте базу данных:

```sql
CREATE DATABASE yii2_books CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Настройте подключение в `app/.env`:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=yii2_books
DB_USER=root
DB_PASSWORD=your_password
```

### 3. Применить миграции

```bash
cd app
php yii migrate
```

### 4. Настроить веб-сервер

Укажите DocumentRoot на `app/web/`

Или используйте встроенный сервер:

```bash
cd app
php yii serve --port=8080
```

Откройте: http://localhost:8080

## Полезные команды

```bash
make help      # Показать все команды
make up        # Запустить контейнеры
make down      # Остановить контейнеры
make restart   # Перезапустить
make shell     # Войти в PHP контейнер
make db        # Войти в MySQL консоль
make logs      # Показать логи
```

## Возможные проблемы

### Порты заняты

Если порт 8081 занят, измените его в `.env`:

```env
NGINX_PORT=8082
```

Перезапустите:

```bash
make down
make up
```

### Ошибка подключения к БД

Проверьте настройки в `app/.env` и перезапустите контейнеры:

```bash
make restart
```

### Права доступа

Если возникают ошибки с правами:

```bash
docker-compose exec php chmod -R 777 runtime web/assets web/uploads
```