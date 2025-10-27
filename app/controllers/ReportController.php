<?php

namespace app\controllers;

use app\models\Author;
use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;

/**
 * Report controller
 */
class ReportController extends Controller
{
    /**
     * @param int|null $year
     * @return string
     */
    public function actionIndex(?int $year = null): string
    {
        if ($year === null) {
            $year = (int)date('Y');
        }

        if ($year < 1000 || $year > (int)date('Y') + 1) {
            Yii::$app->session->setFlash('error', 'Некорректный год');
            $year = (int)date('Y');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Author::find()->topByBooksInYear($year, 10),
            'pagination' => false,
        ]);

        $availableYears = $this->getAvailableYears();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'year' => $year,
            'availableYears' => $availableYears,
        ]);
    }

    /**
     * @return array
     */
    protected function getAvailableYears(): array
    {
        $years = (new \yii\db\Query())
            ->select('year')
            ->distinct()
            ->from('{{%books}}')
            ->orderBy(['year' => SORT_DESC])
            ->column();

        return array_map('intval', $years);
    }
}