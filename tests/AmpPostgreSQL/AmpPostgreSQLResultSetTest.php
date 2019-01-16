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
use function Desperado\ServiceBus\Storage\fetchAll;
use function Desperado\ServiceBus\Storage\fetchOne;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

/**
 *
 */
final class AmpPostgreSQLResultSetTest extends TestCase
{
    /**
     * @var AmpPostgreSQLAdapter
     */
    private $adapter;

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new AmpPostgreSQLAdapter(
            StorageConfiguration::fromDSN((string) \getenv('TEST_POSTGRES_DSN'))
        );

        wait(
            $this->adapter->execute(
                'CREATE TABLE IF NOT EXISTS test_result_set (id uuid PRIMARY KEY, value VARCHAR)'
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        wait(
            $this->adapter->execute('DROP TABLE test_result_set')
        );
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function fetchOne(): void
    {
        $uuid1 = '3b5f80dd-0d14-4f8e-9684-0320dc35d3fd';
        $uuid2 = 'ad1278ad-031a-45e0-aa04-2a03e143d438';

        $promise = $this->adapter->execute(
            'INSERT INTO test_result_set (id, value) VALUES (?,?), (?,?)', [
                $uuid1, 'value1',
                $uuid2, 'value2'
            ]
        );

        wait($promise);

        $result = wait(
            fetchOne(
                wait($this->adapter->execute(\sprintf('SELECT * FROM test_result_set WHERE id = \'%s\'', $uuid2)))
            )
        );

        static::assertNotEmpty($result);
        static:: assertEquals(['id' => $uuid2, 'value' => 'value2'], $result);

        $result = wait(
            fetchOne(
                wait(
                    $this->adapter->execute('SELECT * FROM test_result_set WHERE id = \'b4141f6e-a461-11e8-98d0-529269fb1459\'')
                )
            )
        );

        static::assertNull($result);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function fetchAll(): void
    {
        $promise = $this->adapter->execute(
            'INSERT INTO test_result_set (id, value) VALUES (?,?), (?,?)', [
                'b922bda9-d2e5-4b41-b30d-e3b9a3717753', 'value1',
                '3fdbbc08-c6bd-4fd9-b343-1c069c0d3044', 'value2'
            ]
        );

        wait($promise);

        $result = wait(
            fetchAll(
                wait($this->adapter->execute('SELECT * FROM test_result_set'))
            )
        );

        static::assertNotEmpty($result);
        static::assertCount(2, $result);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function fetchAllWithEmptySet(): void
    {
        $result = wait(
            fetchAll(
                wait($this->adapter->execute('SELECT * FROM test_result_set'))
            )
        );

        static::assertThat($result, new IsType('array'));
        static::assertEmpty($result);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function multipleGetCurrentRow(): void
    {
        $promise = $this->adapter->execute(
            'INSERT INTO test_result_set (id, value) VALUES (?,?), (?,?)', [
                '457e634c-6fef-4144-a5e4-76def3f51c10', 'value1',
                'f4edd226-6fbf-499d-b6c4-b419560a7291', 'value2'
            ]
        );

        wait($promise);

        /** @var \Desperado\ServiceBus\Storage\ResultSet $result */
        $result = wait($this->adapter->execute('SELECT * FROM test_result_set'));

        while(wait($result->advance()))
        {
            $row     = $result->getCurrent();
            $rowCopy = $result->getCurrent();

            static::assertEquals($row, $rowCopy);
        }
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function executeCommand(): void
    {
        /** @var \Desperado\ServiceBus\Storage\ResultSet $result */
        $result = wait($this->adapter->execute('DELETE FROM test_result_set'));

        while(wait($result->advance()))
        {
            static::fail('Non empty cycle');
        }

        static::assertNull($result->lastInsertId());
    }
}
