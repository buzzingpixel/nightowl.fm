<?php

declare(strict_types=1);

namespace Tests\Persistence;

use App\Persistence\DatabaseTransactionManager;
use Exception;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

class DatabaseTransactionManagerTest extends TestCase
{
    public function testTransactionManager(): void
    {
        $inTransaction                = new stdClass();
        $inTransaction->inTransaction = false;

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('beginTransaction')
            ->willReturnCallback(
                static fn () => $inTransaction->inTransaction = true
            );

        $pdo->method('inTransaction')
            ->willReturnCallback(static fn () => $inTransaction->inTransaction);

        $pdo->expects(self::once())
            ->method('commit')
            ->willReturn(true);

        $manager = new DatabaseTransactionManager($pdo);

        self::assertTrue($manager->beginTransaction());

        self::assertTrue(
            (new DatabaseTransactionManagerAdditional())
                ->beginTransaction($manager)
        );

        self::assertFalse(
            (new DatabaseTransactionManagerAdditional())
                ->commit($manager)
        );

        self::assertTrue($manager->commit());
    }

    public function testTransactionManagerWhenBeginThrows(): void
    {
        $pdo = $this->createMock(PDO::class);

        $pdo->method('beginTransaction')
            ->willThrowException(new Exception());

        $manager = new DatabaseTransactionManager($pdo);

        self::assertFalse($manager->beginTransaction());
    }

    public function testRollBack(): void
    {
        $inTransaction                = new stdClass();
        $inTransaction->inTransaction = false;

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('beginTransaction')
            ->willReturnCallback(
                static fn () => $inTransaction->inTransaction = true
            );

        $pdo->method('inTransaction')
            ->willReturnCallback(static fn () => $inTransaction->inTransaction);

        $pdo->expects(self::never())
            ->method('commit');

        $manager = new DatabaseTransactionManager($pdo);

        self::assertTrue($manager->beginTransaction());

        self::assertTrue(
            (new DatabaseTransactionManagerAdditional())
                ->beginTransaction($manager)
        );

        self::assertFalse(
            (new DatabaseTransactionManagerAdditional())
                ->rollBack($manager)
        );

        $exception = null;

        try {
            $manager->commit();
        } catch (Throwable $e) {
            $exception = $e;
        }

        self::assertInstanceOf(PDOException::class, $exception);
    }

    public function testParentRollBack(): void
    {
        $inTransaction                = new stdClass();
        $inTransaction->inTransaction = false;

        $pdo = $this->createMock(PDO::class);

        $pdo->expects(self::once())
            ->method('rollBack')
            ->willReturn(true);

        $pdo->expects(self::once())
            ->method('beginTransaction')
            ->willReturnCallback(
                static fn () => $inTransaction->inTransaction = true
            );

        $pdo->method('inTransaction')
            ->willReturnCallback(static fn () => $inTransaction->inTransaction);

        $pdo->expects(self::never())
            ->method('commit');

        $manager = new DatabaseTransactionManager($pdo);

        self::assertTrue($manager->beginTransaction());

        self::assertTrue(
            (new DatabaseTransactionManagerAdditional())
                ->beginTransaction($manager)
        );

        self::assertFalse(
            (new DatabaseTransactionManagerAdditional())
                ->commit($manager)
        );

        self::assertTrue($manager->rollBack());
    }
}
