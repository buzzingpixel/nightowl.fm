<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Events\SaveUserAfterSave;
use App\Context\Users\Events\SaveUserBeforeSave;
use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\FetchUserByEmailAddress;
use App\Context\Users\Services\FetchUserById;
use App\Context\Users\Services\SaveUser;
use App\Context\Users\Transformers\TransformUserModelToUserRecord;
use App\Payload\Payload;
use App\Persistence\SaveExistingRecord;
use App\Persistence\SaveNewRecord;
use App\Persistence\Users\UserRecord;
use App\Persistence\UuidFactoryWithOrderedTimeCodec;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Safe\DateTimeImmutable;
use Tests\TestConfig;
use Throwable;

use function assert;
use function func_get_args;
use function password_verify;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class SaveUserTest extends TestCase
{
    private SaveUser $saveUser;

    private string $expectFetchUserEmailAddress      = '';
    private bool $fetchUserByEmailAddressReturnsUser = false;
    private bool $expectNewRecordCall                = false;
    private string $expectFetchUserId                = '';
    private bool $fetchUserByIdReturnsUser           = false;
    private bool $expectExistingRecordCall           = false;
    private bool $expectDispatcherCalls              = false;
    /** @var mixed[] */
    private array $dispatcherCalls = [];

    public function testWhenNoPasswordHashAndNoNewPassword(): void
    {
        $this->expectFetchUserEmailAddress        = '';
        $this->fetchUserByEmailAddressReturnsUser = false;
        $this->expectNewRecordCall                = false;
        $this->expectFetchUserId                  = '';
        $this->fetchUserByIdReturnsUser           = false;
        $this->expectExistingRecordCall           = false;

        $this->internalSetup();

        $userModel = new UserModel();

        $payload = ($this->saveUser)($userModel);

        self::assertSame(
            Payload::STATUS_NOT_VALID,
            $payload->getStatus()
        );

        self::assertSame(
            [
                'password' => 'Password is required',
                'emailAddress' => 'A valid email address is required',
            ],
            $payload->getResult()
        );
    }

    public function testWhenPasswordHashAndInvalidEmailAddress(): void
    {
        $this->expectFetchUserEmailAddress        = '';
        $this->fetchUserByEmailAddressReturnsUser = false;
        $this->expectNewRecordCall                = false;
        $this->expectFetchUserId                  = '';
        $this->fetchUserByIdReturnsUser           = false;
        $this->expectExistingRecordCall           = false;

        $this->internalSetup();

        $userModel               = new UserModel();
        $userModel->emailAddress = 'foobar';
        $userModel->passwordHash = 'TestPasswordHash';

        $payload = ($this->saveUser)($userModel);

        self::assertSame(
            Payload::STATUS_NOT_VALID,
            $payload->getStatus()
        );

        self::assertSame(
            ['emailAddress' => 'A valid email address is required'],
            $payload->getResult()
        );
    }

    public function testWhenNewPasswordIsTooShort(): void
    {
        $this->expectFetchUserEmailAddress        = '';
        $this->fetchUserByEmailAddressReturnsUser = false;
        $this->expectNewRecordCall                = false;
        $this->expectFetchUserId                  = '';
        $this->fetchUserByIdReturnsUser           = false;
        $this->expectExistingRecordCall           = false;

        $this->internalSetup();

        $userModel               = new UserModel();
        $userModel->emailAddress = 'foo@bar.baz';
        $userModel->newPassword  = 'foo';

        $payload = ($this->saveUser)($userModel);

        self::assertSame(
            Payload::STATUS_NOT_VALID,
            $payload->getStatus()
        );

        self::assertSame(
            ['password' => 'Password is too short'],
            $payload->getResult()
        );
    }

    public function testSaveNewUserWhenEmailExists(): void
    {
        $this->expectFetchUserEmailAddress        = 'foo@bar.baz';
        $this->fetchUserByEmailAddressReturnsUser = true;
        $this->expectNewRecordCall                = false;
        $this->expectFetchUserId                  = '';
        $this->fetchUserByIdReturnsUser           = false;
        $this->expectExistingRecordCall           = false;
        $this->expectDispatcherCalls              = true;

        $this->internalSetup();

        $userModel                    = new UserModel();
        $userModel->emailAddress      = 'foo@bar.baz';
        $userModel->newPassword       = 'FooBarBaz';
        $userModel->isActive          = false;
        $userModel->firstName         = 'TestFirstName';
        $userModel->lastName          = 'TestLastName';
        $userModel->displayName       = 'TestDisplayName';
        $userModel->billingName       = 'TestBillingName';
        $userModel->billingCompany    = 'TestBillingCompany';
        $userModel->billingPhone      = 'TestBillingPhone';
        $userModel->billingCountry    = 'TestBillingCountry';
        $userModel->billingAddress    = 'TestBillingAddress';
        $userModel->billingCity       = 'TestBillingCity';
        $userModel->billingPostalCode = 'TestBillingPostalCode';

        $payload = ($this->saveUser)($userModel);

        self::assertSame(
            Payload::STATUS_NOT_CREATED,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'User with email address foo@bar.baz already exists'],
            $payload->getResult()
        );

        self::assertCount(2, $this->dispatcherCalls);

        $call1 = $this->dispatcherCalls[0];

        self::assertCount(1, $call1);

        $beforeSave = $call1[0];

        assert($beforeSave instanceof SaveUserBeforeSave);

        self::assertSame($beforeSave->userModel, $userModel);

        $call2 = $this->dispatcherCalls[1];

        self::assertCount(1, $call2);

        $afterSave = $call2[0];

        assert($afterSave instanceof SaveUserAfterSave);

        self::assertSame($afterSave->userModel, $userModel);

        self::assertSame($payload, $afterSave->payload);
    }

    public function testSaveNewUser(): void
    {
        $this->expectFetchUserEmailAddress        = 'foo@bar.baz';
        $this->fetchUserByEmailAddressReturnsUser = false;
        $this->expectNewRecordCall                = true;
        $this->expectFetchUserId                  = '';
        $this->fetchUserByIdReturnsUser           = false;
        $this->expectExistingRecordCall           = false;
        $this->expectDispatcherCalls              = true;

        $this->internalSetup();

        $userModel                    = new UserModel();
        $userModel->emailAddress      = 'foo@bar.baz';
        $userModel->newPassword       = 'FooBarBaz';
        $userModel->isActive          = false;
        $userModel->firstName         = 'TestFirstName';
        $userModel->lastName          = 'TestLastName';
        $userModel->displayName       = 'TestDisplayName';
        $userModel->billingName       = 'TestBillingName';
        $userModel->billingCompany    = 'TestBillingCompany';
        $userModel->billingPhone      = 'TestBillingPhone';
        $userModel->billingCountry    = 'TestBillingCountry';
        $userModel->billingAddress    = 'TestBillingAddress';
        $userModel->billingCity       = 'TestBillingCity';
        $userModel->billingPostalCode = 'TestBillingPostalCode';

        $payload = ($this->saveUser)($userModel);

        self::assertSame(
            Payload::STATUS_CREATED,
            $payload->getStatus()
        );

        self::assertCount(1, $this->newRecordCallArgs);

        $userRecord = $this->newRecordCallArgs[0];
        assert($userRecord instanceof UserRecord || $userRecord === null);
        self::assertInstanceOf(UserRecord::class, $userRecord);

        self::assertSame('foo@bar.baz', $userRecord->email_address);

        self::assertSame('0', $userRecord->is_active);

        self::assertNotEmpty($userRecord->created_at);

        self::assertNotEmpty($userRecord->id);

        self::assertTrue(password_verify('FooBarBaz', $userRecord->password_hash));

        self::assertFalse(password_verify('BazBarFoo', $userRecord->password_hash));

        self::assertCount(2, $this->dispatcherCalls);

        $call1 = $this->dispatcherCalls[0];

        self::assertCount(1, $call1);

        $beforeSave = $call1[0];

        assert($beforeSave instanceof SaveUserBeforeSave);

        self::assertSame($beforeSave->userModel, $userModel);

        $call2 = $this->dispatcherCalls[1];

        self::assertCount(1, $call2);

        $afterSave = $call2[0];

        assert($afterSave instanceof SaveUserAfterSave);

        self::assertSame($afterSave->userModel, $userModel);

        self::assertSame($payload, $afterSave->payload);
    }

    /**
     * @throws Throwable
     */
    public function testSaveExistingUserWhenIdDoesNotExist(): void
    {
        $testId = TestConfig::$di->get(UuidFactoryWithOrderedTimeCodec::class)
            ->uuid1()
            ->toString();

        $this->expectFetchUserEmailAddress        = '';
        $this->fetchUserByEmailAddressReturnsUser = false;
        $this->expectNewRecordCall                = false;
        $this->expectFetchUserId                  = $testId;
        $this->fetchUserByIdReturnsUser           = false;
        $this->expectExistingRecordCall           = false;
        $this->expectDispatcherCalls              = true;

        $this->internalSetup();

        $userModel               = new UserModel();
        $userModel->id           = $testId;
        $userModel->emailAddress = 'foo@bar.baz';
        $userModel->newPassword  = 'FooBarBaz';
        $userModel->isActive     = true;

        $payload = ($this->saveUser)($userModel);

        self::assertSame(
            Payload::STATUS_NOT_FOUND,
            $payload->getStatus()
        );

        self::assertSame(
            ['message' => 'User with id ' . $testId . ' not found'],
            $payload->getResult()
        );

        self::assertCount(2, $this->dispatcherCalls);

        $call1 = $this->dispatcherCalls[0];

        self::assertCount(1, $call1);

        $beforeSave = $call1[0];

        assert($beforeSave instanceof SaveUserBeforeSave);

        self::assertSame($beforeSave->userModel, $userModel);

        $call2 = $this->dispatcherCalls[1];

        self::assertCount(1, $call2);

        $afterSave = $call2[0];

        assert($afterSave instanceof SaveUserAfterSave);

        self::assertSame($afterSave->userModel, $userModel);

        self::assertSame($payload, $afterSave->payload);
    }

    /**
     * @throws Throwable
     */
    public function testSaveExistingUser(): void
    {
        $testId = TestConfig::$di->get(UuidFactoryWithOrderedTimeCodec::class)->uuid1()->toString();

        $this->expectFetchUserEmailAddress        = '';
        $this->fetchUserByEmailAddressReturnsUser = false;
        $this->expectNewRecordCall                = false;
        $this->expectFetchUserId                  = $testId;
        $this->fetchUserByIdReturnsUser           = true;
        $this->expectExistingRecordCall           = true;
        $this->expectDispatcherCalls              = true;

        $this->internalSetup();

        $createdAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $userModel               = new UserModel();
        $userModel->id           = $testId;
        $userModel->emailAddress = 'foo@bar.baz';
        $userModel->passwordHash = 'ExistingFooBarPassHash';
        $userModel->isActive     = true;
        $userModel->createdAt    = $createdAt;

        $payload = ($this->saveUser)($userModel);

        self::assertSame(
            Payload::STATUS_UPDATED,
            $payload->getStatus()
        );

        self::assertCount(1, $this->existingRecordCallArgs);

        $userRecord = $this->existingRecordCallArgs[0];
        assert($userRecord instanceof UserRecord || $userRecord === null);
        self::assertInstanceOf(UserRecord::class, $userRecord);

        self::assertSame('foo@bar.baz', $userRecord->email_address);

        self::assertSame('1', $userRecord->is_active);

        self::assertSame($testId, $userRecord->id);

        self::assertSame('ExistingFooBarPassHash', $userRecord->password_hash);

        self::assertSame($createdAt->format(DateTimeInterface::ATOM), $userRecord->created_at);

        self::assertCount(2, $this->dispatcherCalls);

        $call1 = $this->dispatcherCalls[0];

        self::assertCount(1, $call1);

        $beforeSave = $call1[0];

        assert($beforeSave instanceof SaveUserBeforeSave);

        self::assertSame($beforeSave->userModel, $userModel);

        $call2 = $this->dispatcherCalls[1];

        self::assertCount(1, $call2);

        $afterSave = $call2[0];

        assert($afterSave instanceof SaveUserAfterSave);

        self::assertSame($afterSave->userModel, $userModel);

        self::assertSame($payload, $afterSave->payload);
    }

    private function internalSetup(): void
    {
        $this->saveUser = new SaveUser(
            $this->mockFetchUserByEmailAddress(),
            $this->mockSaveNewRecord(),
            $this->mockFetchUserById(),
            $this->mockSaveExistingRecord(),
            TestConfig::$di->get(
                TransformUserModelToUserRecord::class
            ),
            TestConfig::$di->get(
                UuidFactoryWithOrderedTimeCodec::class
            ),
            $this->mockEventDispatcher()
        );
    }

    /**
     * @return FetchUserByEmailAddress&MockObject
     */
    private function mockFetchUserByEmailAddress(): FetchUserByEmailAddress
    {
        $mock = $this->createMock(
            FetchUserByEmailAddress::class
        );

        if ($this->expectFetchUserEmailAddress === '') {
            $mock->expects(self::never())
                ->method(self::anything());

            return $mock;
        }

        $mock->expects(self::once())
            ->method('__invoke')
            ->with(
                self::equalTo($this->expectFetchUserEmailAddress)
            )
            ->willReturn(
                $this->fetchUserByEmailAddressReturnsUser ?
                    new UserModel() :
                    null
            );

        return $mock;
    }

    /** @var mixed[] */
    private array $newRecordCallArgs = [];

    /**
     * @return SaveNewRecord&MockObject
     */
    private function mockSaveNewRecord(): SaveNewRecord
    {
        $this->newRecordCallArgs = [];

        $mock = $this->createMock(
            SaveNewRecord::class
        );

        if (! $this->expectNewRecordCall) {
            $mock->expects(self::never())
                ->method(self::anything());

            return $mock;
        }

        $mock->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(function (): Payload {
                $this->newRecordCallArgs = func_get_args();

                return new Payload(Payload::STATUS_CREATED);
            });

        return $mock;
    }

    /**
     * @return FetchUserById&MockObject
     */
    private function mockFetchUserById(): FetchUserById
    {
        $mock = $this->createMock(
            FetchUserById::class
        );

        if ($this->expectFetchUserId === '') {
            $mock->expects(self::never())
                ->method(self::anything());

            return $mock;
        }

        $mock->expects(self::once())
            ->method('__invoke')
            ->with(
                self::equalTo($this->expectFetchUserId)
            )
            ->willReturn(
                $this->fetchUserByIdReturnsUser ?
                    new UserModel() :
                    null
            );

        return $mock;
    }

    /** @var mixed[] */
    private array $existingRecordCallArgs = [];

    /**
     * @return SaveExistingRecord&MockObject
     */
    private function mockSaveExistingRecord(): SaveExistingRecord
    {
        $this->existingRecordCallArgs = [];

        $mock = $this->createMock(
            SaveExistingRecord::class
        );

        if (! $this->expectExistingRecordCall) {
            $mock->expects(self::never())
                ->method(self::anything());

            return $mock;
        }

        $mock->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(function (): Payload {
                $this->existingRecordCallArgs = func_get_args();

                return new Payload(Payload::STATUS_UPDATED);
            });

        return $mock;
    }

    /**
     * @return EventDispatcherInterface&MockObject
     */
    private function mockEventDispatcher(): EventDispatcherInterface
    {
        $mock = $this->createMock(
            EventDispatcherInterface::class
        );

        if (! $this->expectDispatcherCalls) {
            $mock->expects(self::never())
                ->method(self::anything());

            return $mock;
        }

        $mock->method('dispatch')
            ->willReturnCallback(function (): void {
                $this->dispatcherCalls[] = func_get_args();
            });

        return $mock;
    }
}
