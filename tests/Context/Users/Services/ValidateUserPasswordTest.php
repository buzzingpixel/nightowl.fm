<?php

declare(strict_types=1);

namespace Tests\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Services\SaveUser;
use App\Context\Users\Services\ValidateUserPassword;
use App\Payload\Payload;
use PHPUnit\Framework\TestCase;

use function password_hash;

use const PASSWORD_ARGON2I;
use const PASSWORD_DEFAULT;

class ValidateUserPasswordTest extends TestCase
{
    public function testWhenPasswordInvalid(): void
    {
        $password = 'FooPassword';

        $user = new UserModel();

        $user->passwordHash = 'FooHash';

        $saveUser = $this->createMock(SaveUser::class);

        $saveUser->expects(self::never())
            ->method(self::anything());

        $service = new ValidateUserPassword($saveUser);

        self::assertFalse(
            $service($user, $password),
        );
    }

    public function testWhenPasswordNeedsRehashButRequestNoRehash(): void
    {
        $password = 'FooBarPassword';

        $passwordHash = (string) password_hash(
            $password,
            PASSWORD_ARGON2I
        );

        $user = new UserModel();

        $user->passwordHash = $passwordHash;

        $saveUser = $this->createMock(SaveUser::class);

        $saveUser->expects(self::never())
            ->method(self::anything());

        $service = new ValidateUserPassword($saveUser);

        self::assertTrue(
            $service(
                $user,
                $password,
                false
            ),
        );
    }

    public function testWhenPasswordDoesNotNeedRehash(): void
    {
        $password = 'FooBarPassword';

        $passwordHash = (string) password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $user = new UserModel();

        $user->passwordHash = $passwordHash;

        $saveUser = $this->createMock(SaveUser::class);

        $saveUser->expects(self::never())
            ->method(self::anything());

        $service = new ValidateUserPassword($saveUser);

        self::assertTrue(
            $service(
                $user,
                $password,
            ),
        );
    }

    public function testWhenPasswordNeedsRehash(): void
    {
        $password = 'FooBarPassword';

        $passwordHash = (string) password_hash(
            $password,
            PASSWORD_ARGON2I
        );

        $user = new UserModel();

        $user->passwordHash = $passwordHash;

        $saveUser = $this->createMock(SaveUser::class);

        $saveUser->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (UserModel $model) use (
                    $passwordHash
                ): Payload {
                    self::assertNotSame(
                        $passwordHash,
                        $model->passwordHash,
                    );

                    return new Payload(Payload::STATUS_UPDATED);
                }
            );

        $service = new ValidateUserPassword($saveUser);

        self::assertTrue(
            $service(
                $user,
                $password
            ),
        );

        self::assertNotSame(
            $passwordHash,
            $user->passwordHash,
        );
    }
}
