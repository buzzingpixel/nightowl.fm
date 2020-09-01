<?php

declare(strict_types=1);

use App\Http\Response\GetMasterFeedAction;
use App\Http\Response\Home\HomeAction;
use App\Http\Response\IForgot\IForGotAction;
use App\Http\Response\IForgot\PostIForgotAction;
use App\Http\Response\LogIn\PostLogInAction;
use App\Http\Response\LogIn\PostLogOutAction;
use App\Http\Response\ResetPasswordWithToken\PostResetPasswordWithTokenAction;
use App\Http\Response\ResetPasswordWithToken\ResetPasswordWithTokenAction;
use App\Http\Response\Show\FeedAction;
use Slim\App;

return static function (App $app): void {
    // Match all integers except 0 or 1
    // {page:(?!(?:0|1)$)\d+}

    // Match anything except a forward slash
    // {slug:[^\/]+}

    $app->get('/', HomeAction::class);

    $app->post('/log-in', PostLogInAction::class);
    $app->post('/log-out', PostLogOutAction::class);
    $app->get('/iforgot', IForGotAction::class);
    $app->post('/iforgot', PostIForgotAction::class);
    $app->get('/reset-pw-with-token/{token}', ResetPasswordWithTokenAction::class);
    $app->post('/reset-pw-with-token/{token}', PostResetPasswordWithTokenAction::class);

    // CMS
    $cmsRoutes = require __DIR__ . '/CMS/index.php';
    $cmsRoutes($app);

    // Site FE

    $app->get('/masterfeed', GetMasterFeedAction::class);

    // Shows
    $app->get('/{showSlug}/feed', FeedAction::class);
};
