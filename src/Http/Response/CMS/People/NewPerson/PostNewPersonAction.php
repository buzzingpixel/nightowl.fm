<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People\NewPerson;

use App\Context\Links\Models\LinkModel;
use App\Context\People\Models\PersonModel;
use App\Context\People\PeopleApi;
use App\Factories\ValidationFactory;
use App\Payload\Payload;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as V;

use function assert;
use function is_array;

class PostNewPersonAction
{
    private ValidationFactory $validationFactory;
    private PeopleApi $peopleApi;
    private PostNewPersonResponder $responder;

    public function __construct(
        ValidationFactory $validationFactory,
        PeopleApi $peopleApi,
        PostNewPersonResponder $responder
    ) {
        $this->validationFactory = $validationFactory;
        $this->peopleApi         = $peopleApi;
        $this->responder         = $responder;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $data = [
            'first_name' => (string) ($post['first_name'] ?? ''),
            'last_name' => (string) ($post['last_name'] ?? ''),
            'slug' => (string) ($post['slug'] ?? ''),
            'email' => (string) ($post['email'] ?? ''),
            'photo_file_path' => (string) ($post['photo']['file_path'] ?? ''),
            'photo_file_name' => (string) ($post['photo']['file_name'] ?? ''),
            'photo_preference' => (string) ($post['photo_preference'] ?? ''),
            'bio' => (string) ($post['bio'] ?? ''),
            'location' => (string) ($post['location'] ?? ''),
            'facebook_page_slug' => (string) ($post['facebook_page_slug'] ?? ''),
            'twitter_handle' => (string) ($post['twitter_handle'] ?? ''),
            'links' => [],
        ];

        $validator = $this->validationFactory->make(
            [
                'notEmpty' => 'Value must not be empty',
                'email' => 'Must be a valid email address',
                'slug' => 'Must not contain spaces, capitalization, or special characters',
            ],
        );

        $validator->validate(
            $data,
            [
                'first_name' => V::notEmpty(),
                'last_name' => V::notEmpty(),
                'slug' => V::allOf(
                    V::notEmpty(),
                    V::slug(),
                ),
                'email' => V::allOf(
                    V::notEmpty(),
                    V::email(),
                ),
                'photo_preference' => V::notEmpty(),
                'bio' => V::notEmpty(),
            ]
        );

        if (! $validator->isValid()) {
            return $this->responder->respond(new Payload(
                Payload::STATUS_NOT_VALID,
                [
                    'message' => 'The data provided was invalid',
                    'inputMessages' => $validator->getErrors(),
                    'inputValues' => $post,
                ]
            ));
        }

        foreach (($post['links']['link_title'] ?? []) as $key => $title) {
            $title = (string) $title;
            $url   = (string) ($post['links']['link_url'][$key] ?? '');

            if ($title === '' || $url === '') {
                continue;
            }

            $data['links'][] = new LinkModel(
                $title,
                $url,
            );
        }

        $person                       = new PersonModel();
        $person->firstName            = $data['first_name'];
        $person->lastName             = $data['last_name'];
        $person->slug                 = $data['slug'];
        $person->email                = $data['email'];
        $person->newPhotoFileLocation = $data['photo_file_path'];
        $person->photoPreference      = $data['photo_preference'];
        $person->bio                  = $data['bio'];
        $person->location             = $data['location'];
        $person->facebookPageSlug     = $data['facebook_page_slug'];
        $person->twitterHandle        = $data['twitter_handle'];
        $person->setLinks($data['links']);

        return $this->responder->respond(
            $this->peopleApi->savePerson($person)
        );
    }
}
