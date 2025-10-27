<?php

namespace app\models;

use app\enums\UserStatus;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;

/**
 * User model
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $password_reset_token
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read UserStatus $statusEnum
 * @property-write string $password
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @var string|null
     */
    public ?string $password = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user}}';
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
            [['username', 'email'], 'required'],

            // username
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'unique', 'message' => 'Это имя пользователя уже занято'],
            ['username', 'match',
                'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                'message' => 'Имя пользователя может содержать только латинские буквы, цифры, дефис и подчеркивание'
            ],

            // email
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'message' => 'Этот email уже зарегистрирован'],

            // password
            ['password', 'string', 'min' => 6],
            ['password', 'required', 'on' => 'signup'],

            // status
            ['status', 'integer'],
            ['status', 'in', 'range' => array_column(UserStatus::cases(), 'value')],
            ['status', 'default', 'value' => UserStatus::ACTIVE->value],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Имя пользователя',
            'email' => 'Email',
            'password' => 'Пароль',
            'status' => 'Статус',
            'created_at' => 'Дата регистрации',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * @return UserStatus
     */
    public function getStatusEnum(): UserStatus
    {
        return UserStatus::tryFrom($this->status) ?? UserStatus::INACTIVE;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->statusEnum->isActive();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->password) {
            $this->password_hash = \Yii::$app->security->generatePasswordHash($this->password);
            $this->password = null;
        }

        if ($insert && !$this->auth_key) {
            $this->auth_key = \Yii::$app->security->generateRandomString();
        }

        return true;
    }

    /**
     * @param string $password
     * @return bool
     */
    public function validatePassword(string $password): bool
    {
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @return void
     */
    public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = \Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * @return void
     */
    public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
    }

    /**
     * @param string|null $token
     * @return bool
     */
    public static function isPasswordResetTokenValid(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $parts = explode('_', $token);
        $timestamp = (int)end($parts);

        return $timestamp + 3600 >= time();
    }

    /**
     * @param string $token
     * @return static|null
     */
    public static function findByPasswordResetToken(string $token): ?self
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => UserStatus::ACTIVE->value,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id, 'status' => UserStatus::ACTIVE->value]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    /**
     * @param string $username
     * @return static|null
     */
    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => UserStatus::ACTIVE->value]);
    }

    /**
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email): ?self
    {
        return static::findOne(['email' => $email, 'status' => UserStatus::ACTIVE->value]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }
}