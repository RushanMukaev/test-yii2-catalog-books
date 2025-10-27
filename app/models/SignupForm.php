<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Signup form
 *
 * @property-write string $password
 */
class SignupForm extends Model
{
    public ?string $username = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $password_repeat = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'email', 'password', 'password_repeat'], 'required'],

            // username
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match',
                'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                'message' => 'Имя пользователя может содержать только латинские буквы, цифры, дефис и подчеркивание'
            ],
            ['username', 'unique',
                'targetClass' => User::class,
                'message' => 'Это имя пользователя уже занято'
            ],

            // email
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique',
                'targetClass' => User::class,
                'message' => 'Этот email уже зарегистрирован'
            ],

            // password
            ['password', 'string', 'min' => 6],

            // password_repeat
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'username' => 'Имя пользователя',
            'email' => 'Email',
            'password' => 'Пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }

    /**
     * @return User|null
     */
    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->password = $this->password;
        $user->scenario = 'signup';

        if ($user->save()) {
            return $user;
        }

        return null;
    }
}