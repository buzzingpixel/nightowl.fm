<?php

declare(strict_types=1);

use App\Http\Response\CMS\Ajax\PostFileUploadAction;
use App\Http\Response\CMS\IndexAction;
use App\Http\Response\CMS\People\NewPerson\NewPersonAction;
use App\Http\Response\CMS\People\NewPerson\PostNewPersonAction;
use App\Http\Response\CMS\People\PeopleIndexAction;
use App\Http\Response\CMS\Shows\NewShow\NewShowAction;
use App\Http\Response\CMS\Shows\ShowsIndexAction;
use App\Http\RouteMiddleware\RequireLogInAction;
use Config\NoOp;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $app->group('/cms', function (RouteCollectorProxy $r): void {
        // $this so PHPCS will be happy and not convert to static function.
        /** @phpstan-ignore-next-line */
        $this->get(NoOp::class)();

        $r->get('', IndexAction::class);

        $r->post(
            '/ajax/file-upload',
            PostFileUploadAction::class
        );

        $r->get('/shows', ShowsIndexAction::class);
        $r->get('/shows/new', NewShowAction::class);

        $r->get('/people', PeopleIndexAction::class);
        $r->get('/people/new', NewPersonAction::class);
        $r->post('/people/new', PostNewPersonAction::class);

        // $r->group('', function (RouteCollectorProxy $ri): void {
        //     // $this so PHPCS will be happy and not convert to static function.
        //     /** @phpstan-ignore-next-line */
        //     $this->get(NoOp::class)();
        //     $ri->get('/shows/new', NewShowAction::class);
        // })->add(RequireAdminAction::class);
    })->add(RequireLogInAction::class);
};
