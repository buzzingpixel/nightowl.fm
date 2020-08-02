<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\FetchTotalUserResetTokens;
use App\Persistence\Users\UserPasswordResetTokenRecord;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class FetchTotalUserResetTokensTest extends TestCase
{
    public function testWhenNotArray(): void
    {
        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo(['id' => 'testUserId']));

        $statement->expects(self::at(1))
            ->method('fetch')
            ->willReturn(null);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT COUNT(*) FROM ' .
                (new UserPasswordResetTokenRecord())->getTableName() .
                ' WHERE user_id = :id'
            ))
            ->willReturn($statement);

        $service = new FetchTotalUserResetTokens($pdo);

        $user     = new UserModel();
        $user->id = 'testUserId';

        self::assertSame(0, $service($user));
    }

    public function testWhenNoCountKeyInFetchedArray(): void
    {
        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo(['id' => 'testUserId']));

        $statement->expects(self::at(1))
            ->method('fetch')
            ->willReturn(['foo' => 'bar']);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT COUNT(*) FROM ' .
                (new UserPasswordResetTokenRecord())->getTableName() .
                ' WHERE user_id = :id'
            ))
            ->willReturn($statement);

        $service = new FetchTotalUserResetTokens($pdo);

        $user     = new UserModel();
        $user->id = 'testUserId';

        self::assertSame(0, $service($user));
    }

    public function test(): void
    {
        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo(['id' => 'testUserId']));

        $statement->expects(self::at(1))
            ->method('fetch')
            ->willReturn(['count' => '182']);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT COUNT(*) FROM ' .
                (new UserPasswordResetTokenRecord())->getTableName() .
                ' WHERE user_id = :id'
            ))
            ->willReturn($statement);

        $service = new FetchTotalUserResetTokens($pdo);

        $user     = new UserModel();
        $user->id = 'testUserId';

        self::assertSame(182, $service($user));
    }
}
