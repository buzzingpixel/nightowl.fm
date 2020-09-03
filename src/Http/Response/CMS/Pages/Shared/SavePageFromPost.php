<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Pages\Shared;

use App\Context\Pages\Models\PageModel;
use App\Context\Pages\PagesApi;
use App\Context\Pages\Services\ValidateUniquePageUri;
use App\Factories\ValidationFactory;
use App\Http\Response\CMS\Pages\EditPage\PostEditPageResponder;
use App\Http\Response\CMS\Pages\NewPage\PostNewPageResponder;
use App\Payload\Payload;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as V;

use function array_merge;
use function array_shift;
use function assert;
use function explode;
use function is_array;
use function is_string;
use function mb_substr;

class SavePageFromPost
{
    private ValidationFactory $validationFactory;
    private ValidateUniquePageUri $validateUniquePageUri;
    private PagesApi $pagesApi;

    public function __construct(
        ValidationFactory $validationFactory,
        ValidateUniquePageUri $validateUniquePageUri,
        PagesApi $pagesApi
    ) {
        $this->validationFactory     = $validationFactory;
        $this->validateUniquePageUri = $validateUniquePageUri;
        $this->pagesApi              = $pagesApi;
    }

    /**
     * @param PostNewPageResponder|PostEditPageResponder $responder
     */
    public function save(
        ServerRequestInterface $request,
        PageModel $page,
        $responder
    ): ResponseInterface {
        $post = $request->getParsedBody();

        assert(is_array($post));

        $data = [
            'title' => (string) ($post['title'] ?? $page->title),
            'uri' => (string) ($post['uri'] ?? $page->uri),
            'content' => (string) ($post['content'] ?? $page->content),
        ];

        $validator = $this->validationFactory->make(
            ['notEmpty' => 'Value must not be empty'],
        );

        /** @psalm-suppress MixedArgument */
        $validator->validate(
            $data,
            [
                'title' => V::notEmpty(),
                'uri' => V::allOf(
                    V::notEmpty(),
                    V::callback(
                        /**
                         * @param mixed $input
                         */
                        function ($input): bool {
                            if (! is_string($input)) {
                                return false;
                            }

                            if (mb_substr($input, 0, 1) !== '/') {
                                return false;
                            }

                            $uriArray = explode('/', $input);
                            array_shift($uriArray);

                            foreach ($uriArray as $uriSegment) {
                                $validator = $this->validationFactory->make();

                                $validator->validate(
                                    ['uriSegment' => $uriSegment],
                                    ['uriSegment' => V::slug()],
                                );

                                if ($validator->isValid()) {
                                    continue;
                                }

                                return false;
                            }

                            return true;
                        }
                    )->setTemplate('Must be valid URI, beginning with a "/"'),
                    V::callback(
                        /**
                         * @param mixed $input
                         */
                        function ($input) use ($page): bool {
                            if (! is_string($input)) {
                                return false;
                            }

                            return $this->validateUniquePageUri->validate(
                                $input,
                                $page->id,
                            );
                        }
                    )->setTemplate('Must be unique URI'),
                ),
                'content' => V::notEmpty(),
            ],
        );

        if (! $validator->isValid()) {
            return $responder->respond(
                new Payload(
                    Payload::STATUS_NOT_VALID,
                    [
                        'message' => 'The data provided was invalid',
                        'inputMessages' => $validator->getErrors(),
                        'inputValues' => $post,
                    ]
                ),
                $page,
            );
        }

        $page->title   = $data['title'];
        $page->uri     = $data['uri'];
        $page->content = $data['content'];

        $payload = $this->pagesApi->savePage($page);

        return $responder->respond(
            new Payload(
                $payload->getStatus(),
                array_merge(
                    $payload->getResult(),
                    ['inputValues' => $post],
                ),
            ),
            $page,
        );
    }
}
