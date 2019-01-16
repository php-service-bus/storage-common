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
 * Transaction handler
 */
interface Transaction extends QueryExecutor, BinaryDataDecoder
{
    /**
     * Commit transaction
     *
     * @return Promise It does not return any result
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\UniqueConstraintViolationCheckFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function commit(): Promise;

    /**
     * Rollback transaction
     *
     * @return Promise It does not return any result
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function rollback(): Promise;
}
