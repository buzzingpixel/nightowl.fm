<?php

declare(strict_types=1);

namespace App\Http\Response\People;

use App\Context\Episodes\EpisodeApi;
use App\Context\Episodes\Models\FetchModel;
use App\Context\People\Models\FetchModel as PersonFetchModel;
use App\Context\People\PeopleApi;
use App\Context\Shows\Models\FetchModel as ShowFetchModel;
use App\Context\Shows\ShowApi;
use App\Context\Shows\ShowConstants;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Twig\Environment as TwigEnvironment;

class GetPersonAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private PeopleApi $peopleApi;
    private ShowApi $showApi;
    private EpisodeApi $episodeApi;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig,
        PeopleApi $peopleApi,
        ShowApi $showApi,
        EpisodeApi $episodeApi
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->peopleApi       = $peopleApi;
        $this->showApi         = $showApi;
        $this->episodeApi      = $episodeApi;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $slug = (string) $request->getAttribute('personSlug');

        $personFetchModel = new PersonFetchModel();

        $personFetchModel->slugs = [$slug];

        $person = $this->peopleApi->fetchPerson($personFetchModel);

        if ($person === null) {
            throw new HttpNotFoundException($request);
        }

        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true');

        $meta = new Meta();

        $meta->title = $person->getFullName();

        $hostFetchModel              = new ShowFetchModel();
        $hostFetchModel->hosts       = [$person];
        $hostFetchModel->notStatuses = [ShowConstants::SHOW_STATUS_HIDDEN];

        $hostShows = $this->showApi->fetchShows($hostFetchModel);

        $guestEpisodesFetchModel              = new FetchModel();
        $guestEpisodesFetchModel->guests      = [$person];
        $guestEpisodesFetchModel->isPublished = true;
        $guestEpisodesFetchModel->notShows    = $hostShows;
        $guestEpisodesFetchModel->limit       = 100;

        $guestEpisodes = $this->episodeApi->fetchEpisodes(
            $guestEpisodesFetchModel
        );

        $response->getBody()->write(
            $this->twig->render(
                'Http/Person.twig',
                [
                    'meta' => $meta,
                    'person' => $person,
                    'hostShows' => $hostShows,
                    'guestEpisodes' => $guestEpisodes,
                ]
            ),
        );

        return $response;
    }
}
