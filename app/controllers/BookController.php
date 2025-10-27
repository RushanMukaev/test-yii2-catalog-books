<?php

namespace app\controllers;

use app\models\Book;
use app\models\Author;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * Book controller
 */
class BookController extends Controller
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
            'query' => Book::find()
                ->with('authors')
                ->orderBy(['year' => SORT_DESC, 'title' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 20,
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
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Book();

        if ($model->load(Yii::$app->request->post())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if (!$model->save()) {
                    throw new \Exception('Ошибка сохранения книги');
                }

                if ($model->imageFile) {
                    $fileName = $this->uploadBookCover($model);
                    if ($fileName) {
                        $model->image_path = $fileName;
                        $model->save(false);
                    }
                }

                $this->saveBookAuthors($model, $model->author_ids);
                $this->sendBookNotifications($model);

                $transaction->commit();

                Yii::$app->session->setFlash('success', 'Книга успешно добавлена');
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                $transaction->rollBack();

                Yii::error([
                    'message' => 'Ошибка создания книги',
                    'exception' => $e->getMessage(),
                    'model' => $model->attributes,
                ], __METHOD__);

                Yii::$app->session->setFlash('error', 'Ошибка при создании книги: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'authors' => $this->getAuthorsForSelect(),
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
        $oldImagePath = $model->image_path;

        if ($model->load(Yii::$app->request->post())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($model->imageFile) {
                    $fileName = $this->uploadBookCover($model);
                    if ($fileName) {
                        if ($oldImagePath) {
                            $this->deleteBookCover($oldImagePath);
                        }
                        $model->image_path = $fileName;
                    }
                }

                if (!$model->save()) {
                    throw new \Exception('Ошибка сохранения книги');
                }

                $this->saveBookAuthors($model, $model->author_ids);
                $transaction->commit();

                Yii::$app->session->setFlash('success', 'Книга успешно обновлена');
                return $this->redirect(['view', 'id' => $model->id]);

            } catch (\Exception $e) {
                $transaction->rollBack();

                Yii::error([
                    'message' => 'Ошибка обновления книги',
                    'exception' => $e->getMessage(),
                    'model' => $model->attributes,
                ], __METHOD__);

                Yii::$app->session->setFlash('error', 'Ошибка при обновлении книги: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'authors' => $this->getAuthorsForSelect(),
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

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Книга успешно удалена');
        } catch (\Exception $e) {
            Yii::error([
                'message' => 'Ошибка удаления книги',
                'exception' => $e->getMessage(),
                'book_id' => $id,
            ], __METHOD__);

            Yii::$app->session->setFlash('error', 'Ошибка при удалении книги');
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Book
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): Book
    {
        if (($model = Book::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная книга не найдена');
    }

    /**
     * @return array
     */
    protected function getAuthorsForSelect(): array
    {
        return ArrayHelper::map(
            Author::find()->orderByFullName()->all(),
            'id',
            'fullName'
        );
    }

    /**
     * @param Book $model
     * @return string|null
     */
    protected function uploadBookCover(Book $model): ?string
    {
        if (!$model->imageFile) {
            return null;
        }

        $uploadPath = Yii::getAlias('@webroot/uploads/books');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $fileName = uniqid('book_', true) . '.' . $model->imageFile->extension;
        $filePath = $uploadPath . '/' . $fileName;

        if ($model->imageFile->saveAs($filePath)) {
            return $fileName;
        }

        return null;
    }

    /**
     * @param string $fileName
     * @return bool
     */
    protected function deleteBookCover(string $fileName): bool
    {
        $filePath = Yii::getAlias('@webroot/uploads/books/' . $fileName);

        if (file_exists($filePath)) {
            return @unlink($filePath);
        }

        return false;
    }

    /**
     * @param Book $model
     * @param array $authorIds
     * @return void
     */
    protected function saveBookAuthors(Book $model, array $authorIds): void
    {
        Yii::$app->db->createCommand()
            ->delete('{{%book_author}}', ['book_id' => $model->id])
            ->execute();

        if (!empty($authorIds)) {
            $timestamp = time();
            $rows = array_map(
                fn($authorId) => [$model->id, $authorId, $timestamp],
                $authorIds
            );

            Yii::$app->db->createCommand()
                ->batchInsert('{{%book_author}}', ['book_id', 'author_id', 'created_at'], $rows)
                ->execute();
        }
    }

    /**
     * @param Book $model
     * @return void
     */
    protected function sendBookNotifications(Book $model): void
    {
        try {
            if (!Yii::$app->has('sms')) {
                Yii::warning('SMS сервис не настроен', __METHOD__);
                return;
            }

            $result = Yii::$app->sms->notifyNewBook($model);

            Yii::info([
                'book_id' => $model->id,
                'book_title' => $model->title,
                'notifications' => $result,
            ], 'sms-notifications');

        } catch (\Exception $e) {
            Yii::error([
                'message' => 'Ошибка отправки SMS уведомлений',
                'exception' => $e->getMessage(),
                'book_id' => $model->id,
            ], __METHOD__);
        }
    }
}