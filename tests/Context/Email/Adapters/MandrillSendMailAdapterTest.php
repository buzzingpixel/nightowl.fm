<?php

declare(strict_types=1);

namespace Tests\Context\Email\Adapters;

use App\Context\Email\Adapters\MandrillSendMailAdapter;
use App\Context\Email\Configuration\MandrillConfig;
use App\Context\Email\Models\EmailModel;
use App\Payload\Payload;
use Exception;
use Mandrill;
use Mandrill_Messages;
use PHPUnit\Framework\TestCase;

class MandrillSendMailAdapterTest extends TestCase
{
    private function createMandrillConfig(): MandrillConfig
    {
        $mandrillConfig = new MandrillConfig();

        $mandrillConfig->fromName = 'Foo From Name';

        $mandrillConfig->fromEmail = 'foo@bar.baz';

        return $mandrillConfig;
    }

    public function testErrors(): void
    {
        $emailModel            = new EmailModel();
        $emailModel->fromEmail = 'foo';

        $mandrill = $this->createMock(Mandrill::class);

        $adapter = new MandrillSendMailAdapter(
            $mandrill,
            $this->createMandrillConfig(),
        );

        $payload = $adapter($emailModel);

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            [
                'message' => 'The email was not valid',
                'errors' => [
                    'Plain text or HTML must be provided',
                    'From email address must be valid',
                ],
            ],
            $payload->getResult()
        );
    }

    public function testWhenThrows(): void
    {
        $emailModel            = new EmailModel();
        $emailModel->fromEmail = 'baz@bar.foo';
        $emailModel->toName    = 'foo-to-name';
        $emailModel->toEmail   = 'bar@foo.baz';
        $emailModel->subject   = 'foo-subject';
        $emailModel->plainText = 'foo-plain-text';
        $emailModel->html      = 'foo-html';

        $mandrill = $this->createMock(Mandrill::class);

        $mandrill->messages = $this->createMock(
            Mandrill_Messages::class
        );

        $mandrill->messages->expects(self::once())
            ->method('send')
            ->with(self::equalTo([
                'text' => 'foo-plain-text',
                'subject' => 'foo-subject',
                'from_email' => 'foo@bar.baz',
                'from_name' => 'Foo From Name',
                'to' => [
                    [
                        'email' => 'bar@foo.baz',
                        'name' => 'foo-to-name',
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
                'html' => 'foo-html',
                'headers' => ['Reply-To' => 'baz@bar.foo'],
            ]))
            ->willThrowException(new Exception());

        $adapter = new MandrillSendMailAdapter(
            $mandrill,
            $this->createMandrillConfig(),
        );

        $payload = $adapter($emailModel);

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult()
        );
    }

    public function testWhenStatusNotSent(): void
    {
        $emailModel          = new EmailModel();
        $emailModel->toEmail = 'bar@foo.baz';
        $emailModel->subject = 'foo-subject';
        $emailModel->html    = '<body>foo-html</body>';

        $mandrill = $this->createMock(Mandrill::class);

        $mandrill->messages = $this->createMock(
            Mandrill_Messages::class
        );

        $mandrill->messages->expects(self::once())
            ->method('send')
            ->with(self::equalTo([
                'text' => 'foo-html',
                'subject' => 'foo-subject',
                'from_email' => 'foo@bar.baz',
                'from_name' => 'Foo From Name',
                'to' => [
                    [
                        'email' => 'bar@foo.baz',
                        'name' => 'bar@foo.baz',
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
                'html' => '<body>foo-html</body>',
            ]))
            ->willReturn([['status' => 'foo']]);

        $adapter = new MandrillSendMailAdapter(
            $mandrill,
            $this->createMandrillConfig(),
        );

        $payload = $adapter($emailModel);

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            [
                'message' => 'There was a problem sending the email',
                'errors' => ['status' => 'foo'],
            ],
            $payload->getResult()
        );
    }

    public function test(): void
    {
        $emailModel            = new EmailModel();
        $emailModel->toEmail   = 'bar@foo.baz';
        $emailModel->subject   = 'foo-subject';
        $emailModel->plainText = '<body>foo-html</body>';

        $mandrill = $this->createMock(Mandrill::class);

        $mandrill->messages = $this->createMock(
            Mandrill_Messages::class
        );

        $mandrill->messages->expects(self::once())
            ->method('send')
            ->with(self::equalTo([
                'text' => '<body>foo-html</body>',
                'subject' => 'foo-subject',
                'from_email' => 'foo@bar.baz',
                'from_name' => 'Foo From Name',
                'to' => [
                    [
                        'email' => 'bar@foo.baz',
                        'name' => 'bar@foo.baz',
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
            ]))
            ->willReturn([['status' => 'sent']]);

        $adapter = new MandrillSendMailAdapter(
            $mandrill,
            $this->createMandrillConfig(),
        );

        $payload = $adapter($emailModel);

        self::assertSame(
            Payload::STATUS_SUCCESSFUL,
            $payload->getStatus()
        );

        self::assertSame(
            [
                'message' => 'The email has been sent',
                'success_message' => ['status' => 'sent'],
            ],
            $payload->getResult()
        );
    }
}
