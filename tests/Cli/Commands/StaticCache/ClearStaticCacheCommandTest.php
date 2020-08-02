<?php

declare(strict_types=1);

namespace Tests\Cli\Commands\StaticCache;

use App\Cli\Commands\StaticCache\ClearStaticCacheCommand;
use App\Http\ServiceSuites\StaticCache\StaticCacheApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearStaticCacheCommandTest extends TestCase
{
    public function test(): void
    {
        $staticCacheApi = $this->createMock(
            StaticCacheApi::class
        );

        $staticCacheApi->expects(self::once())
            ->method('clearStaticCache');

        $command = new ClearStaticCacheCommand($staticCacheApi);

        self::assertSame(
            'static-cache:clear',
            $command->getName()
        );

        self::assertSame(
            'Clears the static cache',
            $command->getDescription()
        );

        $input = $this->createMock(InputInterface::class);

        $input->expects(self::never())
            ->method(self::anything());

        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=yellow>Clearing static cache...</>'
            ));

        $output->expects(self::at(1))
            ->method('writeln')
            ->with(self::equalTo(
                '<fg=green>Static cache cleared</>'
            ));

        self::assertSame(
            0,
            $command->execute($input, $output),
        );
    }
}
