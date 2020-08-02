<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\FetchUserById;
use App\Context\Users\Transformers\TransformUserRecordToUserModel;
use App\Persistence\Users\UserRecord;
use DateTimeInterface;
use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\TestConfig;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchUserByIdTest extends TestCase
{
    public function testWhenPdoThrows(): void
    {
        $pdo = $this->createMock(PDO::class);

        $pdo->method('prepare')->willThrowException(new Exception());

        $fetch = new FetchUserById(
            $pdo,
            TestConfig::$di->get(
                TransformUserRecordToUserModel::class
            )
        );

        self::assertNull($fetch('foobar'));
    }

    public function testWhenFetchObjectReturnsNull(): void
    {
        $fetch = new FetchUserById(
            $this->mockPdo(
                $this->mockPdoStatement(
                    'FooBarIdTest'
                )
            ),
            TestConfig::$di->get(
                TransformUserRecordToUserModel::class
            )
        );

        self::assertNull($fetch('FooBarIdTest'));
    }

    public function testWhenFetchReturnUserRecord(): void
    {
        $fetch = new FetchUserById(
            $this->mockPdo(
                $this->mockPdoStatement(
                    'BazFooId',
                    $this->createUserRecord()
                )
            ),
            TestConfig::$di->get(
                TransformUserRecordToUserModel::class
            )
        );

        $userModel = $fetch('BazFooId');

        self::assertInstanceOf(UserModel::class, $userModel);

        self::assertSame('TestId', $userModel->id);

        self::assertSame('TestEmailAddress', $userModel->emailAddress);

        self::assertSame('TestPasswordHash', $userModel->passwordHash);

        self::assertSame('', $userModel->newPassword);

        self::assertTrue($userModel->isActive);

        self::assertSame('2019-11-25T04:11:51+00:00', $userModel->createdAt->format(DateTimeInterface::ATOM));
    }

    private function createUserRecord(): UserRecord
    {
        $userRecord = new UserRecord();

        $userRecord->id = 'TestId';

        $userRecord->email_address = 'TestEmailAddress';

        $userRecord->password_hash = 'TestPasswordHash';

        $userRecord->is_active = '1';

        $userRecord->created_at = '2019-11-25 04:11:51+00';

        $userRecord->timezone = 'America/New_York';

        return $userRecord;
    }

    /**
     * @return PDOStatement&MockObject
     */
    private function mockPdoStatement(
        string $expectId,
        ?UserRecord $userRecord = null
    ): PDOStatement {
        $mock = $this->createMock(PDOStatement::class);

        $mock->expects(self::at(0))
            ->method('execute')
            ->with(self::equalTo([':id' => $expectId]))
            ->willReturn(true);

        $mock->expects(self::at(1))
            ->method('fetchObject')
            ->with(self::equalTo(UserRecord::class))
            ->willReturn($userRecord);

        return $mock;
    }

    /**
     * @return PDO&MockObject
     */
    private function mockPdo(PDOStatement $statement): PDO
    {
        $mock = $this->createMock(PDO::class);

        $mock->expects(self::once())
            ->method('prepare')
            ->with(
                self::equalTo(
                    'SELECT * FROM users WHERE id = :id'
                )
            )
            ->willReturn($statement);

        return $mock;
    }
}
