<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

use Desperado\ServiceBus\Storage\AmpPosgreSQL\AmpPostgreSQLAdapter;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use Amp\Loop;
use function Desperado\ServiceBus\Storage\selectQuery;
use function Desperado\ServiceBus\Storage\fetchAll;

include __DIR__ . '/../vendor/autoload.php';

$adapter = new AmpPostgreSQLAdapter(
    StorageConfiguration::fromDSN('pgsql://postgres:123456789@localhost:5432/test')
);

include __DIR__ . '/../vendor/autoload.php';

$adapter = new AmpPostgreSQLAdapter(
    StorageConfiguration::fromDSN('pgsql://postgres:123456789@localhost:5432/test')
);

Loop::run(
    static function() use ($adapter): \Generator
    {
        $listEntriesQuery = selectQuery('companies')->compile();

        /** @var \Desperado\ServiceBus\Storage\ResultSet $resultSet */
        $resultSet = yield $adapter->execute($listEntriesQuery->sql(), $listEntriesQuery->params());

        /** @var array $collection */
        $collection = yield fetchAll($resultSet);

        /** @noinspection ForgottenDebugOutputInspection */
        print_r($collection);
    }
);
