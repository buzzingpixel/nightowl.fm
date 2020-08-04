<?php

declare(strict_types=1);

use App\Http\Response\CMS\IndexAction;
use App\Http\Response\CMS\Shows\ShowsAction;
use App\Http\RouteMiddleware\RequireLogInAction;
use Config\NoOp;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $app->group('/cms', function (RouteCollectorProxy $r): void {
        // $this so PHPCS will be happy and not convert to static function.
        $this->get(NoOp::class)();

        $r->get('', IndexAction::class);

        $r->get('/shows', ShowsAction::class);
    })->add(RequireLogInAction::class);
};
