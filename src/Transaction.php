<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage common parts
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Storage\Common;

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
     * @throws \ServiceBus\Storage\Common\Exceptions\ConnectionFailed
     * @throws \ServiceBus\Storage\Common\Exceptions\UniqueConstraintViolationCheckFailed
     * @throws \ServiceBus\Storage\Common\Exceptions\StorageInteractingFailed
     */
    public function commit(): Promise;

    /**
     * Rollback transaction
     *
     * @return Promise It does not return any result
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\ConnectionFailed
     * @throws \ServiceBus\Storage\Common\Exceptions\StorageInteractingFailed
     */
    public function rollback(): Promise;
}
