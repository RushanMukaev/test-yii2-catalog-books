<?php
/**
 * Bootstrap файл для загрузки environment variables
 *
 * Загружает .env файл через vlucas/phpdotenv
 * Выполняется перед инициализацией приложения
 */

use Dotenv\Dotenv;

// Загрузка autoloader Composer
require __DIR__ . '/../vendor/autoload.php';

// Загрузка .env файла
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Валидация обязательных переменных окружения
$dotenv->required([
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
    'SMSPILOT_API_KEY',
])->notEmpty();

// Валидация булевых значений
$dotenv->required('APP_DEBUG')->isBoolean();
$dotenv->required('CSRF_ENABLED')->isBoolean();

// Установка PHP настроек из .env
if (isset($_ENV['TIMEZONE'])) {
    date_default_timezone_set($_ENV['TIMEZONE']);
}

// Определение YII_DEBUG и YII_ENV из .env
defined('YII_DEBUG') or define('YII_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
defined('YII_ENV') or define('YII_ENV', $_ENV['APP_ENV'] ?? 'production');