<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\CreateUserSession;
use App\Context\Users\Services\LogUserIn;
use App\Context\Users\Services\ValidateUserPassword;
use App\Payload\Payload;
use buzzingpixel\cookieapi\CookieApi;
use buzzingpixel\cookieapi\interfaces\CookieInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Throwable;

use function assert;
use function date;
use function func_get_args;

class LogUserInTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testWhenPasswordDoesNotVerify(): void
    {
        $userModel = new UserModel();

        $validateUserPassword = $this->createMock(
            ValidateUserPassword::class
        );

        $validateUserPassword->expects(self::once())
            ->method('__invoke')
            ->with(
                self::equalTo($userModel),
                self::equalTo('FakePass'),
            )
            ->willReturn(false);

        $service = new LogUserIn(
            $validateUserPassword,
            $this->createMock(
                CreateUserSession::class
            ),
            $this->createMock(CookieApi::class)
        );

        $payload = $service(
            $userModel,
            'FakePass'
        );

        self::assertSame(
            Payload::STATUS_NOT_VALID,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'Your password is invalid'],
            $payload->getResult()
        );
    }

    /**
     * @throws Throwable
     */
    public function testLogInWhenUnableToCreateSession(): void
    {
        $user = new UserModel();

        $validateUserPassword = $this->createMock(
            ValidateUserPassword::class
        );

        $validateUserPassword->expects(self::once())
            ->method('__invoke')
            ->with(
                self::equalTo($user),
                self::equalTo('FooBarPassword'),
            )
            ->willReturn(true);

        $password = 'FooBarPassword';

        $createSessionPayload = new Payload(
            Payload::STATUS_NOT_VALID,
            ['id' => 'FooBarId']
        );

        $service = new LogUserIn(
            $validateUserPassword,
            $this->mockCreateUserSession(
                $user,
                $createSessionPayload
            ),
            $this->mockCookieApi(false)
        );

        $payload = $service($user, $password);

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            [],
            $payload->getResult()
        );
    }

    /**
     * @throws Throwable
     */
    public function testLogIn(): void
    {
        $user = new UserModel();

        $validateUserPassword = $this->createMock(
            ValidateUserPassword::class
        );

        $validateUserPassword->expects(self::once())
            ->method('__invoke')
            ->with(
                self::equalTo($user),
                self::equalTo('FooBarPassword'),
            )
            ->willReturn(true);

        $password = 'FooBarPassword';

        $createSessionPayload = new Payload(
            Payload::STATUS_CREATED,
            ['id' => 'FooBarId']
        );

        $service = new LogUserIn(
            $validateUserPassword,
            $this->mockCreateUserSession(
                $user,
                $createSessionPayload
            ),
            $this->mockCookieApi(true)
        );

        $payload = $service($user, $password);

        self::assertSame(
            Payload::STATUS_SUCCESSFUL,
            $payload->getStatus()
        );

        self::assertSame(
            ['id' => 'FooBarId'],
            $payload->getResult()
        );

        self::assertCount(
            7,
            $this->makeCookieCallArgs
        );

        self::assertSame(
            'user_session_token',
            $this->makeCookieCallArgs[0]
        );

        self::assertSame(
            'FooBarId',
            $this->makeCookieCallArgs[1]
        );

        $cookieDateTime = $this->makeCookieCallArgs[2];

        assert(
            $cookieDateTime instanceof DateTimeImmutable ||
            $cookieDateTime === null
        );

        self::assertInstanceOf(
            DateTimeImmutable::class,
            $cookieDateTime
        );

        $currentYear = (int) date('Y');

        $yearPlus20 = (string) ($currentYear + 20);

        self::assertSame(
            $yearPlus20,
            $cookieDateTime->format('Y')
        );

        self::assertSame(
            '/',
            $this->makeCookieCallArgs[3]
        );

        self::assertSame(
            '',
            $this->makeCookieCallArgs[4]
        );

        self::assertFalse($this->makeCookieCallArgs[5]);

        self::assertTrue($this->makeCookieCallArgs[6]);

        self::assertCount(
            1,
            $this->saveCookieCallArgs
        );

        $saveCookieCookie = $this->saveCookieCallArgs[0];

        assert(
            $saveCookieCookie instanceof CookieInterface ||
            $saveCookieCookie === null
        );

        self::assertInstanceOf(
            CookieInterface::class,
            $saveCookieCookie
        );

        self::assertSame(
            'FooBarCookieName',
            $saveCookieCookie->name()
        );
    }

    /**
     * @return CreateUserSession&MockObject
     */
    private function mockCreateUserSession(
        UserModel $expectUser,
        Payload $returnPayload
    ): CreateUserSession {
        $mock = $this->createMock(CreateUserSession::class);

        $mock->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($expectUser))
            ->willReturn($returnPayload);

        return $mock;
    }

    /**
     * @return CookieApi&MockObject
     */
    private function mockCookieApi(bool $expectSave): CookieApi
    {
        $this->makeCookieCallArgs = [];

        $this->saveCookieCallArgs = [];

        $mock = $this->createMock(CookieApi::class);

        if (! $expectSave) {
            $mock->expects(self::never())
                ->method(self::anything());

            return $mock;
        }

        $mock->expects(self::once())
            ->method('makeCookie')
            ->willReturnCallback([$this, 'makeCookieCallback']);

        $mock->expects(self::once())
            ->method('saveCookie')
            ->willReturnCallback([$this, 'saveCookieCallback']);

        return $mock;
    }

    /** @var mixed[] */
    private array $makeCookieCallArgs = [];

    public function makeCookieCallback(): CookieInterface
    {
        $this->makeCookieCallArgs = func_get_args();

        $cookie = $this->createMock(CookieInterface::class);

        $cookie->method('name')->willReturn('FooBarCookieName');

        return $cookie;
    }

    /** @var mixed[] */
    private array $saveCookieCallArgs = [];

    public function saveCookieCallback(): void
    {
        $this->saveCookieCallArgs = func_get_args();
    }
}
