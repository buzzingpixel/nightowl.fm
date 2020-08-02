<?php

declare(strict_types=1);

namespace Tests\Context\Email\Services;

use App\Context\Email\Models\EmailModel;
use App\Context\Email\Services\QueueEmail;
use App\Context\Email\Services\SendQueueEmail;
use App\Context\Queue\Models\QueueModel;
use App\Context\Queue\QueueApi;
use App\Payload\Payload;
use PHPUnit\Framework\TestCase;

class QueueEmailTest extends TestCase
{
    public function test(): void
    {
        $emailModel = new EmailModel();

        $queueApi = $this->createMock(QueueApi::class);

        $queueApi->expects(self::once())
            ->method('addToQueue')
            ->willReturnCallback(static function (
                QueueModel $model
            ) use (
                $emailModel
            ): Payload {
                self::assertSame(
                    'send-email',
                    $model->handle,
                );

                self::assertSame(
                    'Send Email',
                    $model->displayName,
                );

                self::assertCount(1, $model->items);

                $item = $model->items[0];

                self::assertSame(
                    SendQueueEmail::class,
                    $item->class,
                );

                self::assertSame(
                    $emailModel,
                    /** @phpstan-ignore-next-line */
                    $item->context['model'],
                );

                return new Payload(Payload::STATUS_SUCCESSFUL);
            });

        $service = new QueueEmail($queueApi);

        $service($emailModel);
    }
}
