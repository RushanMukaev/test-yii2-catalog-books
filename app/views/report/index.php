<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var int $year */
/** @var array $availableYears */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Отчет ТОП-10 авторов';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="well">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
        ]); ?>

        <div class="row">
            <div class="col-md-3">
                <?= Html::label('Выберите год:', 'year', ['class' => 'control-label']) ?>
                <?php
                $yearOptions = empty($availableYears) ? [] : array_combine($availableYears, $availableYears);
                ?>
                <?= Html::dropDownList('year', $year, $yearOptions, [
                    'class' => 'form-control',
                    'id' => 'year',
                    'onchange' => 'this.form.submit()',
                    'prompt' => empty($availableYears) ? 'Нет данных' : 'Выберите год',
                ]) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <?php
    $authors = $dataProvider->getModels();
    if (empty($authors)):
    ?>
        <div class="alert alert-info">
            За <?= $year ?> год книг не найдено.
        </div>
    <?php else: ?>
        <h2>ТОП-10 авторов за <?= $year ?> год</h2>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th style="width: 80px; text-align: center;">Место</th>
                    <th>ФИО автора</th>
                    <th style="width: 150px; text-align: center;">Количество книг</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $index => $author): ?>
                    <tr>
                        <td style="text-align: center;">
                            <strong><?= $index + 1 ?></strong>
                        </td>
                        <td>
                            <?= Html::a(
                                Html::encode($author->fullName),
                                ['/author/view', 'id' => $author->id],
                                ['class' => 'author-link']
                            ) ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge bg-primary"><?= $author->books_count ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="alert alert-info" style="margin-top: 20px;">
            <strong>Примечание:</strong> В отчет включены только те авторы, чьи книги были выпущены в <?= $year ?> году.
            Если у автора несколько книг за этот год, он получает более высокое место в рейтинге.
        </div>
    <?php endif; ?>

</div>