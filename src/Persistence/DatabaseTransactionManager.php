<?php

declare(strict_types=1);

namespace App\Persistence;

use PDO;
use PDOException;
use Throwable;

use function assert;
use function debug_backtrace;
use function is_string;

use const DEBUG_BACKTRACE_PROVIDE_OBJECT;

class DatabaseTransactionManager
{
    private string $caller       = '';
    private bool $rollbackCalled = false;

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Ensures a transaction has begun.
     * If a transaction has not been started, a transaction will be started.
     * If a transaction has already been started, nothing will be done.
     * Only the calling class will be able to commit the transaction
     *
     * Returns (bool) true if a transaction is started or already in progress
     * Returns (bool) false on failure to start transaction
     */
    public function beginTransaction(): bool
    {
        if ($this->pdo->inTransaction()) {
            return true;
        }

        try {
            $trace = debug_backtrace(
                DEBUG_BACKTRACE_PROVIDE_OBJECT,
                2
            );

            /** @psalm-suppress MixedAssignment */
            $caller = $trace['1']['class'];

            assert(is_string($caller));

            $this->caller = $caller;

            return $this->pdo->beginTransaction();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Commits the transaction if the class that started the transaction is
     * the caller
     *
     * Returns (bool) true if transaction is committed successfully
     * Returns (bool) false if transaction cannot be committed successfully
     * or commit called by class that did not originally open the transaction
     *
     * @throws PDOException
     */
    public function commit(): bool
    {
        if ($this->rollbackCalled) {
            throw new PDOException();
        }

        $trace = debug_backtrace(
            DEBUG_BACKTRACE_PROVIDE_OBJECT,
            2
        );

        /** @psalm-suppress MixedAssignment */
        $caller = $trace['1']['class'];

        assert(is_string($caller));

        if ($caller !== $this->caller) {
            return false;
        }

        $this->caller = '';

        $this->rollbackCalled = false;

        return $this->pdo->commit();
    }

    /**
     * Rolls back the transaction if the rollback is called by the class
     * that originally started the transaction. If called by another class that
     * did not start the transaction, a property will be set so the call to
     * commit subsequently will fail.
     */
    public function rollBack(): bool
    {
        $trace = debug_backtrace(
            DEBUG_BACKTRACE_PROVIDE_OBJECT,
            2
        );

        /** @psalm-suppress MixedAssignment */
        $caller = $trace['1']['class'];

        assert(is_string($caller));

        if ($caller !== $this->caller) {
            $this->rollbackCalled = true;

            return false;
        }

        $this->caller = '';

        $this->rollbackCalled = false;

        return $this->pdo->rollBack();
    }
}
