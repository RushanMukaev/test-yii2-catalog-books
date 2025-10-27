<?php

use yii\db\Migration;

/**
 * Creates subscription table
 */
class m250122_000005_create_subscription_table extends Migration
{
    private const TABLE_NAME = '{{%subscription}}';
    private const TABLE_AUTHORS = '{{%authors}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'phone' => $this->string(15)->notNull(),
            'author_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex(
            'idx-subscription-unique',
            self::TABLE_NAME,
            ['phone', 'author_id'],
            true
        );

        $this->createIndex(
            'idx-subscription-author_id',
            self::TABLE_NAME,
            'author_id'
        );

        $this->createIndex(
            'idx-subscription-phone',
            self::TABLE_NAME,
            'phone'
        );

        $this->addForeignKey(
            'fk-subscription-author_id',
            self::TABLE_NAME,
            'author_id',
            self::TABLE_AUTHORS,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk-subscription-author_id', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}