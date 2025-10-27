<?php

namespace app\services;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;

/**
 * SMS notification service via SmsPilot API
 *
 * @property string $apiKey
 * @property string $apiUrl
 * @property string $sender
 */
class SmsService extends Component
{
    /**
     * @var string
     */
    public string $apiKey = '';

    /**
     * @var string
     */
    public string $apiUrl = 'https://smspilot.ru/api.php';

    /**
     * @var string
     */
    public string $sender = 'INFORM';

    /**
     * @var bool
     */
    public bool $debug = false;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->apiKey)) {
            throw new \yii\base\InvalidConfigException('API ключ SmsPilot не указан');
        }
    }

    /**
     * @param string $phone
     * @param string $message
     * @return array
     */
    public function send(string $phone, string $message): array
    {
        if (!$this->validatePhone($phone)) {
            return [
                'success' => false,
                'message' => 'Некорректный формат номера телефона',
                'data' => [],
            ];
        }

        if (empty(trim($message))) {
            return [
                'success' => false,
                'message' => 'Текст сообщения не может быть пустым',
                'data' => [],
            ];
        }

        if (mb_strlen($message) > 1000) {
            return [
                'success' => false,
                'message' => 'Текст сообщения слишком длинный (максимум 1000 символов)',
                'data' => [],
            ];
        }

        try {
            $client = new Client(['baseUrl' => $this->apiUrl]);

            $response = $client->createRequest()
                ->setMethod('POST')
                ->setData([
                    'send' => $message,
                    'to' => $this->normalizePhone($phone),
                    'from' => $this->sender,
                    'apikey' => $this->apiKey,
                    'format' => 'json',
                ])
                ->send();

            if ($this->debug) {
                Yii::info([
                    'phone' => $phone,
                    'message' => $message,
                    'response' => $response->data,
                ], __METHOD__);
            }

            if ($response->isOk && isset($response->data['send'])) {
                $sendData = $response->data['send'][0] ?? [];

                if (isset($sendData['status']) && $sendData['status'] === 'accepted') {
                    return [
                        'success' => true,
                        'message' => 'SMS успешно отправлено',
                        'data' => $sendData,
                    ];
                }

                $errorMessage = $sendData['error']['description'] ?? 'Неизвестная ошибка';
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => $sendData,
                ];
            }

            return [
                'success' => false,
                'message' => 'Ошибка соединения с сервером SmsPilot',
                'data' => $response->data ?? [],
            ];

        } catch (\Exception $e) {
            Yii::error([
                'message' => 'Ошибка отправки SMS',
                'exception' => $e->getMessage(),
                'phone' => $phone,
            ], __METHOD__);

            return [
                'success' => false,
                'message' => 'Ошибка отправки SMS: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * @param array<string> $phones
     * @param string $message
     * @return array
     */
    public function sendBulk(array $phones, string $message): array
    {
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($phones as $phone) {
            $result = $this->send($phone, $message);
            $results[$phone] = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'total' => count($phones),
            'success' => $successCount,
            'failed' => $failedCount,
            'results' => $results,
        ];
    }

    /**
     * @param string $phone
     * @return bool
     */
    private function validatePhone(string $phone): bool
    {
        return (bool)preg_match('/^\+7\d{10}$/', $phone);
    }

    /**
     * @param string $phone
     * @return string
     */
    private function normalizePhone(string $phone): string
    {
        return ltrim($phone, '+');
    }

    /**
     * @param \app\models\Book $book
     * @return array
     */
    public function notifyNewBook(\app\models\Book $book): array
    {
        $authors = $book->authors;

        if (empty($authors)) {
            return [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'results' => [],
            ];
        }

        $authorIds = array_map(fn($author) => $author->id, $authors);
        $subscriptions = \app\models\Subscription::findByAuthorIds($authorIds);

        if (empty($subscriptions)) {
            return [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'results' => [],
            ];
        }

        $authorsNames = implode(', ', array_map(fn($a) => $a->shortName, $authors));
        $message = sprintf(
            "Новая книга: «%s» (%d г.)\nАвтор: %s",
            $book->title,
            $book->year,
            $authorsNames
        );

        $phones = array_unique(array_map(fn($s) => $s->phone, $subscriptions));

        return $this->sendBulk($phones, $message);
    }
}