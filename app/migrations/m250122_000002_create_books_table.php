<?php

use yii\db\Migration;

/**
 * Creates books table
 */
class m250122_000002_create_books_table extends Migration
{
    private const TABLE_NAME = '{{%books}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'year' => $this->integer()->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(17)->notNull(),
            'image_path' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex(
            'idx-books-isbn',
            self::TABLE_NAME,
            'isbn',
            true
        );

        $this->execute(
            'CREATE INDEX idx_books_year_desc ON ' . self::TABLE_NAME . ' (year DESC)'
        );

        $this->createIndex(
            'idx-books-title-year',
            self::TABLE_NAME,
            ['title', 'year']
        );

        $this->execute(
            'CREATE FULLTEXT INDEX idx_books_fulltext
            ON ' . self::TABLE_NAME . ' (title, description)'
        );

        $this->createIndex(
            'idx-books-created_at',
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