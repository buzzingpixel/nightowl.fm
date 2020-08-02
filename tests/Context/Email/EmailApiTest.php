<?php

declare(strict_types=1);

namespace Tests\Context\Email;

use App\Context\Email\EmailApi;
use App\Context\Email\Interfaces\SendMailAdapter;
use App\Context\Email\Models\EmailModel;
use App\Context\Email\Services\QueueEmail;
use App\Payload\Payload;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class EmailApiTest extends TestCase
{
    public function testSendMail(): void
    {
        $payload = new Payload(Payload::STATUS_SUCCESSFUL);

        $email = new EmailModel();

        $service = $this->createMock(SendMailAdapter::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($email))
            ->willReturn($payload);

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(SendMailAdapter::class)
            ->willReturn($service);

        $api = new EmailApi($di);

        self::assertSame(
            $payload,
            $api->sendMail($email)
        );
    }

    public function testQueueEmail(): void
    {
        $email = new EmailModel();

        $service = $this->createMock(QueueEmail::class);

        $service->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($email));

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(QueueEmail::class)
            ->willReturn($service);

        $api = new EmailApi($di);

        $api->queueEmail($email);
    }
}
