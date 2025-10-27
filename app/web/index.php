<?php
/**
 * Entry point для web приложения
 *
 * Загружает .env, инициализирует Yii2 и запускает приложение
 */

// Загрузка Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Загрузка .env файла через vlucas/phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Валидация обязательных переменных окружения
$dotenv->required([
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
])->notEmpty();

// Установка режима приложения из .env
defined('YII_DEBUG') or define('YII_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
defined('YII_ENV') or define('YII_ENV', $_ENV['APP_ENV'] ?? 'production');

// Установка часового пояса
if (isset($_ENV['TIMEZONE'])) {
    date_default_timezone_set($_ENV['TIMEZONE']);
}

// Загрузка Yii2
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// Загрузка конфигурации и запуск приложения
$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();