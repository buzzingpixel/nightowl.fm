<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\EditPerson;

use App\Context\People\Models\FetchModel;
use App\Context\People\PeopleApi;
use App\Http\Response\CMS\People\Shared\SavePersonFromPost;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class PostEditPersonAction
{
    private PeopleApi $peopleApi;
    private PostEditPersonResponder $responder;
    private SavePersonFromPost $savePersonFromPost;

    public function __construct(
        PeopleApi $peopleApi,
        PostEditPersonResponder $responder,
        SavePersonFromPost $savePersonFromPost
    ) {
        $this->peopleApi          = $peopleApi;
        $this->responder          = $responder;
        $this->savePersonFromPost = $savePersonFromPost;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $fetchModel->ids = [(string) $request->getAttribute('id')];

        $person = $this->peopleApi->fetchPerson($fetchModel);

        if ($person === null) {
            throw new HttpNotFoundException($request);
        }

        return $this->savePersonFromPost->save(
            $request,
            $person,
            $this->responder
        );
    }
}
