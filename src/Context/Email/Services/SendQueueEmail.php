<?php

declare(strict_types=1);

namespace App\Context\Email\Services;

use App\Context\Email\Interfaces\SendMailAdapter;
use App\Context\Email\Models\EmailModel;
use App\Payload\Payload;
use Exception;
use Throwable;

use function implode;

use const PHP_EOL;

class SendQueueEmail
{
    private SendMailAdapter $sendMail;

    public function __construct(SendMailAdapter $sendMail)
    {
        $this->sendMail = $sendMail;
    }

    /**
     * @param array<string, array<string>> $context
     *
     * @throws Throwable
     */
    public function __invoke(array $context): void
    {
        $props = $context['model'] ?? [];

        $emailModel            = new EmailModel();
        $emailModel->fromEmail = $props['fromEmail'] ?? '';
        $emailModel->toName    = $props['toName'] ?? '';
        $emailModel->toEmail   = $props['toEmail'] ?? '';
        $emailModel->subject   = $props['subject'] ?? '';
        $emailModel->plainText = $props['plainText'] ?? '';
        $emailModel->html      = $props['html'] ?? '';

        $payload = ($this->sendMail)($emailModel);

        if ($payload->getStatus() === Payload::STATUS_SUCCESSFUL) {
            return;
        }

        $result = $payload->getResult();

        /**
         * @psalm-suppress MixedOperand
         * @psalm-suppress MixedArgument
         */
        throw new Exception(
            'Status: ' . $payload->getStatus() . PHP_EOL .
                'Message: ' . $result['message'] . PHP_EOL .
                'Errors: ' .
                implode(
                    ', ',
                    $result['errors'] ?? []
                )
        );
    }
}
