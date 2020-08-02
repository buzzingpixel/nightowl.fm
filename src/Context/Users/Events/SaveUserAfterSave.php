<?php

declare(strict_types=1);

namespace App\Context\Users\Events;

use App\Context\Events\StoppableEvent;
use App\Context\Users\Models\UserModel;
use App\Payload\Payload;

class SaveUserAfterSave extends StoppableEvent
{
    public UserModel $userModel;
    public Payload $payload;

    public function __construct(UserModel $userModel, Payload $payload)
    {
        $this->userModel = $userModel;
        $this->payload   = $payload;
    }
}
