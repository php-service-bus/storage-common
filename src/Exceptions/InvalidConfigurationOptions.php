<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage common parts
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Storage\Common\Exceptions;

/**
 *
 */
final class InvalidConfigurationOptions extends \InvalidArgumentException implements StorageExceptionMarker
{

}
