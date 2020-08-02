<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Persistence\Users\UserPasswordResetTokenRecord;
use PDO;
use Throwable;

use function assert;
use function is_bool;

// phpcs:disable Squiz.NamingConventions.ValidVariableName.NotCamelCaps

class FetchUserByResetToken
{
    private PDO $pdo;
    private FetchUserById $fetchUserById;

    public function __construct(PDO $pdo, FetchUserById $fetchUserById)
    {
        $this->pdo           = $pdo;
        $this->fetchUserById = $fetchUserById;
    }

    public function __invoke(string $token): ?UserModel
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT * FROM user_password_reset_tokens WHERE id=:id'
            );

            $statement->execute([':id' => $token]);

            /** @psalm-suppress MixedAssignment */
            $record = $statement->fetchObject(
                UserPasswordResetTokenRecord::class
            );
            assert($record instanceof UserPasswordResetTokenRecord || is_bool($record));

            if (is_bool($record)) {
                return null;
            }

            return ($this->fetchUserById)($record->user_id);
        } catch (Throwable $e) {
            return null;
        }
    }
}
