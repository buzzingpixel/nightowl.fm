<?php

declare(strict_types=1);

namespace App\Payload;

use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;

use function assert;
use function is_string;
use function mb_strpos;
use function mb_substr;
use function method_exists;
use function strrev;
use function ucfirst;

abstract class SpecificPayload
{
    protected bool $isInitialized = false;

    /**
     * @param mixed[] $vars
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws LogicException
     */
    public function __construct(array $vars = [])
    {
        if ($this->isInitialized) {
            throw new LogicException(
                static::class . ' instances can only be initialized once.'
            );
        }

        $this->isInitialized = true;

        /** @psalm-suppress MixedAssignment */
        foreach ($vars as $var => $val) {
            assert(is_string($var));

            $method = 'set' . ucfirst($var);

            if (! method_exists($this, $method)) {
                throw new InvalidArgumentException(
                    'Property does not exist: ' . $var
                );
            }

            $this->{$method}($val);
        }

        $reflect = new ReflectionClass($this);

        $this->shortName = $reflect->getShortName();

        $this->name = $this->shortName;

        $reversedShortName = strrev($this->shortName);

        // @codeCoverageIgnoreStart
        if (mb_strpos($reversedShortName, 'daolyaP') !== 0) {
            return;
        }

        // @codeCoverageIgnoreEnd

        $this->name = (string) mb_substr($this->shortName, 0, -7);
    }

    protected string $shortName = '';

    public function getShortName(): string
    {
        return $this->shortName;
    }

    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }
}
