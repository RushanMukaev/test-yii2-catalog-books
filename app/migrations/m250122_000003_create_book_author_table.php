<?php

use yii\db\Migration;

/**
 * Creates book_author junction table
 */
class m250122_000003_create_book_author_table extends Migration
{
    private const TABLE_NAME = '{{%book_author}}';
    private const TABLE_BOOKS = '{{%books}}';
    private const TABLE_AUTHORS = '{{%authors}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'book_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex(
            'idx-book_author-unique',
            self::TABLE_NAME,
            ['book_id', 'author_id'],
            true
        );

        $this->createIndex(
            'idx-book_author-author_id',
            self::TABLE_NAME,
            'author_id'
        );

        $this->addForeignKey(
            'fk-book_author-book_id',
            self::TABLE_NAME,
            'book_id',
            self::TABLE_BOOKS,
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-book_author-author_id',
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
        $this->dropForeignKey('fk-book_author-book_id', self::TABLE_NAME);
        $this->dropForeignKey('fk-book_author-author_id', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}