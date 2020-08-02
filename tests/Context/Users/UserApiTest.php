<?php

declare(strict_types=1);

namespace Tests\Context\Users;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\DeleteUser;
use App\Context\Users\Services\FetchLoggedInUser;
use App\Context\Users\Services\FetchTotalUserResetTokens;
use App\Context\Users\Services\FetchTotalUsers;
use App\Context\Users\Services\FetchUserByEmailAddress;
use App\Context\Users\Services\FetchUserById;
use App\Context\Users\Services\FetchUserByResetToken;
use App\Context\Users\Services\FetchUsersByLimitOffset;
use App\Context\Users\Services\GeneratePasswordResetToken;
use App\Context\Users\Services\LogCurrentUserOut;
use App\Context\Users\Services\LogUserIn;
use App\Context\Users\Services\RequestPasswordResetEmail;
use App\Context\Users\Services\ResetPasswordByToken;
use App\Context\Users\Services\SaveUser;
use App\Context\Users\Services\ValidateUserPassword;
use App\Context\Users\UserApi;
use App\Payload\Payload;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Throwable;

use function func_get_args;

class UserApiTest extends TestCase
{
    private UserApi $userApi;

    private Payload $payload;
    private UserModel $userModel;

    /** @var mixed[] */
    private array $callArgs = [];

    public function testSaveUser(): void
    {
        $payload = $this->userApi->saveUser($this->userModel);

        self::assertSame($this->payload, $payload);

        self::assertCount(1, $this->callArgs);

        self::assertSame(
            $this->userModel,
            $this->callArgs[0]
        );
    }

    public function testFetchUserByEmailAddress(): void
    {
        $userModel = $this->userApi->fetchUserByEmailAddress(
            'foo@bar.baz'
        );

        self::assertSame($this->userModel, $userModel);

        self::assertCount(1, $this->callArgs);

        self::assertSame(
            'foo@bar.baz',
            $this->callArgs[0]
        );
    }

    public function testFetchUserById(): void
    {
        $userModel = $this->userApi->fetchUserById('testId');

        self::assertSame($this->userModel, $userModel);

        self::assertCount(1, $this->callArgs);

        self::assertSame(
            'testId',
            $this->callArgs[0]
        );
    }

    public function testValidateUserPassword(): void
    {
        $user = new UserModel();

        $password = 'FooBarPass';

        $service = $this->createMock(
            ValidateUserPassword::class
        );

        $service->expects(self::once())
            ->method('__invoke')
            ->with(
                self::equalTo($user),
                self::equalTo($password),
                self::equalTo(false),
            )
            ->willReturn(true);

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(
                self::equalTo(ValidateUserPassword::class)
            )
            ->willReturn($service);

        $api = new UserApi($di);

        self::assertTrue($api->validateUserPassword(
            $user,
            $password,
            false
        ));
    }

    public function testLogUserIn(): void
    {
        $payload = $this->userApi->logUserIn(
            $this->userModel,
            'FooBarPassword'
        );

        self::assertSame($payload, $this->payload);

        self::assertCount(2, $this->callArgs);

        self::assertSame($this->userModel, $this->callArgs[0]);

        self::assertSame('FooBarPassword', $this->callArgs[1]);
    }

    public function testDeleteUser(): void
    {
        $payload = $this->userApi->deleteUser(
            $this->userModel
        );

        self::assertSame($payload, $this->payload);

        self::assertCount(1, $this->callArgs);

        self::assertSame($this->userModel, $this->callArgs[0]);
    }

    public function testFetchLoggedInUser(): void
    {
        $userModel = $this->userApi->fetchLoggedInUser();

        self::assertSame($userModel, $this->userModel);
    }

    public function testGeneratePasswordResetToken(): void
    {
        $payload = $this->userApi->generatePasswordResetToken(
            $this->userModel
        );

        self::assertSame($payload, $this->payload);

        self::assertCount(1, $this->callArgs);

        self::assertSame($this->userModel, $this->callArgs[0]);
    }

    /**
     * @throws Throwable
     */
    public function testRequestPasswordResetEmail(): void
    {
        $this->userApi->requestPasswordResetEmail(
            $this->userModel
        );
    }

