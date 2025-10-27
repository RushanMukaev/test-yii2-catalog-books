<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Subscription model
 *
 * @property int $id
 * @property string $phone
 * @property int $author_id
 * @property int $created_at
 *
 * @property-read Author $author
 * @property-read string $formattedPhone
 */
class Subscription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%subscription}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['phone', 'author_id'], 'required'],

            ['phone', 'string', 'length' => 12],
            ['phone', 'match',
                'pattern' => '/^\+7\d{10}$/',
                'message' => 'Телефон должен быть в формате +7XXXXXXXXXX (например: +79991234567)'
            ],

            ['author_id', 'integer'],
            ['author_id', 'exist',
                'targetClass' => Author::class,
                'targetAttribute' => 'id',
                'message' => 'Автор не найден'
            ],

            ['phone', 'unique',
                'targetAttribute' => ['phone', 'author_id'],
                'message' => 'Вы уже подписаны на этого автора'
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
            'phone' => 'Номер телефона',
            'author_id' => 'Автор',
            'created_at' => 'Дата подписки',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /**
     * @return string
     */
    public function getFormattedPhone(): string
    {
        if (!$this->phone) {
            return '';
        }

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($this->phone, 2, 3),
            substr($this->phone, 5, 3),
            substr($this->phone, 8, 2),
            substr($this->phone, 10, 2)
        );
    }

    /**
     * @param string $phone
     * @return string
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '8') && strlen($digits) === 11) {
            $digits = '7' . substr($digits, 1);
        }

        if (str_starts_with($digits, '7') && strlen($digits) === 11) {
            return '+' . $digits;
        }

        if (strlen($digits) === 10) {
            return '+7' . $digits;
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->phone) {
            $this->phone = self::normalizePhone($this->phone);
        }

        return true;
    }

    /**
     * @param array<int> $authorIds
     * @return self[]
     */
    public static function findByAuthorIds(array $authorIds): array
    {
        return self::find()
            ->where(['author_id' => $authorIds])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->formattedPhone . ' → ' . ($this->author->fullName ?? 'Автор не найден');
    }
}