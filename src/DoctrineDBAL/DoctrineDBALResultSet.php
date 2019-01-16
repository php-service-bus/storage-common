<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\DoctrineDBAL;

use Amp\Promise;
use Amp\Success;
use Desperado\ServiceBus\Storage\ResultSet;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

/**
 *
 */
final class DoctrineDBALResultSet implements ResultSet
{
    /**
     * Last row emitted
     *
     * @var array<array-key, string|int|float|resource|null>|null
     */
    private $currentRow;

    /**
     * Pdo fetch result
     *
     * @var array<array-key, array<string, string|int|float|resource|null>|null
     */
    private $fetchResult;

    /**
     * Results count
     *
     * @var int
     */
    private $resultsCount;

    /**
     * Current iterator position
     *
     * @var int
     */
    private $currentPosition = 0;

    /**
     * Connection instance
     *
     * @var Connection
     */
    private $connection;

    /**
     * Number of rows affected by the last DELETE, INSERT, or UPDATE statement
     *
     * @var int
     */
    private $affectedRows;

    /**
     * @param Connection $connection
     * @param Statement  $wrappedStmt
     */
    public function __construct(Connection $connection, Statement $wrappedStmt)
    {
        /** @var array<array-key, array<string, string|int|float|resource|null>|null> $rows */
        $rows = $wrappedStmt->fetchAll();

        $this->connection   = $connection;
        $this->fetchResult  = $rows;
        $this->affectedRows = $wrappedStmt->rowCount();
        $this->resultsCount = \count($this->fetchResult);
    }

    /**
     * @inheritdoc
     */
    public function advance(): Promise
    {
        $this->currentRow = null;

        if(++$this->currentPosition > $this->resultsCount)
        {
            return new Success(false);
        }

        return new Success(true);
    }

    /**
     * @inheritdoc
     */
    public function getCurrent(): ?array
    {
        if(null !== $this->currentRow)
        {
            return $this->currentRow;
        }

        /** @var array<array-key, string|int|float|resource|null>|null $data */
        $data = $this->fetchResult[$this->currentPosition - 1] ?? null;

        if(true === \is_array($data) && 0 === \count($data))
        {
            $data = null;
        }

        return $this->currentRow = $data;
    }

    /**
     * @inheritdoc
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        return $this->connection->lastInsertId($sequence);
    }

    /**
     * @inheritdoc
     */
    public function affectedRows(): int
    {
        return $this->affectedRows;
    }
}
