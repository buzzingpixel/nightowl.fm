<?php

declare(strict_types=1);

namespace App\Http\Models;

use LogicException;

use function ceil;
use function http_build_query;
use function ltrim;
use function mb_strpos;
use function range;
use function rtrim;

class Pagination
{
    private bool $isInstantiated = false;

    private int $pad = 2;

    private int $currentPage = 1;

    private int $perPage = 12;

    private int $totalResults = 1;

    private string $base = '';

    private string $queryString = '';

    public function __construct()
    {
        if ($this->isInstantiated) {
            throw new LogicException(
                'Instance may only be instantiated once'
            );
        }

        $this->calcPrevPageLink = null;

        $this->isInstantiated = true;
    }

    public function pad(): int
    {
        return $this->pad;
    }

    public function withPad(int $val): Pagination
    {
        $clone = clone $this;

        $clone->pad = $val;

        return $clone;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function withCurrentPage(int $val): Pagination
    {
        $clone = clone $this;

        $clone->currentPage = $val;

        return $clone;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function withPerPage(int $val): Pagination
    {
        $clone = clone $this;

        $clone->perPage = $val;

        return $clone;
    }

    public function totalResults(): int
    {
        return $this->totalResults;
    }

    public function withTotalResults(int $val): Pagination
    {
        $clone = clone $this;

        $clone->totalResults = $val;

        return $clone;
    }

    public function base(): string
    {
        return $this->base;
    }

    public function withBase(string $val): Pagination
    {
        $clone = clone $this;

        $http  = mb_strpos($val, 'http://');
        $https = mb_strpos($val, 'https://');

        if ($http === 0 || $https === 0) {
            $clone->base = rtrim($val, '/');
        } else {
            $clone->base = '/' . rtrim(ltrim($val, '/'), '/');
        }

        return $clone;
    }

    public function queryString(): string
    {
        return $this->queryString;
    }

    public function withQueryString(string $val): Pagination
    {
        $clone = clone $this;

        $clone->queryString = $val;

        return $clone;
    }

    /**
     * @param string[] $val
     */
    public function withQueryStringFromArray(array $val): Pagination
    {
        $clone = clone $this;

        $clone->queryString = '?' . http_build_query($val);

        return $clone;
    }

    public function totalPages(): int
    {
        return (int) ceil($this->totalResults() / $this->perPage());
    }

    public function prevPage(): ?int
    {
        if ($this->currentPage() <= 1) {
            return null;
        }

        return $this->currentPage() - 1;
    }

    private ?string $calcPrevPageLink = null;

    public function prevPageLink(): ?string
    {
        if ($this->calcPrevPageLink === null) {
            if ($this->prevPage() === null) {
                return null;
            }

            $prevPage = (string) $this->prevPage();

            $this->calcPrevPageLink = $this->prevPage() > 1 ?
                $this->base() . '/page/' . $prevPage . $this->queryString() :
                $this->base() . $this->queryString();
        }

        return $this->calcPrevPageLink;
    }

    public function nextPage(): ?int
    {
        if ($this->currentPage() >= $this->totalPages()) {
            return null;
        }

        return $this->currentPage() + 1;
    }

    public function nextPageLink(): ?string
    {
        if ($this->nextPage() === null) {
            return null;
        }

        $nextPage = (string) $this->nextPage();

        return $this->base() . '/page/' . $nextPage . $this->queryString();
    }

    public function firstPageLink(): ?string
    {
        if ($this->currentPage() <= $this->pad() + 1) {
            return null;
        }

        $base = $this->base() !== '' ? $this->base() : '/';

        return $base . $this->queryString();
    }

    public function lastPageLink(): ?string
    {
        if ($this->currentPage() + $this->pad() >= $this->totalPages()) {
            return null;
        }

        return $this->base() . '/page/' . $this->totalPages() . $this->queryString();
    }

    /** @var mixed[]|null */
    private ?array $calcPagesArray = null;

    /**
     * @return mixed[]
     */
    public function pagesArray(): array
    {
        if ($this->calcPagesArray !== null) {
            return $this->calcPagesArray;
        }

        if ($this->totalPages() < 2) {
            return [];
        }

        $lowerRange = $this->currentPage() - $this->pad();
        $upperRange = $this->currentPage() + $this->pad();

        // Figure out if we're starting from one or ending at total
        if ($this->currentPage() < $this->pad() + 1) {
            $lowerRange = 1;
            $upperRange = ($this->pad() * 2) + 1;
        } elseif ($this->currentPage() + $this->pad() >= $this->totalPages()) {
            $lowerRange = $this->totalPages() - ($this->pad() * 2);
            $upperRange = $this->totalPages();
        }

        // Sanity check lower range
        $lowerRange = $lowerRange < 1 ? 1 : $lowerRange;

        // Sanity check upper range
        if ($upperRange > $this->totalPages()) {
            $upperRange = $this->totalPages();
        }

        $pages = [];

        foreach (range($lowerRange, $upperRange) as $pageNum) {
            $pageArray = [
                'label' => $pageNum,
                'target' => $this->base() . '/page/' . $pageNum . $this->queryString(),
                'isActive' => false,
            ];

            if ($pageNum === 1) {
                $pageArray['target'] = $this->base() . $this->queryString;
            }

            if ($pageNum === $this->currentPage()) {
                $pageArray['isActive'] = true;
            }

            $pages[] = $pageArray;
        }

        $this->calcPagesArray = $pages;

        return $pages;
    }
}
