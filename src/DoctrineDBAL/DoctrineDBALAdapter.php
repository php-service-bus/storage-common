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

use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use Desperado\ServiceBus\Storage\DatabaseAdapter;
use Desperado\ServiceBus\Storage\Exceptions\InvalidConfigurationOptions;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * DoctrineDBAL adapter
 *
 * Designed primarily for testing. Please do not use this adapter in your code
 */
final class DoctrineDBALAdapter implements DatabaseAdapter
{
    /**
     * Storage config
     *
     * @var StorageConfiguration
     */
    private $configuration;

    /**
     * Doctrine connection
     *
     * @var Connection|null
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StorageConfiguration $configuration
     * @param LoggerInterface|null $logger
     */
    public function __construct(StorageConfiguration $configuration, LoggerInterface $logger = null)
    {
        $this->configuration = $configuration;
        $this->logger        = $logger ?? new NullLogger();
    }

    /**
     * @inheritDoc
     */
    public function execute(string $queryString, array $parameters = []): Promise
    {
        $this->logger->debug($queryString, $parameters);

        try
        {
            $statement = $this->connection()->prepare($queryString);
            $isSuccess = $statement->execute($parameters);

            if(false === $isSuccess)
            {
                // @codeCoverageIgnoreStart
                /** @var array{0:string, 1:int, 2:string} $errorInfo */
                $errorInfo = $this->connection()->errorInfo();

                /** @var string $message Driver-specific error message */
                $message = $errorInfo[2];

                throw new \RuntimeException($message);
                // @codeCoverageIgnoreEnd
            }

            return new Success(new DoctrineDBALResultSet($this->connection(), $statement));
        }
        catch(\Throwable $throwable)
        {
            return new Failure(DoctrineDBALExceptionConvert::do($throwable));
        }
    }

    /**
     * @inheritDoc
     */
    public function transaction(): Promise
    {
        try
        {
            $this->logger->debug('START TRANSACTION');

            $this->connection()->beginTransaction();

            return new Success(new DoctrineDBALTransaction($this->connection(), $this->logger));
        }
            // @codeCoverageIgnoreStart
            /** @noinspection PhpRedundantCatchClauseInspection */
        catch(DBALException $exception)
        {
            return new Failure(DoctrineDBALExceptionConvert::do($exception));
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @inheritDoc
     */
    public function unescapeBinary($payload): string
    {
        /** @var string|resource $payload */

        if(true === \is_resource($payload))
        {
            return \stream_get_contents($payload, -1, 0);
        }

        return $payload;
    }


    /**
     * Get connection instance
     *
     * @return Connection
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\InvalidConfigurationOptions
     */
    private function connection(): Connection
    {
        if(null === $this->connection)
        {
            try
            {
                $this->connection = DriverManager::getConnection(['url' => $this->configuration->originalDSN]);
            }
            catch(\Throwable $throwable)
            {
                throw new InvalidConfigurationOptions($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
            }
        }

        return $this->connection;
    }
}
