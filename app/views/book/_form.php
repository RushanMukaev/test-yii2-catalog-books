<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Author;

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var yii\widgets\ActiveForm $form */

$authors = ArrayHelper::map(Author::find()->orderBy(['last_name' => SORT_ASC])->all(), 'id', 'fullName');
?>

<div class="book-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Введите название книги']) ?>

    <?= $form->field($model, 'author_ids')->dropDownList($authors, [
        'multiple' => true,
        'size' => 8,
        'prompt' => 'Выберите авторов',
    ])->hint('Зажмите Ctrl (Cmd на Mac) для выбора нескольких авторов') ?>

    <?= $form->field($model, 'year')->textInput([
        'type' => 'number',
        'min' => 1000,
        'max' => date('Y') + 1,
        'placeholder' => 'Например: ' . date('Y')
    ]) ?>

    <?= $form->field($model, 'isbn')->textInput([
        'maxlength' => true,
        'placeholder' => 'Например: 978-5-17-123456-7'
    ])->hint('Введите ISBN-10 или ISBN-13') ?>

    <?= $form->field($model, 'description')->textarea([
        'rows' => 6,
        'placeholder' => 'Краткое описание книги'
    ]) ?>

    <?= $form->field($model, 'imageFile')->fileInput(['accept' => 'image/*']) ?>

    <?php if ($model->image_path): ?>
        <div class="form-group">
            <label>Текущая обложка:</label><br>
            <?= Html::img($model->imageUrl, [
                'alt' => $model->title,
                'class' => 'img-thumbnail',
                'style' => 'max-width: 200px;'
            ]) ?>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>