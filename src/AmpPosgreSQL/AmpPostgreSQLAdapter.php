<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\AmpPosgreSQL;

use function Amp\call;
use Amp\Postgres\ConnectionConfig;
use Amp\Postgres\Pool;
use function Amp\Postgres\pool;
use Amp\Promise;
use Desperado\ServiceBus\Storage\DatabaseAdapter;
use Desperado\ServiceBus\Storage\Exceptions\InvalidConfigurationOptions;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 *
 */
final class AmpPostgreSQLAdapter implements DatabaseAdapter
{
    /**
     * Connection parameters
     *
     * @var StorageConfiguration
     */
    private $configuration;

    /**
     * Connections pool
     *
     * @var Pool|null
     */
    private $pool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StorageConfiguration $configuration
     * @param LoggerInterface|null $logger
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\InvalidConfigurationOptions
     */
    public function __construct(StorageConfiguration $configuration, ?LoggerInterface $logger = null)
    {
        // @codeCoverageIgnoreStart
        if(false === \extension_loaded('pgsql'))
        {
            throw new InvalidConfigurationOptions('ext-pgsql must be installed');
        }
        // @codeCoverageIgnoreEnd

        $this->configuration = $configuration;
        $this->logger        = $logger ?? new NullLogger();
    }

    public function __destruct()
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType Null in case of error */
        if(null !== $this->pool)
        {
            $this->pool->close();
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(string $queryString, array $parameters = []): Promise
    {
        $connectionsPool = $this->pool();
        $logger          = $this->logger;

        /** @psalm-suppress InvalidArgument Incorrect psalm unpack parameters (...$args) */
        return call(
        /** @psalm-return AmpPostgreSQLResultSet */
            static function(string $queryString, array $parameters = []) use ($connectionsPool, $logger): \Generator
            {
                try
                {
                    $logger->debug($queryString, $parameters);

                    return new AmpPostgreSQLResultSet(
                        yield $connectionsPool->execute($queryString, $parameters)
                    );
                }
                catch(\Throwable $throwable)
                {
                    throw AmpPosgreSQLExceptionConvert::do($throwable);
                }
            },
            $queryString,
            $parameters
        );
    }

    /**
     * @inheritdoc
     */
    public function transaction(): Promise
    {
        $connectionsPool = $this->pool();
        $logger          = $this->logger;

        /** @psalm-suppress InvalidArgument Incorrect psalm unpack parameters (...$args) */
        return call(
            static function() use ($connectionsPool, $logger): \Generator
            {
                try
                {
                    $logger->debug('BEGIN TRANSACTION ISOLATION LEVEL READ COMMITTED');

                    /** @var \Amp\Postgres\Transaction $transaction */
                    $transaction = yield $connectionsPool->beginTransaction();

                    return new AmpPostgreSQLTransaction($transaction, $logger);
                }
                    // @codeCoverageIgnoreStart
                catch(\Throwable $throwable)
                {
                    throw AmpPosgreSQLExceptionConvert::do($throwable);
                }
                // @codeCoverageIgnoreEnd
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function unescapeBinary($payload): string
    {
        if(true === \is_resource($payload))
        {
            $payload = \stream_get_contents($payload, -1, 0);
        }

        /** @var string $payload */

        /** @noinspection PhpComposerExtensionStubsInspection */
        return \pg_unescape_bytea($payload);
    }

    /**
     * Receive connection pool
     *
     * @return Pool
     */
    private function pool(): Pool
    {
        if(null === $this->pool)
        {
            $queryData = $this->configuration->queryParameters;

            $maxConnectionsCount = (int) ($queryData['max_connections'] ?? Pool::DEFAULT_MAX_CONNECTIONS);
            $idleTimeout         = (int) ($queryData['idle_timeout'] ?? Pool::DEFAULT_IDLE_TIMEOUT);

            $this->pool = pool(
                new ConnectionConfig(
                    (string) $this->configuration->host,
                    $this->configuration->port ?? ConnectionConfig::DEFAULT_PORT,
                    (string) $this->configuration->username,
                    (string) $this->configuration->password,
                    (string) $this->configuration->databaseName
                ),
                $maxConnectionsCount,
                $idleTimeout
            );
        }

        return $this->pool;
    }
}
