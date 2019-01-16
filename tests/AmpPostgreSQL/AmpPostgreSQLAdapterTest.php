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
use Desperado\ServiceBus\Storage\Tests\BaseStorageAdapterTest;

/**
 *
 */
final class AmpPostgreSQLAdapterTest extends BaseStorageAdapterTest
{
    /**
     * @var AmpPostgreSQLAdapter
     */
    private static $adapter;

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        wait(
            static::getAdapter()->execute(
                'CREATE TABLE IF NOT EXISTS test_ai (id serial PRIMARY KEY, value VARCHAR)'
            )
        );
    }

    /**
     * @return void
     *
     * @throws \Throwable
     */
    public static function tearDownAfterClass(): void
    {
        $adapter = static::getAdapter();

        wait($adapter->execute('DROP TABLE storage_test_table'));
        wait($adapter->execute('DROP TABLE test_ai'));

        self::$adapter = null;
    }

    /**
     * @inheritdoc
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

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function lastInsertId(): void
    {
        $adapter = static::getAdapter();

        /** @var \Desperado\ServiceBus\Storage\ResultSet $result */
        $result = wait($adapter->execute('INSERT INTO test_ai (value) VALUES (\'qwerty\') RETURNING id'));

        static::assertEquals('1', $result->lastInsertId());

        /** @var \Desperado\ServiceBus\Storage\ResultSet $result */
        $result = wait($adapter->execute('INSERT INTO test_ai (value) VALUES (\'qwerty\') RETURNING id'));

        static::assertEquals('2', $result->lastInsertId());
    }

    /**
     * @test
     * @expectedException \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function failedConnection(): void
    {
        $adapter = new AmpPostgreSQLAdapter(
            StorageConfiguration::fromDSN('qwerty')
        );

        wait($adapter->execute('SELECT now()'));
    }
}
