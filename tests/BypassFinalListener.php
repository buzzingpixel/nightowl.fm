<?php

declare(strict_types=1);

namespace Tests;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

final class BypassFinalListener implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
    }
}
