<?php

declare(strict_types=1);

namespace App\Persistence;

use ReflectionClass;
use ReflectionProperty;

use function array_map;

abstract class Record
{
    protected static string $tableName = '';

    public string $id = '';

    public static function tableName(): string
    {
        return static::$tableName;
    }

    public function getTableName(): string
    {
        return static::$tableName;
    }

    /**
     * @return string[]
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getFields(bool $prefixNamesForBind = false): array
    {
        $prefixNamesWith = $prefixNamesForBind ? ':' : '';

        /** @noinspection PhpUnhandledExceptionInspection */
        $reflectionClass = new ReflectionClass($this);

        $properties = $reflectionClass->getProperties(
            ReflectionProperty::IS_PUBLIC
        );

        return array_map(
            static function (ReflectionProperty $property) use (
                $prefixNamesWith
            ) {
                return $prefixNamesWith . $property->getName();
            },
            $properties
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getBindValues(): array
    {
        $bindValues = [];

        foreach ($this->getFields() as $field) {
            /** @psalm-suppress MixedAssignment */
            $bindValues[':' . $field] = $this->{$field};
        }

        return $bindValues;
    }
}
