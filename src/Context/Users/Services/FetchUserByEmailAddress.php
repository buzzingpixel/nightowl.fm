<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Transformers\TransformUserRecordToUserModel;
use App\Persistence\Users\UserRecord;
use PDO;
use Throwable;

use function assert;
use function is_bool;

class FetchUserByEmailAddress
{
    private PDO $pdo;
    private TransformUserRecordToUserModel $transformUserRecordToUserModel;

    public function __construct(
        PDO $pdo,
        TransformUserRecordToUserModel $transformUserRecordToUserModel
    ) {
        $this->pdo                            = $pdo;
        $this->transformUserRecordToUserModel = $transformUserRecordToUserModel;
    }

    public function __invoke(string $emailAddress): ?UserModel
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT * FROM users WHERE email_address = :email'
            );

            $statement->execute([':email' => $emailAddress]);

            /** @psalm-suppress MixedAssignment */
            $userRecord = $statement->fetchObject(UserRecord::class);
            assert($userRecord instanceof UserRecord || is_bool($userRecord) || $userRecord === null);

            $isInstance = $userRecord instanceof UserRecord;

            if (
                $userRecord === null ||
                is_bool($userRecord) ||
                ! $isInstance
            ) {
                return null;
            }

            return ($this->transformUserRecordToUserModel)(
                $userRecord
            );
        } catch (Throwable $e) {
            return null;
        }
    }
}
