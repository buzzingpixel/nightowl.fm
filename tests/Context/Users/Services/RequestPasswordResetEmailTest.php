<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Email\EmailApi;
use App\Context\Email\Models\EmailModel;
use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\GeneratePasswordResetToken;
use App\Context\Users\Services\RequestPasswordResetEmail;
use App\Payload\Payload;
use Config\General;
use PHPUnit\Framework\TestCase;
use Throwable;
use Twig\Environment as TwigEnvironment;

class RequestPasswordResetEmailTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testWhenNotCreated(): void
    {
        $user = new UserModel();

        $generatePasswordResetToken = $this->createMock(
            GeneratePasswordResetToken::class
        );

        $generatePasswordResetToken->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($user))
            ->willReturn(new Payload(Payload::STATUS_ERROR));

        $emailApi = $this->createMock(EmailApi::class);

        $emailApi->expects(self::never())
            ->method(self::anything());

        $twigEnvironment = $this->createMock(
            TwigEnvironment::class
        );

        $twigEnvironment->expects(self::never())
            ->method(self::anything());

        $generalConfig = $this->createMock(General::class);

        $generalConfig->expects(self::never())
            ->method(self::anything());

        $service = new RequestPasswordResetEmail(
            $generatePasswordResetToken,
            $emailApi,
            $twigEnvironment,
            $generalConfig,
        );

        $service($user);
    }

    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $user               = new UserModel();
        $user->emailAddress = 'fooEmailAddress';

        $generatePasswordResetToken = $this->createMock(
            GeneratePasswordResetToken::class
        );

        $generatePasswordResetToken->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($user))
            ->willReturn(new Payload(Payload::STATUS_CREATED, [
                'foo' => 'bar',
                'id' => 'testId',
            ]));

        $emailApi = $this->createMock(EmailApi::class);

        $emailApi->expects(self::once())
            ->method('queueEmail')
            ->willReturnCallback(
                static function (EmailModel $emailModel): void {
                    self::assertSame(
                        'info@nightowl.fm',
                        $emailModel->fromEmail,
                    );

                    self::assertSame(
                        'fooEmailAddress',
                        $emailModel->toEmail,
                    );

                    self::assertSame(
                        'Reset your password on NightOwl.fm',
                        $emailModel->subject,
                    );

                    self::assertSame(
                        'fooTwigRenderResult',
                        $emailModel->plainText,
                    );
                }
            );

        $twigEnvironment = $this->createMock(
            TwigEnvironment::class
        );

        $twigEnvironment->expects(self::once())
            ->method('render')
            ->with(
                self::equalTo('Email/PasswordResetEmail.twig'),
                self::equalTo(
                    [
                        'emailAddress' => $user->emailAddress,
                        'link' => 'testSiteUrl' .
                            '/account/reset-pw-with-token/' .
                            'testId',
                    ]
                )
            )
            ->willReturn('fooTwigRenderResult');

        $generalConfig = $this->createMock(General::class);

        $generalConfig->method('__call')
            ->willReturnCallback(
                static fn (string $n) => $n === 'siteUrl' ?
                    'testSiteUrl' :
                    null,
            );

        $service = new RequestPasswordResetEmail(
            $generatePasswordResetToken,
            $emailApi,
            $twigEnvironment,
            $generalConfig,
        );

        $service($user);
    }
}