    /**
     * @throws Throwable
     */
    public function testFetchTotalUserResetTokens(): void
    {
        self::assertSame(
            42,
            $this->userApi->fetchTotalUserResetTokens(
                $this->userModel
            )
        );
    }

    public function testFetchUserByResetToken(): void
    {
        $userModel = $this->userApi->fetchUserByResetToken(
            'FooToken'
        );

        self::assertSame($userModel, $this->userModel);

        self::assertCount(1, $this->callArgs);

        self::assertSame('FooToken', $this->callArgs[0]);
    }

    public function testCurrentUserOut(): void
    {
        $payload = $this->userApi->logCurrentUserOut();

        self::assertSame($payload, $this->payload);
    }

    public function testResetPasswordByToken(): void
    {
        $payload = $this->userApi->resetPasswordByToken(
            'FooToken',
            'FooPass'
        );

        self::assertSame($payload, $this->payload);

        self::assertCount(2, $this->callArgs);

        self::assertSame('FooToken', $this->callArgs[0]);

        self::assertSame('FooPass', $this->callArgs[1]);
    }

    public function testFetchTotalUsers(): void
    {
        self::assertSame(23, $this->userApi->fetchTotalUsers());
    }

    public function testFetchUsersByLimitOffset(): void
    {
        self::assertSame(
            [$this->userModel],
            $this->userApi->fetchUsersByLimitOffset(42, 24)
        );
    }

    protected function setUp(): void
    {
        $this->callArgs = [];

        $this->payload = new Payload(Payload::STATUS_SUCCESSFUL);

        $this->userModel = new UserModel();

        $this->userApi = new UserApi($this->mockDi());
    }

    /**
     * @return MockObject&ContainerInterface
     */
    private function mockDi(): ContainerInterface
    {
        $mock = $this->createMock(ContainerInterface::class);

        $mock->method('get')->willReturnCallback(
            [$this, 'containerGet']
        );

        return $mock;
    }

    /**
     * @return mixed
     *
     * @throws Throwable
     */
    public function containerGet(string $class)
    {
        switch ($class) {
            case SaveUser::class:
                return $this->mockSaveUser();

            case FetchUserByEmailAddress::class:
                return $this->mockFetchUserByEmailAddress();

            case FetchUserById::class:
                return $this->mockFetchUserById();

            case LogUserIn::class:
                return $this->mockLogUserIn();

            case DeleteUser::class:
                return $this->mockDeleteUser();

            case FetchLoggedInUser::class:
                return $this->mockFetchLoggedInUser();

            case GeneratePasswordResetToken::class:
                return $this->mockGeneratePasswordResetToken();

            case FetchUserByResetToken::class:
                return $this->mockFetchUserByResetToken();

            case LogCurrentUserOut::class:
                return $this->mockLogCurrentUserOut();

            case ResetPasswordByToken::class:
                return $this->mockResetPasswordByToken();

            case FetchTotalUsers::class:
                return $this->mockFetchTotalUsers();

            case FetchUsersByLimitOffset::class:
                return $this->mockFetchUsersByLimitOffset();

            case RequestPasswordResetEmail::class:
                return $this->mockRequestPasswordResetEmail();

            case FetchTotalUserResetTokens::class:
                return $this->mockFetchTotalUserResetTokens();
        }

        throw new Exception('Unknown class');
    }

    /**
     * @return SaveUser&MockObject
     */
    private function mockSaveUser(): SaveUser
    {
        $mock = $this->createMock(SaveUser::class);

        $mock->method('__invoke')
            ->willReturnCallback(function (): Payload {
                $this->callArgs = func_get_args();

                return $this->payload;
            });

        return $mock;
    }

    /**
     * @return FetchUserByEmailAddress&MockObject
     */
    private function mockFetchUserByEmailAddress(): FetchUserByEmailAddress
    {
        $mock = $this->createMock(
            FetchUserByEmailAddress::class
        );

        $mock->method('__invoke')
            ->willReturnCallback(function (): UserModel {
                $this->callArgs = func_get_args();

                return $this->userModel;
            });

        return $mock;
    }

