<?php

use yii\db\Migration;

/**
 * Creates authors table
 */
class m250122_000001_create_authors_table extends Migration
{
    private const TABLE_NAME = '{{%authors}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'first_name' => $this->string(100)->notNull(),
            'last_name' => $this->string(100)->notNull(),
            'middle_name' => $this->string(100),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex(
            'idx-authors-last_name-first_name',
            self::TABLE_NAME,
            ['last_name', 'first_name']
        );

        $this->execute(
            'CREATE FULLTEXT INDEX idx_authors_fulltext
            ON ' . self::TABLE_NAME . ' (first_name, last_name, middle_name)'
        );

        $this->createIndex(
            'idx-authors-created_at',
            self::TABLE_NAME,
            'created_at'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
