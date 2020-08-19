<?php

declare(strict_types=1);

namespace App\Context\People\EventListeners;

use App\Context\People\Events\DeletePersonAfterDelete;
use App\Context\People\Services\DeleteUserProfilePhoto;

class DeletePersonAfterDeleteDeleteProfilePhoto
{
    private DeleteUserProfilePhoto $deleteUserProfilePhoto;

    public function __construct(DeleteUserProfilePhoto $deleteUserProfilePhoto)
    {
        $this->deleteUserProfilePhoto = $deleteUserProfilePhoto;
    }

    public function onAfterDelete(DeletePersonAfterDelete $afterDelete): void
    {
        $this->deleteUserProfilePhoto->delete($afterDelete->person);
    }
}
