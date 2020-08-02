<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\DeleteUser;
use App\Payload\Payload;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteUserTest extends TestCase
{
    private DeleteUser $service;

    /** @var PDO&MockObject */
    private $pdo;
    private UserModel $user;

    public function testWhenPdoThrowsException(): void
    {
        $this->pdo->expects(self::at(0))
            ->method('beginTransaction')
            ->willReturn(true);

        $this->pdo->expects(self::at(1))
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM users WHERE id=:id'
            ))
            ->willThrowException(new Exception());

        $this->pdo->expects(self::at(2))
            ->method('rollBack');

        $payload = ($this->service)($this->user);

        self::assertSame(
            Payload::STATUS_ERROR,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'An unknown error occurred'],
            $payload->getResult()
        );
    }

    public function test(): void
    {
        $this->pdo->expects(self::at(0))
            ->method('beginTransaction');

        /**
         * Delete user
         */

        $statementForDeleteUser = $this->createMock(
            PDOStatement::class
        );

        $statementForDeleteUser->expects(self::once())
            ->method('execute')
            ->with(self::equalTo(
                [':id' => 'TestId']
            ))
            ->willReturn(true);

        $this->pdo->expects(self::at(1))
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM users WHERE id=:id'
            ))
            ->willReturn($statementForDeleteUser);

        /**
         * Delete session
         */

        $statementForDeleteSession = $this->createMock(
            PDOStatement::class
        );

        $statementForDeleteSession->expects(self::once())
            ->method('execute')
            ->with(self::equalTo(
                [':user_id' => 'TestId']
            ))
            ->willReturn(true);

        $this->pdo->expects(self::at(2))
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM user_sessions WHERE user_id=:user_id'
            ))
            ->willReturn($statementForDeleteSession);

        /**
         * Delete tokens
         */

        $statementForDeleteTokens = $this->createMock(
            PDOStatement::class
        );

        $statementForDeleteTokens->expects(self::once())
            ->method('execute')
            ->with(self::equalTo(
                [':user_id' => 'TestId']
            ))
            ->willReturn(true);

        $this->pdo->expects(self::at(3))
            ->method('prepare')
            ->with(self::equalTo(
                'DELETE FROM user_password_reset_tokens WHERE user_id=:user_id'
            ))
            ->willReturn($statementForDeleteTokens);

        /**
         * Commit
         */

        $this->pdo->expects(self::at(4))
            ->method('commit')
            ->willReturn(true);

        $payload = ($this->service)($this->user);

        self::assertSame(
            Payload::STATUS_SUCCESSFUL,
            $payload->getStatus()
        );
    }

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);

        $this->user     = new UserModel();
        $this->user->id = 'TestId';

        $this->service = new DeleteUser($this->pdo);
    }
}
