<?php

/**
 * Common storage parts.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Storage\Common;

use Amp\Promise;

/**
 * The result of the operation.
 */
interface ResultSet
{
    /**
     * Succeeds with true if an emitted value is available by calling getCurrent() or false if the iterator has
     * resolved. If the iterator fails, the returned promise will fail with the same exception.
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\ResultSetIterationFailed
     *
     * @return Promise<bool>
     */
    public function advance(): Promise;

    /**
     * Gets the last emitted value or throws an exception if the iterator has completed.
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\ResultSetIterationFailed
     *
     * @return array<string, float|int|resource|string|null>|null Value emitted from the iterator
     */
    public function getCurrent(): ?array;

    /**
     * Receive last insert id.
     *
     * @param string $sequence
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\ResultSetIterationFailed
     *
     * @return Promise<int|string|null>
     */
    public function lastInsertId(?string $sequence = null): Promise;

    /**
     * Returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement executed.
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\ResultSetIterationFailed
     *
     * @return int
     */
    public function affectedRows(): int;
}
