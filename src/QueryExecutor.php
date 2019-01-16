<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage;

use Amp\Promise;

/**
 * Query execution interface
 */
interface QueryExecutor
{
    /**
     * Execute query
     *
     * @param string $queryString
     * @param array  $parameters
     *
     * @return Promise<\Desperado\ServiceBus\Storage\ResultSet>
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\InvalidConfigurationOptions
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\UniqueConstraintViolationCheckFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function execute(string $queryString, array $parameters = []): Promise;
}
