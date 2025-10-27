<?php
/**
 * Конфигурация базы данных
 *
 * Использует переменные окружения из .env файла
 * Поддерживает Docker и локальную разработку
 */

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf(
        'mysql:host=%s;dbname=%s;port=%s',
        $_ENV['DB_HOST'] ?? 'db',
        $_ENV['DB_NAME'] ?? 'yii2_books',
        $_ENV['DB_PORT'] ?? '3306'
    ),
    'username' => $_ENV['DB_USER'] ?? 'yii2',
    'password' => $_ENV['DB_PASSWORD'] ?? 'yii2',
    'charset' => 'utf8mb4',

    // Schema cache для production (улучшает производительность)
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',

    // Настройки для MySQL 8.0+
    'attributes' => [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
];