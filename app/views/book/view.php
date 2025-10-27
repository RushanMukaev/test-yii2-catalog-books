<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Book $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!Yii::$app->user->isGuest): ?>
        <p>
            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить эту книгу?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <?= Html::img($model->imageUrl, [
                'alt' => $model->title,
                'class' => 'img-fluid img-thumbnail',
                'style' => 'max-width: 300px;'
            ]) ?>
        </div>
        <div class="col-md-9">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'title:text:Название',
                    [
                        'label' => 'Авторы',
                        'value' => $model->authorsNames,
                    ],
                    [
                        'attribute' => 'year',
                        'label' => 'Год выпуска',
                        'value' => $model->displayYear,
                    ],
                    [
                        'attribute' => 'isbn',
                        'label' => 'ISBN',
                        'value' => $model->formattedIsbn,
                    ],
                    'description:ntext:Описание',
                    [
                        'attribute' => 'created_at',
                        'label' => 'Дата добавления',
                        'format' => ['datetime', 'php:d.m.Y H:i'],
                    ],
                    [
                        'attribute' => 'updated_at',
                        'label' => 'Дата обновления',
                        'format' => ['datetime', 'php:d.m.Y H:i'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

</div>