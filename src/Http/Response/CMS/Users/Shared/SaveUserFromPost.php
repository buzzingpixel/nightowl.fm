<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Users\Shared;

use App\Context\Users\Models\UserModel;
use App\Context\Users\UserApi;
use App\Http\Response\CMS\Users\EditUser\PostEditUserResponder;
use App\Http\Response\CMS\Users\NewUser\PostNewUserResponder;
use App\Payload\Payload;
use App\Utilities\SimpleValidator;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\UuidFactory;
use Throwable;

use function assert;
use function count;
use function is_array;

class SaveUserFromPost
{
    private UuidFactory $uuidFactory;
    private UserApi $userApi;

    public function __construct(
        UuidFactory $uuidFactory,
        UserApi $userApi
    ) {
        $this->uuidFactory = $uuidFactory;
        $this->userApi     = $userApi;
    }

    /**
     * @param PostNewUserResponder|PostEditUserResponder $responder
     *
     * @throws Throwable
     */
    public function save(
        ServerRequestInterface $request,
        UserModel $user,
        $responder
    ): ResponseInterface {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $data = [
            'email_address' => (string) ($post['email_address'] ?? ''),
            'is_active' => (string) ($post['is_active'] ?? ''),
            'timezone' => (string) ($post['timezone'] ?? ''),
            'send_password_reset' => (string) ($post['send_password_reset'] ?? ''),
        ];

        $inputMessages = [];

        if (! SimpleValidator::email($data['email_address'])) {
            $inputMessages['email_address'] = 'A valid email is required';
        }

        $userTimezone = new DateTimeZone('US/Central');

        if ($data['timezone'] === '') {
            $inputMessages['timezone'] = 'Timezone is required';
        } else {
            try {
                $userTimezone = new DateTimeZone($data['timezone']);
            } catch (Throwable $e) {
                $inputMessages['timezone'] = 'A valid timezone is required';
            }
        }

        if (count($inputMessages) > 0) {
            return $responder->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'There were errors with your submission',
                        'inputMessages' => $inputMessages,
                        'inputValues' => $data,
                    ],
                ),
                $user,
            );
        }

        $user->emailAddress = $data['email_address'];

        $user->newPassword = $this->uuidFactory->uuid4()->toString();

        $user->isActive = $data['is_active'] === 'true';

        $user->timezone = $userTimezone;

        $isNew = $user->id === '';

        $payload = $this->userApi->saveUser($user);

        if ($isNew && $payload->getStatus() !== Payload::STATUS_CREATED) {
            return $responder->respond(
                new Payload(
                    Payload::STATUS_NOT_CREATED,
                    ['message' => 'An unknown error occurred'],
                ),
                $user,
            );
        }

        if (! $isNew && $payload->getStatus() !== Payload::STATUS_UPDATED) {
            return $responder->respond($payload, $user);
        }

        if ($data['send_password_reset'] === 'true') {
            $this->userApi->requestPasswordResetEmail($user);
        }

        return $responder->respond($payload, $user);
    }
}
