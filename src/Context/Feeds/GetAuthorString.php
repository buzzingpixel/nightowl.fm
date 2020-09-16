<?php

declare(strict_types=1);

namespace App\Context\Feeds;

use App\Context\People\Models\PersonModel;

use function array_values;
use function count;

class GetAuthorString
{
    /**
     * @param PersonModel[] $people
     */
    public function get(array $people): string
    {
        $str = '';

        $people = array_values($people);

        $totalPeople = count($people);

        foreach ($people as $i => $person) {
            $count = $i + 1;

            if ($totalPeople === 1) {
                $str = $person->getFullName();

                break;
            }

            if ($totalPeople === 2) {
                $str .= $person->getFullName();

                if ($i === 0) {
                    $str .= ' and ';
                }

                continue;
            }

            $str .= $person->getFullName();

            if ($count < $totalPeople) {
                $str .= ', ';
            }

            if ($count !== $totalPeople - 1) {
                continue;
            }

            $str .= '&amp; ';
        }

        return $str;
    }
}
