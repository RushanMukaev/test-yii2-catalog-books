<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * Book model
 *
 * @property int $id
 * @property string $title
 * @property int $year
 * @property string|null $description
 * @property string $isbn
 * @property string|null $image_path
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Author[] $authors
 * @property-read string $authorsNames
 * @property-read string $imageUrl
 */
class Book extends ActiveRecord
{
    /**
     * @var UploadedFile|null
     */
    public $imageFile;

    /**
     * @var array<int>
     */
    public array $author_ids = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%books}}';
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
        $currentYear = (int)date('Y');

        return [
            [['title', 'year', 'isbn'], 'required'],
            ['year', 'integer', 'min' => 1000, 'max' => $currentYear + 1],
            ['title', 'string', 'max' => 255],
            ['description', 'string'],
            ['isbn', 'string', 'min' => 10, 'max' => 17],
            ['isbn', 'match',
                'pattern' => '/^(?:\d{10}|\d{13}|[\d\-]{13,17})$/',
                'message' => 'ISBN должен содержать 10 или 13 цифр (можно с дефисами)'
            ],
            ['isbn', 'unique', 'message' => 'Книга с таким ISBN уже существует'],
            ['image_path', 'string', 'max' => 255],
            ['imageFile', 'image',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'maxSize' => 5 * 1024 * 1024,
                'tooBig' => 'Файл не должен превышать 5MB',
                'skipOnEmpty' => true,
            ],
            ['author_ids', 'each', 'rule' => ['integer']],
            ['author_ids', 'required', 'message' => 'Выберите хотя бы одного автора'],
            [['title', 'description'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Название книги',
            'year' => 'Год выпуска',
            'description' => 'Описание',
            'isbn' => 'ISBN',
            'image_path' => 'Обложка',
            'imageFile' => 'Файл обложки',
            'author_ids' => 'Авторы',
            'created_at' => 'Дата добавления',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthors(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('{{%book_author}}', ['book_id' => 'id'])
            ->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC]);
    }

    /**
     * @return string
     */
    public function getAuthorsNames(): string
    {
        $names = ArrayHelper::getColumn($this->authors, 'fullName');
        return implode(', ', $names);
    }

    /**
     * @return string
     */
    public function getAuthorsShortNames(): string
    {
        $names = ArrayHelper::getColumn($this->authors, 'shortName');
        return implode(', ', $names);
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        if ($this->image_path && file_exists(\Yii::getAlias('@webroot/uploads/books/' . $this->image_path))) {
            return \Yii::getAlias('@web/uploads/books/' . $this->image_path);
        }

        return 'https://placehold.co/300x400/e9ecef/6c757d?text=No+Cover';
    }

    /**
     * @param array $options
     * @return string
     */
    public function getImageTag(array $options = []): string
    {
        $defaultOptions = [
            'alt' => Html::encode($this->title),
            'class' => 'img-fluid',
        ];

        return Html::img($this->imageUrl, array_merge($defaultOptions, $options));
    }

    /**
     * @return string
     */
    public function getDisplayYear(): string
    {
        return $this->year . ' г.';
    }

    /**
     * @return string
     */
    public function getFormattedIsbn(): string
    {
        $isbn = preg_replace('/[^\d]/', '', $this->isbn);

        if (strlen($isbn) === 13) {
            return sprintf(
                '%s-%s-%s-%s-%s',
                substr($isbn, 0, 3),
                substr($isbn, 3, 1),
                substr($isbn, 4, 4),
                substr($isbn, 8, 4),
                substr($isbn, 12, 1)
            );
        }

        if (strlen($isbn) === 10) {
            return sprintf(
                '%s-%s-%s-%s',
                substr($isbn, 0, 1),
                substr($isbn, 1, 4),
                substr($isbn, 5, 4),
                substr($isbn, 9, 1)
            );
        }

        return $this->isbn;
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind(): void
    {
        parent::afterFind();
        $this->author_ids = ArrayHelper::getColumn($this->authors, 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->image_path) {
            $filePath = \Yii::getAlias('@webroot/uploads/books/' . $this->image_path);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return sprintf('%s (%d)', $this->title, $this->year);
    }
}