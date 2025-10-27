<?php

namespace app\models\query;

use yii\db\ActiveQuery;

/**
 * Author query class
 */
class AuthorQuery extends ActiveQuery
{
    /**
     * @param int $year
     * @param int $limit
     * @return self
     */
    public function topByBooksInYear(int $year, int $limit = 10): self
    {
        return $this
            ->select([
                '{{%authors}}.*',
                'books_count' => 'COUNT(DISTINCT {{%books}}.id)',
            ])
            ->innerJoinWith('books', false)
            ->where(['{{%books}}.year' => $year])
            ->groupBy('{{%authors}}.id')
            ->having(['>', 'books_count', 0])
            ->orderBy(['books_count' => SORT_DESC, '{{%authors}}.last_name' => SORT_ASC])
            ->limit($limit);
    }

    /**
     * @return self
     */
    public function withBooksCount(): self
    {
        return $this->addSelect([
            'books_count' => (new \yii\db\Query())
                ->select('COUNT(*)')
                ->from('{{%book_author}}')
                ->where('{{%book_author}}.author_id = {{%authors}}.id')
        ]);
    }

    /**
     * @return self
     */
    public function withBooks(): self
    {
        return $this->with('books');
    }

    /**
     * @param string $name
     * @return self
     */
    public function byName(string $name): self
    {
        return $this->andWhere([
            'or',
            ['like', 'first_name', $name],
            ['like', 'last_name', $name],
            ['like', 'middle_name', $name],
        ]);
    }

    /**
     * @return self
     */
    public function orderByFullName(): self
    {
        return $this->orderBy([
            'last_name' => SORT_ASC,
            'first_name' => SORT_ASC,
        ]);
    }
}