    /**
     * @return FetchUserById&MockObject
     */
    private function mockFetchUserById(): FetchUserById
    {
        $mock = $this->createMock(FetchUserById::class);

        $mock->method('__invoke')
            ->willReturnCallback(function (): UserModel {
                $this->callArgs = func_get_args();

                return $this->userModel;
            });

        return $mock;
    }

    /**
     * @return LogUserIn&MockObject
     */
    private function mockLogUserIn(): LogUserIn
    {
        $mock = $this->createMock(LogUserIn::class);

        $mock->method('__invoke')
            ->willReturnCallback(function (): Payload {
                $this->callArgs = func_get_args();

                return $this->payload;
            });

        return $mock;
    }

    /**
     * @return DeleteUser&MockObject
     */
    private function mockDeleteUser(): DeleteUser
    {
        $mock = $this->createMock(DeleteUser::class);

        $mock->method('__invoke')
            ->willReturnCallback(function (): Payload {
                $this->callArgs = func_get_args();

                return $this->payload;
            });

        return $mock;
    }

    /**
     * @return FetchLoggedInUser&MockObject
     */
    private function mockFetchLoggedInUser(): FetchLoggedInUser
    {
        $mock = $this->createMock(FetchLoggedInUser::class);

        $mock->method('__invoke')
            ->willReturnCallback(function (): UserModel {
                return $this->userModel;
            });

        return $mock;
    }

    /**
     * @return GeneratePasswordResetToken&MockObject
     */
    private function mockGeneratePasswordResetToken(): GeneratePasswordResetToken
    {
        $mock = $this->createMock(
            GeneratePasswordResetToken::class
        );

        $mock->method('__invoke')
            ->willReturnCallback(function (): Payload {
                $this->callArgs = func_get_args();

                return $this->payload;
            });

        return $mock;
    }

    /**
     * @return FetchUserByResetToken&MockObject
     */
    private function mockFetchUserByResetToken(): FetchUserByResetToken
    {
        $mock = $this->createMock(FetchUserByResetToken::class);

        $mock->method('__invoke')
            ->willReturnCallback(function (): UserModel {
                $this->callArgs = func_get_args();

                return $this->userModel;
            });

        return $mock;
    }

    /**
     * @return LogCurrentUserOut&MockObject
     */
    private function mockLogCurrentUserOut(): LogCurrentUserOut
    {
        $mock = $this->createMock(
            LogCurrentUserOut::class
        );

        $mock->method('__invoke')
            ->willReturnCallback(function (): Payload {
                return $this->payload;
            });

        return $mock;
    }

    /**
     * @return ResetPasswordByToken&MockObject
     */
    private function mockResetPasswordByToken(): ResetPasswordByToken
    {
        $mock = $this->createMock(ResetPasswordByToken::class);

        $mock->method('__invoke')
            ->willReturnCallback(function (): Payload {
                $this->callArgs = func_get_args();

                return $this->payload;
            });

        return $mock;
    }

    /**
     * @return FetchTotalUsers&MockObject
     */
    private function mockFetchTotalUsers(): FetchTotalUsers
    {
        $mock = $this->createMock(FetchTotalUsers::class);

        $mock->method('__invoke')
            ->willReturn(23);

        return $mock;
    }

    /**
     * @return FetchUsersByLimitOffset&MockObject
     */
    private function mockFetchUsersByLimitOffset(): FetchUsersByLimitOffset
    {
        $mock = $this->createMock(
            FetchUsersByLimitOffset::class
        );

        $mock->method('__invoke')
            ->with(
                self::equalTo(42),
                self::equalTo(24)
            )
            ->willReturn([$this->userModel]);

        return $mock;
    }

    /**
     * @return RequestPasswordResetEmail&MockObject
     */
    private function mockRequestPasswordResetEmail(): RequestPasswordResetEmail
    {
        $mock = $this->createMock(
            RequestPasswordResetEmail::class
        );

        $mock->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($this->userModel));

        return $mock;
    }

    /**
     * @return FetchTotalUserResetTokens&MockObject
     */
    private function mockFetchTotalUserResetTokens(): FetchTotalUserResetTokens
    {
        $mock = $this->createMock(
            FetchTotalUserResetTokens::class
        );

        $mock->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($this->userModel))
            ->willReturn(42);

        return $mock;
    }
}
