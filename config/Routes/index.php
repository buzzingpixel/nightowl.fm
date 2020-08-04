<?php

declare(strict_types=1);

use App\Http\Response\Home\HomeAction;
use App\Http\Response\LogIn\PostLogInAction;
use Slim\App;

return static function (App $app): void {
    // Match all integers except 0 or 1
    // {page:(?!(?:0|1)$)\d+}

    // Match anything except a forward slash
    // {slug:[^\/]+}

    $app->get('/', HomeAction::class);

    $app->post('/log-in', PostLogInAction::class);

    // CMS
    $cmsRoutes = require __DIR__ . '/CMS/index.php';
    $cmsRoutes($app);
};
