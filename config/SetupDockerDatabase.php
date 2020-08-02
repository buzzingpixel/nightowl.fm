<?php

declare(strict_types=1);

namespace Config;

use Exception;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function getenv;

class SetupDockerDatabase extends Command
{
    // phpcs:disable
    protected static $defaultName = 'app-setup:setup-docker-database';
    // phpcs:enable

    /**
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $pdo = new PDO(
            'pgsql:host=db;port=5432',
            'postgres',
            (string) getenv('DB_PASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $query = $pdo->query(
            "SELECT 1 from pg_database WHERE datname='nightowl'"
        );

        if ($query === false) {
            throw new Exception('Unable to query database');
        }

        $check = $query->fetch();

        if ($check !== false) {
            $output->writeln(
                '<fg=green>nightowl database is already set up</>'
            );

            return 0;
        }

        $go = true;

        if (getenv('DB_USER') === false) {
            $go = false;

            $output->writeln(
                '<fg=red>DB_USER environment variable is not set</>'
            );
        }

        if (getenv('DB_PASSWORD') === false) {
            $go = false;

            $output->writeln(
                '<fg=red>DB_PASSWORD environment variable is not set</>'
            );
        }

        if (! $go) {
            return 0;
        }

        $output->writeln(
            '<fg=yellow>Creating nightowl database</>'
        );

        $pdo->exec('CREATE DATABASE nightowl');

        $pdo->exec(
            'CREATE USER ' .
            (string) getenv('DB_USER') .
            " WITH ENCRYPTED PASSWORD '" .
            (string) getenv('DB_PASSWORD') .
            "';"
        );

        $pdo->exec(
            'GRANT ALL PRIVILEGES ON DATABASE nightowl TO ' .
            (string) getenv('DB_USER')
        );

        $output->writeln('<fg=green>nightowl database was created</>');

        return 0;
    }
}
