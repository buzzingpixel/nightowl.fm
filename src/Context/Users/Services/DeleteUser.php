<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Payload\Payload;
use PDO;
use Throwable;

class DeleteUser
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(UserModel $user): Payload
    {
        try {
            $this->pdo->beginTransaction();

            $this->deleteUser($user);

            $this->deleteUserSessions($user);

            $this->deletePasswordResetTokens($user);

            $this->pdo->commit();

            return new Payload(Payload::STATUS_SUCCESSFUL);
        } catch (Throwable $e) {
            $this->pdo->rollBack();

            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => 'An unknown error occurred']
            );
        }
    }

    private function deleteUser(UserModel $user): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM users WHERE id=:id'
        );

        $statement->execute([':id' => $user->id]);
    }

    private function deleteUserSessions(UserModel $user): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM user_sessions WHERE user_id=:user_id'
        );

        $statement->execute([':user_id' => $user->id]);
    }

    private function deletePasswordResetTokens(UserModel $user): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM user_password_reset_tokens WHERE user_id=:user_id'
        );

        $statement->execute([':user_id' => $user->id]);
    }
}
