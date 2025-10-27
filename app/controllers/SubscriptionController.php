<?php

namespace app\controllers;

use app\models\Subscription;
use app\models\Author;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Subscription controller
 */
class SubscriptionController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * @param int $author_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreate(int $author_id)
    {
        $author = $this->findAuthor($author_id);

        $model = new Subscription();
        $model->author_id = $author_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(
                'success',
                sprintf(
                    'Вы успешно подписались на автора %s. Вы будете получать SMS уведомления о новых книгах.',
                    $author->fullName
                )
            );

            return $this->redirect(['author/view', 'id' => $author_id]);
        }

        return $this->render('create', [
            'model' => $model,
            'author' => $author,
        ]);
    }

    /**
     * @param int $id
     * @return Author
     * @throws NotFoundHttpException
     */
    protected function findAuthor(int $id): Author
    {
        if (($model = Author::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенный автор не найден');
    }
}