<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Заполните форму для создания нового аккаунта:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                <?= $form->field($model, 'username')->textInput([
                    'autofocus' => true,
                    'placeholder' => 'Введите имя пользователя'
                ])->hint('Только латинские буквы, цифры, дефис и подчеркивание') ?>

                <?= $form->field($model, 'email')->textInput([
                    'type' => 'email',
                    'placeholder' => 'your@email.com'
                ]) ?>

                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder' => 'Введите пароль'
                ])->hint('Минимум 6 символов') ?>

                <?= $form->field($model, 'password_repeat')->passwordInput([
                    'placeholder' => 'Повторите пароль'
                ]) ?>

                <div class="form-group">
                    <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <p>
        Уже есть аккаунт? <?= Html::a('Войти', ['site/login']) ?>
    </p>
</div>