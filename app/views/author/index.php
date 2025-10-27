<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Авторы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Добавить автора', ['create'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'fullName',
                'label' => 'ФИО',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->fullName), ['view', 'id' => $model->id]);
                },
            ],
            [
                'label' => 'Количество книг',
                'value' => function ($model) {
                    return $model->booksCount;
                },
                'contentOptions' => ['style' => 'width: 150px; text-align: center;'],
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
                                'data-confirm' => 'Вы уверены, что хотите удалить этого автора?',
                                'data-method' => 'post',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

</div>