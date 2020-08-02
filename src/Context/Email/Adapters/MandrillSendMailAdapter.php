<?php

declare(strict_types=1);

namespace App\Context\Email\Adapters;

use App\Context\Email\Configuration\MandrillConfig;
use App\Context\Email\Interfaces\SendMailAdapter;
use App\Context\Email\Models\EmailModel;
use App\Payload\Payload;
use App\Utilities\SimpleValidator;
use Html2Text\Html2Text;
use Mandrill;
use Throwable;

use function assert;
use function count;
use function is_array;

class MandrillSendMailAdapter implements SendMailAdapter
{
    private Mandrill $mandrill;
    private MandrillConfig $mandrillConfig;

    public function __construct(
        Mandrill $mandrill,
        MandrillConfig $mandrillConfig
    ) {
        $this->mandrill       = $mandrill;
        $this->mandrillConfig = $mandrillConfig;
    }

    public function __invoke(EmailModel $emailModel): Payload
    {
        try {
            return $this->innerRun($emailModel);
        } catch (Throwable $e) {
            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => 'An unknown error occurred']
            );
        }
    }

    private function innerRun(EmailModel $emailModel): Payload
    {
        $errors = [];

        if ($emailModel->plainText === '' && $emailModel->html === '') {
            $errors[] = 'Plain text or HTML must be provided';
        }

        if (
            $emailModel->fromEmail !== '' &&
            ! SimpleValidator::email($emailModel->fromEmail)
        ) {
            $errors[] = 'From email address must be valid';
        }

        if (count($errors) > 0) {
            return new Payload(
                Payload::STATUS_ERROR,
                [
                    'message' => 'The email was not valid',
                    'errors' => $errors,
                ],
            );
        }

        if ($emailModel->toName === '') {
            $emailModel->toName = $emailModel->toEmail;
        }

        if ($emailModel->plainText === '') {
            $emailModel->plainText = (new Html2Text($emailModel->html))
                ->getText();
        }

        $message = [
            'text' => $emailModel->plainText,
            'subject' => $emailModel->subject,
            'from_email' => $this->mandrillConfig->fromEmail,
            'from_name' => $this->mandrillConfig->fromName,
            'to' => [
                [
                    'email' => $emailModel->toEmail,
                    'name' => $emailModel->toName,
                    'type' => 'to',
                ],
            ],
            'important' => false,
            'track_opens' => false,
            'track_clicks' => false,
            'auto_text' => false,
            'auto_html' => false,
            'inline_css' => false,
            'url_strip_qs' => false,
            'preserve_recipients' => true,
            'view_content_link' => true,
            'bcc_address' => null,
            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
        ];

        if ($emailModel->html !== '') {
            $message['html'] = $emailModel->html;
        }

        if ($emailModel->fromEmail !== '') {
            $message['headers'] = [
                'Reply-To' => $emailModel->fromEmail,
            ];
        }

        /**
         * Mandrill's PHP API library is terrible
         *
         * @noinspection PhpParamsInspection
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress UndefinedPropertyFetch
         * @psalm-suppress MixedAssignment
         */
        $returnMsg = $this->mandrill->messages->send($message)[0];

        assert(is_array($returnMsg));

        if ($returnMsg['status'] !== 'sent') {
            return new Payload(
                Payload::STATUS_ERROR,
                [
                    'message' => 'There was a problem sending the email',
                    'errors' => $returnMsg,
                ]
            );
        }

        return new Payload(
            Payload::STATUS_SUCCESSFUL,
            [
                'message' => 'The email has been sent',
                'success_message' => $returnMsg,
            ]
        );
    }
}
