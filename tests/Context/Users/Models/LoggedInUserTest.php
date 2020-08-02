<?php

declare(strict_types=1);

namespace Tests\Context\Users\Models;

use App\Context\Users\Models\LoggedInUser;
use App\Context\Users\Models\UserModel;
use PHPUnit\Framework\TestCase;

class LoggedInUserTest extends TestCase
{
    public function test(): void
    {
        $user = new UserModel();

        $model = new LoggedInUser($user);

        self::assertSame(
            $user,
            $model->model(),
        );
    }
}
