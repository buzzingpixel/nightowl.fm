<?php

declare(strict_types=1);

namespace App\Context\Users\Events;

use App\Context\Events\StoppableEvent;
use App\Context\Users\Models\UserModel;

class SaveUserBeforeSave extends StoppableEvent
{
    public UserModel $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }
}
