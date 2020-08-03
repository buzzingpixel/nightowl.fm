<?php

declare(strict_types=1);

use App\Http\Response\Home\HomeAction;
use Slim\App;

return static function (App $app): void {
    // Match all integers except 0 or 1
    // {page:(?!(?:0|1)$)\d+}

    // Match anything except a forward slash
    // {slug:[^\/]+}

    $app->get('/', HomeAction::class);
};
