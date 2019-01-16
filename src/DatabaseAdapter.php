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
 * Interface adapter for working with the database
 */
interface DatabaseAdapter extends QueryExecutor, BinaryDataDecoder
{
    /**
     * Start transaction
     *
     * @return Promise<\Desperado\ServiceBus\Storage\Transaction>
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\InvalidConfigurationOptions
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ConnectionFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\UniqueConstraintViolationCheckFailed
     * @throws \Desperado\ServiceBus\Storage\Exceptions\StorageInteractingFailed
     */
    public function transaction(): Promise;
}
