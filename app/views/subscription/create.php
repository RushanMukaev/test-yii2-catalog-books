<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\models\Subscription $model */
/** @var app\models\Author $author */

$this->title = 'Подписка на автора';
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['/author/index']];
if (isset($author)) {
    $this->params['breadcrumbs'][] = ['label' => $author->fullName, 'url' => ['/author/view', 'id' => $author->id]];
}
$this->params['breadcrumbs'][] = $this->title;

// Подключаем jQuery Inputmask для маски телефона
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

// Применяем маску к полю телефона
$this->registerJs(new JsExpression("
    $('#subscription-phone').inputmask('+7 (999) 999-99-99');
"));
?>

<div class="subscription-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (isset($author)): ?>
        <div class="alert alert-info">
            <strong>Автор:</strong> <?= Html::encode($author->fullName) ?>
            <br>
            <small>Вы будете получать SMS-уведомления о новых книгах этого автора</small>
        </div>
    <?php endif; ?>

    <div class="subscription-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'author_id')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'phone')->textInput([
            'maxlength' => true,
            'placeholder' => '+7 (999) 123-45-67'
        ])->hint('Введите номер телефона в формате +7 (XXX) XXX-XX-XX') ?>

        <div class="form-group">
            <?= Html::submitButton('Подписаться', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Отмена', isset($author) ? ['/author/view', 'id' => $author->id] : ['/author/index'], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

    <div class="alert alert-warning" style="margin-top: 20px;">
        <strong>Обратите внимание:</strong>
        <ul>
            <li>SMS-уведомления отправляются только при добавлении новых книг автора</li>
            <li>Один номер телефона может подписаться на одного автора только один раз</li>
            <li>Ваш номер телефона не будет использоваться в других целях</li>
        </ul>
    </div>

</div>