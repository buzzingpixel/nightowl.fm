<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\FetchUserByResetToken;
use App\Context\Users\Services\ResetPasswordByToken;
use App\Context\Users\Services\SaveUser;
use App\Payload\Payload;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class ResetPasswordByTokenTest extends TestCase
{
    public function testWhenFetchUserThrowsException(): void
    {
        $fetchUser = $this->createMock(
            FetchUserByResetToken::class
        );

        $fetchUser->method('__invoke')
            ->with(self::equalTo('FooToken'))
            ->willThrowException(new Exception());

        $service = new ResetPasswordByToken(
            $fetchUser,
            $this->createMock(SaveUser::class),
            $this->createMock(PDO::class)
        );

        $payload = $service('FooToken', 'FooPassword');

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult()
        );
    }

    public function testWhenNoUserForResetToken(): void
    {
        $fetchUser = $this->createMock(
            FetchUserByResetToken::class
        );

        $fetchUser->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo('FooToken'))
            ->willReturn(null);

        $service = new ResetPasswordByToken(
            $fetchUser,
            $this->createMock(SaveUser::class),
            $this->createMock(PDO::class)
        );

        $payload = $service('FooToken', 'FooPassword');

        self::assertSame(
            Payload::STATUS_NOT_VALID,
            $payload->getStatus()
        );
    }

    public function testWhenNotUpdated(): void
    {
        $user = new UserModel();

        $fetchUser = $this->createMock(
            FetchUserByResetToken::class
        );

        $fetchUser->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo('FooToken'))
            ->willReturn($user);

        $saveUser = $this->createMock(SaveUser::class);

        $saveUser->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($user))
            ->willReturn(new Payload(Payload::STATUS_ERROR));

        $service = new ResetPasswordByToken(
            $fetchUser,
            $saveUser,
            $this->createMock(PDO::class)
        );

        $payload = $service('FooToken', 'FooPassword');

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            'FooPassword',
            $user->newPassword
        );
    }

    public function test(): void
    {
        $user = new UserModel();

        $fetchUser = $this->createMock(
            FetchUserByResetToken::class
        );

        $fetchUser->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo('FooToken'))
            ->willReturn($user);

        $saveUser = $this->createMock(SaveUser::class);

        $saveUser->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($user))
            ->willReturn(new Payload(Payload::STATUS_UPDATED));

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo(
                [':id' => 'FooToken']
            ))
            ->willReturn(true);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM user_password_reset_tokens WHERE id=:id'
            ))
            ->willReturn($statement);

        $service = new ResetPasswordByToken(
            $fetchUser,
            $saveUser,
            $pdo
        );

        $payload = $service('FooToken', 'FooPassword');

        self::assertSame(
            Payload::STATUS_UPDATED,
            $payload->getStatus()
        );

        self::assertSame(
            'FooPassword',
            $user->newPassword
        );
    }
}
