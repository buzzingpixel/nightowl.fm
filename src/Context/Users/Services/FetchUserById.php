<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Context\Users\Transformers\TransformUserRecordToUserModel;
use App\Persistence\Users\UserRecord;
use PDO;
use Throwable;

use function assert;

class FetchUserById
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

    public function __invoke(string $id): ?UserModel
    {
        try {
            $query = $this->pdo->prepare(
                'SELECT * FROM users WHERE id = :id'
            );

            $query->execute([':id' => $id]);

            /** @psalm-suppress MixedAssignment */
            $userRecord = $query->fetchObject(UserRecord::class);
            assert($userRecord instanceof UserRecord || $userRecord === null);

            $isInstance = $userRecord instanceof UserRecord;

            if ($userRecord === null || ! $isInstance) {
                return null;
            }

            return ($this->transformUserRecordToUserModel)($userRecord);
        } catch (Throwable $e) {
            return null;
        }
    }
}
