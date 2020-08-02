<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Payload\Payload;
use buzzingpixel\cookieapi\interfaces\CookieApiInterface;
use PDO;

class LogCurrentUserOut
{
    private CookieApiInterface $cookieApi;
    private PDO $pdo;

    public function __construct(
        CookieApiInterface $cookieApi,
        PDO $pdo
    ) {
        $this->cookieApi = $cookieApi;
        $this->pdo       = $pdo;
    }

    public function __invoke(): Payload
    {
        $cookie = $this->cookieApi->retrieveCookie('user_session_token');

        if ($cookie === null) {
            return new Payload(
                Payload::STATUS_NOT_VALID,
                ['message' => 'User is not logged in']
            );
        }

        $sessionId = $cookie->value();

        $this->cookieApi->deleteCookie($cookie);

        if ($sessionId === '') {
            return new Payload(
                Payload::STATUS_SUCCESSFUL
            );
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM user_sessions WHERE id=:id'
        );

        $statement->execute([':id' => $sessionId]);

        return new Payload(
            Payload::STATUS_SUCCESSFUL
        );
    }
}
