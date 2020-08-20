<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows\Series\Shared;

use App\Context\Series\Models\SeriesModel;
use App\Context\Series\SeriesApi;
use App\Context\Shows\Models\ShowModel;
use App\Factories\ValidationFactory;
use App\Http\Response\CMS\Shows\Series\EditSeries\PostEditSeriesResponder;
use App\Http\Response\CMS\Shows\Series\NewSeries\PostNewSeriesResponder;
use App\Payload\Payload;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as V;

use function array_merge;
use function assert;
use function is_array;

class SaveSeriesFromPost
{
    private ValidationFactory $validationFactory;
    private SeriesApi $seriesApi;

    public function __construct(
        ValidationFactory $validationFactory,
        SeriesApi $seriesApi
    ) {
        $this->validationFactory = $validationFactory;
        $this->seriesApi         = $seriesApi;
    }

    /**
     * @param PostNewSeriesResponder|PostEditSeriesResponder $responder
     */
    public function save(
        ServerRequestInterface $request,
        SeriesModel $series,
        $responder,
        ShowModel $show
    ): ResponseInterface {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $data = [
            'title' => (string) ($post['title'] ?? ''),
            'slug' => (string) ($post['slug'] ?? ''),
            'description' => (string) ($post['description'] ?? ''),
        ];

        $validator = $this->validationFactory->make(
            [
                'notEmpty' => 'Value must not be empty',
                'slug' => 'Must not contain spaces, capitalization, or special characters',
            ],
        );

        $validator->validate(
            $data,
            [
                'title' => V::notEmpty(),
                'slug' => V::allOf(
                    V::notEmpty(),
                    V::slug(),
                    V::callback(
                        function ($input) use ($show, $series): bool {
                            return $this->seriesApi->validateUniqueSeriesSlug(
                                $input,
                                $show->id,
                                $series->id,
                            );
                        }
                    )->setTemplate('Show slug must be unique'),
                ),
                'description' => V::notEmpty(),
            ]
        );

        if (! $validator->isValid()) {
            return $responder->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'The data provided was invalid',
                        'inputMessages' => $validator->getErrors(),
                        'inputValues' => $post,
                    ],
                ),
                $show->id,
                $series,
            );
        }

        $series->title       = $data['title'];
        $series->slug        = $data['slug'];
        $series->description = $data['description'];
        $series->show        = $show;

        $payload = $this->seriesApi->saveSeries($series);

        return $responder->respond(
            new Payload(
                $payload->getStatus(),
                array_merge(
                    $payload->getResult(),
                    ['inputValues' => $post],
                ),
            ),
            $show->id,
            $series,
        );
    }
}
