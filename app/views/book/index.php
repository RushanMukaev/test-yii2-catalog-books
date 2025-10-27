<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Книги';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Добавить книгу', ['create'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label' => 'Обложка',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::img($model->imageUrl, [
                        'alt' => $model->title,
                        'style' => 'max-width: 80px; max-height: 120px;'
                    ]);
                },
                'contentOptions' => ['style' => 'width: 100px; text-align: center;'],
            ],
            [
                'attribute' => 'title',
                'label' => 'Название',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->title), ['view', 'id' => $model->id]);
                },
            ],
            [
                'label' => 'Авторы',
                'value' => function ($model) {
                    return $model->authorsNames;
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
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'visible' => !Yii::$app->user->isGuest,
                'contentOptions' => ['style' => 'width: 100px;'],
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            $url,
                            [
                                'title' => 'Удалить',
                                'data-confirm' => 'Вы уверены, что хотите удалить эту книгу?',
                                'data-method' => 'post',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

</div>