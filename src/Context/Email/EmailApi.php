<?php

declare(strict_types=1);

namespace App\Context\Email;

use App\Context\Email\Interfaces\SendMailAdapter;
use App\Context\Email\Models\EmailModel;
use App\Context\Email\Services\QueueEmail;
use App\Payload\Payload;
use Psr\Container\ContainerInterface;

use function assert;

class EmailApi
{
    private ContainerInterface $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function sendMail(EmailModel $email): Payload
    {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(SendMailAdapter::class);

        assert($service instanceof SendMailAdapter);

        return $service($email);
    }

    public function queueEmail(EmailModel $email): void
    {
        /** @psalm-suppress MixedAssignment */
        $service = $this->di->get(QueueEmail::class);

        assert($service instanceof QueueEmail);

        $service($email);
    }
}
