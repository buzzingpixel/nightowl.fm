<?php

declare(strict_types=1);

namespace Config;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

use function array_slice;
use function basename;
use function count;
use function dirname;
use function explode;
use function implode;

class Schedule
{
    /** @var string[] */
    private static array $scheduleClasses = [];

    /**
     * @return string[]
     */
    public function getScheduleClasses(): array
    {
        if (count(self::$scheduleClasses) > 0) {
            return self::$scheduleClasses;
        }

        $directory = new RecursiveDirectoryIterator(
            dirname(__DIR__) . '/src/Context/Schedule/Runners'
        );

        $iterator = new RecursiveIteratorIterator($directory);

        $finalIterator = new RegexIterator(
            $iterator,
            '/^.+\.php$/i',
            RecursiveRegexIterator::GET_MATCH
        );

        $dir = __DIR__ . '/src';

        $dirArray = explode('/', $dir);

        $classNames = [];

        foreach ($finalIterator as $files) {
            foreach ($files as $file) {
                $baseName = basename($file, '.php');

                $fileNameArray = explode('/', $file);

                $newFileNameArray = array_slice(
                    $fileNameArray,
                    count($dirArray) - 1
                );

                unset($newFileNameArray[count($newFileNameArray) - 1]);

                $className = implode('\\', $newFileNameArray);

                $className = 'App\\' . $className . '\\' . $baseName;

                $classNames[] = $className;
            }
        }

        self::$scheduleClasses = $classNames;

        return self::$scheduleClasses;
    }
}
