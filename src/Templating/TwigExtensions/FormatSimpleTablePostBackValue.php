<?php

declare(strict_types=1);

namespace App\Templating\TwigExtensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function is_array;

class FormatSimpleTablePostBackValue extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [$this->getFunction()];
    }

    private function getFunction(): TwigFunction
    {
        return new TwigFunction(
            'formatSimpleTablePostBackValue',
            [$this, 'format']
        );
    }

    /**
     * @param mixed $val
     *
     * @return mixed[]
     */
    public function format($val): array
    {
        if (! is_array($val)) {
            return [];
        }

        $rows = [];

        /** @psalm-suppress MixedAssignment */
        foreach ($val as $colName => $colRows) {
            if (! is_array($colRows)) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            foreach ($colRows as $rowKey => $colValue) {
                /** @psalm-suppress MixedAssignment */
                $rows[$rowKey][$colName] = $colValue;
            }
        }

        return $rows;
    }
}
