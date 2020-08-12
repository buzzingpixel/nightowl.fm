<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\NewPerson;

use App\Context\People\Models\PersonModel;
use App\Http\Response\CMS\People\Shared\SavePersonFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostNewPersonAction
{
    private PostNewPersonResponder $responder;
    private SavePersonFromPost $savePersonFromPost;

    public function __construct(
        PostNewPersonResponder $responder,
        SavePersonFromPost $savePersonFromPost
    ) {
        $this->responder          = $responder;
        $this->savePersonFromPost = $savePersonFromPost;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->savePersonFromPost->save(
            $request,
            new PersonModel(),
            $this->responder
        );
    }
}
