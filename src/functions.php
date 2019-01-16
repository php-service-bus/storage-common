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

use function Amp\call;
use Amp\Promise;
use Desperado\ServiceBus\Storage\Exceptions\IncorrectParameterCast;
use Desperado\ServiceBus\Storage\Exceptions\OneResultExpected;
use Latitude\QueryBuilder\CriteriaInterface;
use Latitude\QueryBuilder\Engine\PostgresEngine;
use Latitude\QueryBuilder\EngineInterface;
use function Latitude\QueryBuilder\field;
use Latitude\QueryBuilder\Query\DeleteQuery;
use Latitude\QueryBuilder\Query\InsertQuery;
use Latitude\QueryBuilder\Query\SelectQuery;
use Latitude\QueryBuilder\Query\UpdateQuery;
use Latitude\QueryBuilder\QueryFactory;

/**
 * Collect iterator data
 * Not recommended for use on large amounts of data
 *
 * @psalm-suppress MixedTypeCoercion Incorrect resolving the value of the promise
 *
 * @param ResultSet $iterator
 *
 * @return Promise<array<int, mixed>|null>
 *
 * @throws \Desperado\ServiceBus\Storage\Exceptions\ResultSetIterationFailed
 */
function fetchAll(ResultSet $iterator): Promise
{
    /** @psalm-suppress InvalidArgument Incorrect psalm unpack parameters (...$args) */
    return call(
    /** @psalm-suppress InvalidReturnType Incorrect resolving the value of the generator */
        static function(ResultSet $iterator): \Generator
        {
            $array = [];

            while(yield $iterator->advance())
            {
                $array[] = $iterator->getCurrent();
            }

            return $array;
        },
        $iterator
    );
}

/**
 * Extract 1 result
 *
 * @psalm-suppress MixedTypeCoercion Incorrect resolving the value of the promise
 *
 * @param ResultSet $iterator
 *
 * @return Promise<array<string, mixed>|null>
 *
 * @throws \Desperado\ServiceBus\Storage\Exceptions\ResultSetIterationFailed
 * @throws \Desperado\ServiceBus\Storage\Exceptions\OneResultExpected The result must contain only 1 row
 */
function fetchOne(ResultSet $iterator): Promise
{
    /** @psalm-suppress InvalidArgument Incorrect psalm unpack parameters (...$args) */
    return call(
        static function(ResultSet $iterator): \Generator
        {
            /** @var array $collection */
            $collection   = yield fetchAll($iterator);
            $resultsCount = \count($collection);

            if(0 === $resultsCount || 1 === $resultsCount)
            {
                /** @var bool|array $endElement */
                $endElement = \end($collection);

                unset($collection);

                return false !== $endElement ? $endElement : null;
            }

            throw new OneResultExpected(
                \sprintf(
                    'A single record was requested, but the result of the query execution contains several ("%d")',
                    $resultsCount
                )
            );
        },
        $iterator
    );
}

/**
 * @param string                  $field
 * @param int|string|float|object $value
 *
 * @return \Latitude\QueryBuilder\CriteriaInterface
 *
 * @throws \Desperado\ServiceBus\Storage\Exceptions\IncorrectParameterCast
 */
function equalsCriteria(string $field, $value): CriteriaInterface
{
    if(true === \is_object($value))
    {
        $value = castObjectToString($value);
    }

    return field($field)->eq($value);
}

/**
 * @param string                  $field
 * @param int|string|float|object $value
 *
 * @return \Latitude\QueryBuilder\CriteriaInterface
 *
 * @throws \Desperado\ServiceBus\Storage\Exceptions\IncorrectParameterCast
 */
function notEqualsCriteria(string $field, $value): CriteriaInterface
{
    if(true === \is_object($value))
    {
        $value = castObjectToString($value);
    }

    return field($field)->notEq($value);
}

/**
 * Create query builder
 *
 * @param EngineInterface|null $engine
 *
 * @return QueryFactory
 */
