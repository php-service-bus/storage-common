<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\Tests\AmpPostgreSQL;

use function Amp\Promise\wait;
use Desperado\ServiceBus\Storage\AmpPosgreSQL\AmpPostgreSQLAdapter;
use Desperado\ServiceBus\Storage\DatabaseAdapter;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use Desperado\ServiceBus\Storage\Tests\BaseTransactionTest;

/**
 *
 */
final class AmpPostgreSQLTransactionTest extends BaseTransactionTest
{
    /**
     * @var AmpPostgreSQLAdapter
     */
    private static $adapter;

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $adapter = static::getAdapter();

        wait(
            $adapter->execute(
                'CREATE TABLE IF NOT EXISTS test_result_set (id uuid PRIMARY KEY, value bytea)'
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $adapter = static::getAdapter();

        wait(
            $adapter->execute('DROP TABLE test_result_set')
        );
    }

    /**
     * @return DatabaseAdapter
     */
    protected static function getAdapter(): DatabaseAdapter
    {
        if(null === self::$adapter)
        {
            self::$adapter = new AmpPostgreSQLAdapter(
                StorageConfiguration::fromDSN((string) \getenv('TEST_POSTGRES_DSN'))
            );
        }

        return self::$adapter;
    }
}
