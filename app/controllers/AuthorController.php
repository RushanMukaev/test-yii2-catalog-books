<?php

namespace app\controllers;

use app\models\Author;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Author controller
 */
class AuthorController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Author::find()
                ->withBooksCount()
                ->orderByFullName(),
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        $booksDataProvider = new ActiveDataProvider([
            'query' => $model->getBooks()
                ->orderBy(['year' => SORT_DESC, 'title' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'booksDataProvider' => $booksDataProvider,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Author();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор успешно добавлен');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор успешно обновлен');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);

        if ($model->booksCount > 0) {
            Yii::$app->session->setFlash(
                'error',
                'Невозможно удалить автора, у которого есть книги. Сначала удалите все книги этого автора.'
            );
            return $this->redirect(['view', 'id' => $id]);
        }

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Автор успешно удален');
        } catch (\Exception $e) {
            Yii::error([
                'message' => 'Ошибка удаления автора',
                'exception' => $e->getMessage(),
                'author_id' => $id,
            ], __METHOD__);

            Yii::$app->session->setFlash('error', 'Ошибка при удалении автора');
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Author
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): Author
    {
        if (($model = Author::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенный автор не найден');
    }
}