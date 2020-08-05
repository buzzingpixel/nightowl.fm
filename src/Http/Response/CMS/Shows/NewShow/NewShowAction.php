<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\NewShow;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseInterface;

class NewShowAction
{
    private NewShowResponder $responder;

    public function __construct(
        NewShowResponder $responder
    ) {
        $this->responder = $responder;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Create New Show';

        return $this->responder->__invoke($meta);
    }
}
