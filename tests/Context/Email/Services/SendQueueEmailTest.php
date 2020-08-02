<?php

declare(strict_types=1);

namespace Tests\Context\Email\Services;

use App\Context\Email\Interfaces\SendMailAdapter;
use App\Context\Email\Models\EmailModel;
use App\Context\Email\Services\SendQueueEmail;
use App\Payload\Payload;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;

use function assert;
use function Safe\json_decode;
use function Safe\json_encode;

use const PHP_EOL;

class SendQueueEmailTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testWhenSuccessful(): void
    {
        $emailModel            = new EmailModel();
        $emailModel->fromEmail = 'fromEmail';
        $emailModel->toName    = 'toName';
        $emailModel->toEmail   = 'toEmail';
        $emailModel->subject   = 'subject';
        $emailModel->plainText = 'plainText';
        $emailModel->html      = 'html';

        $json = json_encode(['model' => $emailModel]);

        $context = json_decode($json, true);

        $sendMailAdapter = $this->createMock(
            SendMailAdapter::class
        );

        $sendMailAdapter->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (EmailModel $model) use (
                    $emailModel
                ): Payload {
                    self::assertSame(
                        $emailModel->fromEmail,
                        $model->fromEmail,
                    );

                    self::assertSame(
                        $emailModel->toName,
                        $model->toName,
                    );

                    self::assertSame(
                        $emailModel->toEmail,
                        $model->toEmail,
                    );

                    self::assertSame(
                        $emailModel->subject,
                        $model->subject,
                    );

                    self::assertSame(
                        $emailModel->plainText,
                        $model->plainText,
                    );

                    self::assertSame(
                        $emailModel->html,
                        $model->html,
                    );

                    return new Payload(Payload::STATUS_SUCCESSFUL);
                }
            );

        $service = new SendQueueEmail($sendMailAdapter);

        $service($context);
    }

    /**
     * @throws Throwable
     */
    public function testWhenUnsuccessful(): void
    {
        $emailModel            = new EmailModel();
        $emailModel->fromEmail = 'fromEmail';
        $emailModel->toName    = 'toName';
        $emailModel->toEmail   = 'toEmail';
        $emailModel->subject   = 'subject';
        $emailModel->plainText = 'plainText';
        $emailModel->html      = 'html';

        $json = json_encode(['model' => $emailModel]);

        $context = json_decode($json, true);

        $sendMailAdapter = $this->createMock(
            SendMailAdapter::class
        );

        $sendMailAdapter->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (EmailModel $model) use (
                    $emailModel
                ): Payload {
                    self::assertSame(
                        $emailModel->fromEmail,
                        $model->fromEmail,
                    );

                    self::assertSame(
                        $emailModel->toName,
                        $model->toName,
                    );

                    self::assertSame(
                        $emailModel->toEmail,
                        $model->toEmail,
                    );

                    self::assertSame(
                        $emailModel->subject,
                        $model->subject,
                    );

                    self::assertSame(
                        $emailModel->plainText,
                        $model->plainText,
                    );

                    self::assertSame(
                        $emailModel->html,
                        $model->html,
                    );

                    return new Payload(
                        Payload::STATUS_ERROR,
                        [
                            'message' => 'Test Message',
                            'errors' => [
                                'TestError1',
                                'TestError2',
                            ],
                        ]
                    );
                }
            );

        $service = new SendQueueEmail($sendMailAdapter);

        $exception = null;

        try {
            $service($context);
        } catch (Throwable $e) {
            $exception = $e;
        }

        assert($exception instanceof  Exception);

        self::assertSame(
            'Status: ERROR' . PHP_EOL .
            'Message: Test Message' . PHP_EOL .
            'Errors: TestError1, TestError2',
            $exception->getMessage()
        );
    }
}
