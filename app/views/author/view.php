<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

/** @var yii\web\View $this */
/** @var app\models\Author $model */

$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// DataProvider для книг автора
$booksDataProvider = new ActiveDataProvider([
    'query' => $model->getBooks(),
    'pagination' => [
        'pageSize' => 10,
    ],
]);
?>
<div class="author-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить этого автора?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php else: ?>
            <?= Html::a('Подписаться на автора', ['/subscription/create', 'author_id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'first_name:text:Имя',
            'last_name:text:Фамилия',
            'middle_name:text:Отчество',
            [
                'label' => 'Количество книг',
                'value' => $model->booksCount,
            ],
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

    <h2>Книги автора</h2>

    <?= GridView::widget([
        'dataProvider' => $booksDataProvider,
        'columns' => [
            [
                'label' => 'Обложка',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::img($model->imageUrl, [
                        'alt' => $model->title,
                        'style' => 'max-width: 60px; max-height: 90px;'
                    ]);
                },
                'contentOptions' => ['style' => 'width: 80px; text-align: center;'],
            ],
            [
                'attribute' => 'title',
                'label' => 'Название',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->title), ['/book/view', 'id' => $model->id]);
                },
            ],
            [
                'attribute' => 'year',
                'label' => 'Год',
                'contentOptions' => ['style' => 'width: 80px; text-align: center;'],
            ],
            [
                'attribute' => 'isbn',
                'label' => 'ISBN',
                'value' => function ($model) {
                    return $model->formattedIsbn;
                },
            ],
        ],
    ]); ?>

</div>