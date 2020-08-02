<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;

use function password_needs_rehash;
use function password_verify;
use function Safe\password_hash;

use const PASSWORD_DEFAULT;

class ValidateUserPassword
{
    private SaveUser $saveUser;

    public function __construct(
        SaveUser $saveUser
    ) {
        $this->saveUser = $saveUser;
    }

    /**
     * @param bool $rehashPasswordIfNeeded Only set false if about to update password
     */
    public function __invoke(
        UserModel $user,
        string $password,
        bool $rehashPasswordIfNeeded = true
    ): bool {
        $hash = $user->passwordHash;

        if (! password_verify($password, $hash)) {
            return false;
        }

        if (! $rehashPasswordIfNeeded) {
            return true;
        }

        if (! password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            return true;
        }

        /**
         * @psalm-suppress NullArgument
         * @phpstan-ignore-next-line
         */
        $user->passwordHash = (string) password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        ($this->saveUser)($user);

        return true;
    }
}
