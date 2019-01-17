<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

use Amp\Loop;
use Desperado\ServiceBus\Storage\AmpPosgreSQL\AmpPostgreSQLAdapter;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use function Desperado\ServiceBus\Storage\updateQuery;

include __DIR__ . '/../vendor/autoload.php';

$adapter = new AmpPostgreSQLAdapter(
    StorageConfiguration::fromDSN('pgsql://postgres:123456789@localhost:5432/test')
);

Loop::run(
    static function() use ($adapter): \Generator
    {
        $firstUpdateQuery  = updateQuery('some_table', ['key' => 'value'])->compile();
        $secondUpdateQuery = updateQuery('some_another_table', ['key2' => 'value2'])->compile();

        /** @var \Desperado\ServiceBus\Storage\Transaction $transaction */
        $transaction = yield $adapter->transaction();

        try
        {
            yield $transaction->execute($firstUpdateQuery->sql(), $firstUpdateQuery->params());
            yield $transaction->execute($secondUpdateQuery->sql(), $secondUpdateQuery->params());

            yield $transaction->commit();
        }
        catch(\Throwable $throwable)
        {
            yield $transaction->rollback();
        }
    }
);
