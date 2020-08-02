<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Payload\Payload;
use PDO;
use Throwable;

class ResetPasswordByToken
{
    private FetchUserByResetToken $fetchUserByResetToken;
    private SaveUser $saveUser;
    private PDO $pdo;

    public function __construct(
        FetchUserByResetToken $fetchUserByResetToken,
        SaveUser $saveUser,
        PDO $pdo
    ) {
        $this->fetchUserByResetToken = $fetchUserByResetToken;
        $this->saveUser              = $saveUser;
        $this->pdo                   = $pdo;
    }

    public function __invoke(string $token, string $newPassword): Payload
    {
        try {
            $user = ($this->fetchUserByResetToken)($token);

            if ($user === null) {
                return new Payload(
                    Payload::STATUS_NOT_VALID,
                );
            }

            $user->newPassword = $newPassword;

            $payload = ($this->saveUser)($user);

            if ($payload->getStatus() !== Payload::STATUS_UPDATED) {
                return $payload;
            }

            $statement = $this->pdo->prepare(
                'DELETE FROM user_password_reset_tokens WHERE id=:id'
            );

            $statement->execute([':id' => $token]);

            return $payload;
        } catch (Throwable $e) {
            return new Payload(
                Payload::STATUS_ERROR,
                ['message' => 'An unknown error occurred']
            );
        }
    }
}
