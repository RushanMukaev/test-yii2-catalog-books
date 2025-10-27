<?php

use yii\db\Migration;

/**
 * Creates user table
 */
class m250122_000004_create_user_table extends Migration
{
    private const TABLE_NAME = '{{%user}}';

    private const STATUS_DELETED = 0;
    private const STATUS_INACTIVE = 9;
    private const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull()->unique(),
            'email' => $this->string(255)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'password_reset_token' => $this->string(255)->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(self::STATUS_ACTIVE),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx-user-username', self::TABLE_NAME, 'username');
        $this->createIndex('idx-user-email', self::TABLE_NAME, 'email');
        $this->createIndex('idx-user-status', self::TABLE_NAME, 'status');
        $this->createIndex('idx-user-password_reset_token', self::TABLE_NAME, 'password_reset_token');

        $this->insert(self::TABLE_NAME, [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'status' => self::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable(self::TABLE_NAME);
    }
}