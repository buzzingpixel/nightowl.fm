<?php

declare(strict_types=1);

namespace App\Cli\Commands\Queue;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

use function gc_collect_cycles;
use function usleep;

class QueueListenCommand extends Command
{
    private RunQueueCommand $runQueue;

    public function __construct(RunQueueCommand $runQueue)
    {
        parent::__construct();

        $this->runQueue = $runQueue;
    }

    protected function configure(): void
    {
        $this->setName('queue:listen');

        $this->setDescription(
            'Continuously runs the queue'
        );
    }

    private bool $run = true;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        do {
            gc_collect_cycles();
            // $output->writeln(memory_get_usage());
            $this->innerRun();
        } while ($this->run);

        return 0;
    }

    public function innerRun(): void
    {
        $input = new ArgvInput();

        $output = new NullOutput();

        $this->runQueue->execute($input, $output);

        // Sleep for a 0.05 seconds
        usleep(50000);
    }
}
