<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\FetchUserById;
use App\Context\Users\Services\FetchUserByResetToken;
use App\Persistence\Users\UserPasswordResetTokenRecord;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchUserByResetTokenTest extends TestCase
{
    public function testWhenPdoThrows(): void
    {
        $pdo = $this->createMock(PDO::class);

        $pdo->method(self::anything())->willThrowException(
            new Exception()
        );

        $service = new FetchUserByResetToken(
            $pdo,
            $this->createMock(FetchUserById::class)
        );

        self::assertNull($service('FooToken'));
    }

    public function testWhenQueryReturnsNoResult(): void
    {
        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::once())
            ->method('execute')
            ->with(self::equalTo(
                [':id' => 'FooToken']
            ))
            ->willReturn(true);

        $statement->expects(self::once())
            ->method('fetchObject')
            ->with(self::equalTo(
                UserPasswordResetTokenRecord::class
            ))
            ->willReturn(false);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT * FROM user_password_reset_tokens WHERE id=:id'
            ))
            ->willReturn($statement);

        $service = new FetchUserByResetToken(
            $pdo,
            $this->createMock(FetchUserById::class)
        );

        self::assertNull($service('FooToken'));
    }

    public function test(): void
    {
        $userModel = new UserModel();

        $fetchUserById = $this->createMock(
            FetchUserById::class
        );

        $fetchUserById->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo('FooBarUserId'))
            ->willReturn($userModel);

        $record = new UserPasswordResetTokenRecord();

        $record->user_id = 'FooBarUserId';

        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo(
                [':id' => 'FooToken']
            ))
            ->willReturn(true);

        $statement->expects(self::at(1))
            ->method('fetchObject')
            ->with(self::equalTo(
                UserPasswordResetTokenRecord::class
            ))
            ->willReturn($record);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT * FROM user_password_reset_tokens WHERE id=:id'
            ))
            ->willReturn($statement);

        $service = new FetchUserByResetToken(
            $pdo,
            $fetchUserById
        );

        self::assertSame(
            $userModel,
            $service('FooToken')
        );
    }
}
