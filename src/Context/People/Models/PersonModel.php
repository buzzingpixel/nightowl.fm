<?php

declare(strict_types=1);

namespace App\Context\People\Models;

use App\Context\Links\Models\LinkModel;
use LogicException;

use function array_walk;
use function assert;
use function is_array;
use function pathinfo;

/**
 * @property LinkModel[] $links
 */
class PersonModel
{
    public string $id = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $slug = '';

    public string $email = '';

    public string $photoFileLocation = '';

    public function getPhotoFileName(): string
    {
        $pathInfo = pathinfo($this->photoFileLocation);

        return (string) $pathInfo['basename'];
    }

    public string $newPhotoFileLocation = '';

    public string $photoPreference = '';

    public string $bio = '';

    public string $location = '';

    public string $facebookPageSlug = '';

    public string $twitterHandle = '';

    /** @var LinkModel[] */
    private array $links = [];

    public function addLink(LinkModel $link): void
    {
        $this->links[] = $link;
    }

    /**
     * @param LinkModel[] $links
     */
    public function setLinks(array $links): void
    {
        array_walk($links, [$this, 'addLink']);
    }

    /**
     * @return LinkModel[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @param mixed $val
     */
    public function __set(string $name, $val): void
    {
        if ($name !== 'links') {
            throw new LogicException('Invalid property ' . $name);
        }

        assert(is_array($val));

        /** @psalm-suppress MixedArgumentTypeCoercion */
        $this->setLinks($val);
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name !== 'links') {
            throw new LogicException('Invalid property ' . $name);
        }

        return $this->getLinks();
    }

    public function __isset(string $name): bool
    {
        return $name === 'links';
    }
}
