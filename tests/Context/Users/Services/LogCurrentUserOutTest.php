<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Services\LogCurrentUserOut;
use App\Payload\Payload;
use buzzingpixel\cookieapi\interfaces\CookieApiInterface;
use buzzingpixel\cookieapi\interfaces\CookieInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class LogCurrentUserOutTest extends TestCase
{
    public function testWhenNoCookie(): void
    {
        $cookieApi = $this->createMock(
            CookieApiInterface::class
        );

        $cookieApi->expects(self::once())
            ->method('retrieveCookie')
            ->with(self::equalTo('user_session_token'))
            ->willReturn(null);

        $service = new LogCurrentUserOut(
            $cookieApi,
            $this->createMock(PDO::class)
        );

        $payload = $service();

        self::assertSame(
            Payload::STATUS_NOT_VALID,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'User is not logged in'],
            $payload->getResult()
        );
    }

    public function testWhenCookieHasEmptyValue(): void
    {
        $cookie = $this->createMock(CookieInterface::class);

        $cookie->method('value')->willReturn('');

        $cookieApi = $this->createMock(
            CookieApiInterface::class
        );

        $cookieApi->expects(self::at(0))
            ->method('retrieveCookie')
            ->with(self::equalTo('user_session_token'))
            ->willReturn($cookie);

        $cookieApi->expects(self::at(1))
            ->method('deleteCookie')
            ->with(self::equalTo($cookie));

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::never())
            ->method(self::anything());

        $service = new LogCurrentUserOut($cookieApi, $pdo);

        $payload = $service();

        self::assertSame(
            Payload::STATUS_SUCCESSFUL,
            $payload->getStatus()
        );
    }

    public function test(): void
    {
        $cookie = $this->createMock(CookieInterface::class);

        $cookie->method('value')->willReturn('FooId');

        $cookieApi = $this->createMock(
            CookieApiInterface::class
        );

        $cookieApi->expects(self::at(0))
            ->method('retrieveCookie')
            ->with(self::equalTo('user_session_token'))
            ->willReturn($cookie);

        $cookieApi->expects(self::at(1))
            ->method('deleteCookie')
            ->with(self::equalTo($cookie));

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo([':id' => 'FooId']))
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM user_sessions WHERE id=:id'
            ))
            ->willReturn($statement);

        $service = new LogCurrentUserOut($cookieApi, $pdo);

        $payload = $service();

        self::assertSame(
            Payload::STATUS_SUCCESSFUL,
            $payload->getStatus()
        );
    }
}
