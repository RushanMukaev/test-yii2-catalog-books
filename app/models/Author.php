<?php

namespace app\models;

use app\models\query\AuthorQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Author model
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $middle_name
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read string $fullName
 * @property-read string $shortName
 * @property-read Book[] $books
 * @property-read Subscription[] $subscriptions
 * @property-read int $booksCount
 *
 */
class Author extends ActiveRecord
{
    /**
     * @var int|null
     */
    public $books_count;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%authors}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): AuthorQuery
    {
        return new AuthorQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['first_name', 'last_name'], 'required'],
            [['first_name', 'last_name', 'middle_name'], 'string', 'max' => 100],
            [['first_name', 'last_name', 'middle_name'], 'trim'],
            [['first_name', 'last_name', 'middle_name'], 'filter', 'filter' => function ($value) {
                return $value ? preg_replace('/\s+/', ' ', $value) : null;
            }],
            [['first_name', 'last_name', 'middle_name'], 'match',
                'pattern' => '/^[а-яёА-ЯЁa-zA-Z\s\-]+$/u',
                'message' => 'Можно использовать только буквы, пробелы и дефисы'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'middle_name' => 'Отчество',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'fullName' => 'ФИО',
            'shortName' => 'Фамилия И.О.',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBooks(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_author}}', ['author_id' => 'id'])
            ->orderBy(['year' => SORT_DESC, 'title' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Subscription::class, ['author_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        $parts = array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ]);

        return implode(' ', $parts);
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        $initials = [];

        if ($this->first_name) {
            $initials[] = mb_substr($this->first_name, 0, 1) . '.';
        }

        if ($this->middle_name) {
            $initials[] = mb_substr($this->middle_name, 0, 1) . '.';
        }

        return trim($this->last_name . ' ' . implode('', $initials));
    }

    /**
     * @return int
     */
    public function getBooksCount(): int
    {
        if (isset($this->books_count)) {
            return (int)$this->books_count;
        }

        return (int)$this->getBooks()->count();
    }

    /**
     * @return bool
     */
    public function hasSubscribers(): bool
    {
        return $this->getSubscriptions()->exists();
    }

    /**
     * @return array<string>
     */
    public function getSubscribersPhones(): array
    {
        return $this->getSubscriptions()
            ->select('phone')
            ->column();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->fullName;
    }
}