function queryBuilder(EngineInterface $engine = null): QueryFactory
{
    return new QueryFactory($engine ?? new PostgresEngine());
}

/**
 * Create select query (for PostgreSQL)
 *
 * @noinspection PhpDocSignatureInspection
 *
 * @param string $fromTable
 * @param string ...$columns
 *
 * @return SelectQuery
 */
function selectQuery(string $fromTable, string ...$columns): SelectQuery
{
    return queryBuilder()->select(...$columns)->from($fromTable);
}

/**
 * Create update query (for PostgreSQL)
 *
 * @param string $tableName
 * @param array  $values
 *
 * @return UpdateQuery
 */
function updateQuery(string $tableName, array $values): UpdateQuery
{
    return queryBuilder()->update($tableName, $values);
}

/**
 * Create delete query (for PostgreSQL)
 *
 * @param string $fromTable
 *
 * @return DeleteQuery
 */
function deleteQuery(string $fromTable): DeleteQuery
{
    return queryBuilder()->delete($fromTable);
}

/**
 * Create insert query (for PostgreSQL)
 *
 * @param string                      $toTable
 * @param array<string, mixed>|object $toInsert
 *
 * @return InsertQuery
 */
function insertQuery(string $toTable, $toInsert): InsertQuery
{
    if(true === \is_object($toInsert))
    {
        /** @var object $toInsert */

        $rows = castObjectToArray($toInsert);
    }
    else
    {
        /** @var array $rows */
        $rows = $toInsert;
    }

    return queryBuilder()->insert($toTable, $rows);
}

/**
 * Receive object as array (property/value)
 *
 * @internal
 *
 * @param object $object
 *
 * @return array<string, int|float|null|string>
 *
 * @throws \Desperado\ServiceBus\Storage\Exceptions\IncorrectParameterCast
 */
function castObjectToArray(object $object): array
{
    $result = [];

    /** @var int|float|null|string|object $value */
    foreach(getObjectVars($object) as $key => $value)
    {
        $result[toSnakeCase($key)] = cast($key, $value);
    }

    return $result;
}

/**
 * Gets the properties of the given object
 *
 * @internal
 *
 * @param object $object
 *
 * @return array<string, int|float|null|string|object>
 */
function getObjectVars(object $object): array
{
    $closure = \Closure::bind(
        function(): array
        {
            /**
             * @var object $this
             *
             * @psalm-suppress InvalidScope Closure:bind not supports
             */
            return \get_object_vars($this);
        },
        $object,
        $object
    );

    /** @var array<string, int|float|null|string|object> $vars */
    $vars = $closure();

    return $vars;
}

/**
 * @internal
 *
 * Convert string from lowerCamelCase to snake_case
 *
 * @param string $string
 *
 * @return string
 */
function toSnakeCase(string $string): string
{
    return \strtolower(\preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
}

/**
 * @internal
 *
 * @param string                       $key
 * @param int|float|null|string|object $value
 *
 * @return int|float|null|string
 *
 * @throws \Desperado\ServiceBus\Storage\Exceptions\IncorrectParameterCast
 */
function cast(string $key, $value)
{
    if(null === $value || true === \is_scalar($value))
    {
        return $value;
    }

    /** @psalm-suppress RedundantConditionGivenDocblockType */
    if(true === \is_object($value))
    {
        return castObjectToString($value);
    }

    throw new IncorrectParameterCast(
        \sprintf(
            'The "%s" property must contain a scalar value. "%s" given',
            $key,
            \gettype($value)
        )
    );
}

/**
 * Cast object to string
 *
 * @internal
 *
 * @param object $object
 *
 * @return string
 *
 * @throws \Desperado\ServiceBus\Storage\Exceptions\IncorrectParameterCast
 */
function castObjectToString(object $object): string
{
    if(true === \method_exists($object, '__toString'))
    {
        /** @psalm-suppress InvalidCast Object have __toString method */
        return (string) $object;
    }

    throw new IncorrectParameterCast(
        \sprintf('"%s" must implements "__toString" method', \get_class($object))
    );
}
