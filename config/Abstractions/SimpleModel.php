<?php

declare(strict_types=1);

namespace Config\Abstractions;

class SimpleModel
{
    /**
     * @param mixed[] $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return static::${$name} ?? null;
    }
}
