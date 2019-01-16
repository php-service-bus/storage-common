<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\Tests\DoctrineDBAL;

use function Amp\Promise\wait;
use Desperado\ServiceBus\Storage\DatabaseAdapter;
use Desperado\ServiceBus\Storage\DoctrineDBAL\DoctrineDBALAdapter;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use Desperado\ServiceBus\Storage\Tests\BaseStorageAdapterTest;

/**
 *
 */
final class DoctrineDBALAdapterTest extends BaseStorageAdapterTest
{
    /**
     * @var DoctrineDBALAdapter
     */
    private static $adapter;

    /**
     * @inheritdoc
     */
    protected static function getAdapter(): DatabaseAdapter
    {
        if(null === self::$adapter)
        {
            self::$adapter = new DoctrineDBALAdapter(
                StorageConfiguration::fromDSN('sqlite:///:memory:')
            );
        }

        return self::$adapter;
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        wait(
            static::getAdapter()->execute(
                'CREATE TABLE IF NOT EXISTS test_ai (id serial PRIMARY KEY, value VARCHAR)'
            )
        );
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
        $result = wait($adapter->execute('INSERT INTO test_ai (value) VALUES (\'qwerty\')'));

        static::assertEquals('1', $result->lastInsertId());

        /** @var \Desperado\ServiceBus\Storage\ResultSet $result */
        $result = wait($adapter->execute('INSERT INTO test_ai (value) VALUES (\'qwerty\')'));

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
        $adapter = new DoctrineDBALAdapter(
            StorageConfiguration::fromDSN('pgsql://localhost:4486/foo?charset=UTF-8')
        );

        wait($adapter->execute('SELECT now()'));
    }

    /**
     * @test
     * @expectedException \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function failedConnectionString(): void
    {
        $adapter = new DoctrineDBALAdapter(
            StorageConfiguration::fromDSN('')
        );

        wait($adapter->execute('SELECT now()'));
    }
}
