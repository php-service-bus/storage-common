<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\DoctrineDBAL;

use Doctrine\DBAL\Exception as DoctrineDBALExceptions;
use Desperado\ServiceBus\Storage\Exceptions as InternalExceptions;

/**
 * Convert library exceptions to internal types
 *
 * @internal
 */
final class DoctrineDBALExceptionConvert
{
    /**
     * Convert Doctrine DBAL exceptions
     *
     * @param \Throwable $throwable
     *
     * @return InternalExceptions\ConnectionFailed|InternalExceptions\UniqueConstraintViolationCheckFailed|InternalExceptions\StorageInteractingFailed
     */
    public static function do(\Throwable $throwable): \Exception
    {
        $message = \str_replace(\PHP_EOL, '', $throwable->getMessage());

        if($throwable instanceof DoctrineDBALExceptions\ConnectionException)
        {
            return new InternalExceptions\ConnectionFailed($message, (int) $throwable->getCode(), $throwable);
        }

        if($throwable instanceof DoctrineDBALExceptions\UniqueConstraintViolationException)
        {
            return new InternalExceptions\UniqueConstraintViolationCheckFailed($message, (int) $throwable->getCode(), $throwable);
        }

        return new InternalExceptions\StorageInteractingFailed($message, (int) $throwable->getCode(), $throwable);
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {

    }
}
