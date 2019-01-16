<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\Tests;

use function Amp\Promise\wait;
use Desperado\ServiceBus\Storage\DatabaseAdapter;
use function Desperado\ServiceBus\Storage\equalsCriteria;
use function Desperado\ServiceBus\Storage\fetchAll;
use function Desperado\ServiceBus\Storage\fetchOne;
use function Desperado\ServiceBus\Storage\insertQuery;
use function Desperado\ServiceBus\Storage\selectQuery;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

/**
 *
 */
abstract class BaseTransactionTest extends TestCase
{

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $adapter = static::getAdapter();

        wait(
            $adapter->execute('DELETE FROM test_result_set')
        );
    }

    /**
     * Get database adapter
     *
     * @return DatabaseAdapter
     */
    abstract protected static function getAdapter(): DatabaseAdapter;

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function simpleTransaction(): void
    {
        $adapter = static::getAdapter();

        /** @var \Desperado\ServiceBus\Storage\Transaction $transaction */
        $transaction = wait($adapter->transaction());

        wait(
            $adapter->execute(
                'INSERT INTO test_result_set (id, value) VALUES (?,?), (?,?)', [
                    'c072f311-4a0f-4d53-91ea-575b96706eeb', 'value1',
                    '0e6007d9-5386-40ae-a05c-9decec172d60', 'value2'
                ]
            )
        );

        wait($transaction->commit());

        /** check results */

        $result = wait(
            fetchAll(
                wait($adapter->execute('SELECT * FROM test_result_set'))
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
    public function transactionWithReadData(): void
    {
        $adapter = static::getAdapter();

        $uuid = 'cb9f20de-6a8e-4934-84b4-71da78e42697';

        $query = insertQuery('test_result_set', ['id' => $uuid, 'value' => 'value2'])->compile();

        wait($adapter->execute($query->sql(), $query->params()));

        /** @var \Desperado\ServiceBus\Storage\Transaction $transaction */
        $transaction = wait($adapter->transaction());

        $query = selectQuery('test_result_set')
            ->where(equalsCriteria('id', $uuid))
            ->compile();

        $someReadData = wait(fetchOne(wait($transaction->execute($query->sql(), $query->params()))));

        static::assertNotEmpty($someReadData);
        static::assertCount(2, $someReadData);

        wait($transaction->commit());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function rollback(): void
    {
        $adapter = static::getAdapter();

        /** @var \Desperado\ServiceBus\Storage\Transaction $transaction */
        $transaction = wait($adapter->transaction());

        $query = insertQuery(
            'test_result_set',
            ['id' => 'bd561cb9-e745-41fc-9de6-1f41f0665063', 'value' => 'value2']
        )->compile();

        wait($transaction->execute($query->sql(), $query->params()));
        wait($transaction->rollback());

        $query = selectQuery('test_result_set')->compile();

        /** @var array $collection */
        $collection = wait(fetchAll(wait($adapter->execute($query->sql(), $query->params()))));

        static::assertThat($collection, new IsType('array'));
        static::assertCount(0, $collection);
    }
}
