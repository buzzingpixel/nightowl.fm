<?php

declare(strict_types=1);

namespace App\Context\Users\Services;

use App\Context\Users\Models\UserModel;
use App\Payload\Payload;
use buzzingpixel\cookieapi\interfaces\CookieApiInterface;
use DateTimeZone;
use Safe\DateTimeImmutable;

use function Safe\strtotime;

class LogUserIn
{
    private ValidateUserPassword $validateUserPassword;
    private CreateUserSession $createUserSession;
    private CookieApiInterface $cookieApi;

    public function __construct(
        ValidateUserPassword $validateUserPassword,
        CreateUserSession $createUserSession,
        CookieApiInterface $cookieApi
    ) {
        $this->validateUserPassword = $validateUserPassword;
        $this->createUserSession    = $createUserSession;
        $this->cookieApi            = $cookieApi;
    }

    public function __invoke(UserModel $user, string $password): Payload
    {
        $validPassword = ($this->validateUserPassword)(
            $user,
            $password
        );

        if (! $validPassword) {
            return new Payload(
                Payload::STATUS_NOT_VALID,
                ['message' => 'Your password is invalid']
            );
        }

        $createSessionPayload = ($this->createUserSession)($user);

        if ($createSessionPayload->getStatus() !== Payload::STATUS_CREATED) {
            return new Payload(Payload::STATUS_ERROR);
        }

        /** @var array<string, string> $result */
        $result = $createSessionPayload->getResult();

        $sessionId = $result['id'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $currentDate = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $currentDatePlus20Years = $currentDate->setTimestamp(
            strtotime('+ 20 years')
        );

        $this->cookieApi->saveCookie(
            $this->cookieApi->makeCookie(
                'user_session_token',
                $sessionId,
                $currentDatePlus20Years
            )
        );

        return new Payload(
            Payload::STATUS_SUCCESSFUL,
            ['id' => $sessionId]
        );
    }
}
