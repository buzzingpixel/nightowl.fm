<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Services\FetchTotalUsers;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class FetchTotalUsersTest extends TestCase
{
    public function testWhenExceptionThrown(): void
    {
        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT COUNT(*) from users'
            ))
            ->willThrowException(new Exception());

        $service = new FetchTotalUsers($pdo);

        self::assertSame(0, $service());
    }

    public function test(): void
    {
        $statement = $this->createMock(PDOStatement::class);

        $statement->expects(self::at(0))
            ->method('execute')
            ->willReturn(true);

        $statement->expects(self::at(1))
            ->method('fetchColumn')
            ->willReturn(23);

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('prepare')
            ->with(self::equalTo(
                'SELECT COUNT(*) from users'
            ))
            ->willReturn($statement);

        $service = new FetchTotalUsers($pdo);

        self::assertSame(23, $service());
    }
}
