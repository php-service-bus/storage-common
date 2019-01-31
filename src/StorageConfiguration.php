<?php

/**
 * Common storage parts
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Storage\Common;

use ServiceBus\Storage\Common\Exceptions\InvalidConfigurationOptions;

/**
 * Adapter configuration for storage
 */
final class StorageConfiguration
{
    /**
     * Original DSN
     *
     * @var string
     */
    public $originalDSN;

    /**
     * Scheme
     *
     * @var string|null
     */
    public $scheme;

    /**
     * Database host
     *
     * @var string|null
     */
    public $host;

    /**
     * Database port
     *
     * @var int|null
     */
    public $port;

    /**
     * Database user
     *
     * @var string|null
     */
    public $username;

    /**
     * Database user password
     *
     * @var string|null
     */
    public $password;

    /**
     * Database name
     *
     * @var string|null
     */
    public $databaseName;

    /**
     * Connection encoding
     *
     * @var string
     */
    public $encoding;

    /**
     * All query parameters
     *
     * @var array
     */
    public $queryParameters = [];

    /**
     * @param string $connectionDSN DSN examples:
     *                              - inMemory: sqlite:///:memory:
     *                              - AsyncPostgreSQL: pgsql://user:password@host:port/database
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\InvalidConfigurationOptions
     */
    public function __construct(string $connectionDSN)
    {
        $preparedDSN = \preg_replace('#^((?:pdo_)?sqlite3?):///#', '$1://localhost/', $connectionDSN);

        /** @var array|null|false $parsedDSN */
        $parsedDSN = \parse_url((string) $preparedDSN);

        // @codeCoverageIgnoreStart
        if(null !== $parsedDSN && false === \is_array($parsedDSN))
        {
            throw new InvalidConfigurationOptions('Error while parsing connection DSN');
        }
        // @codeCoverageIgnoreEnd

        /**
         * @var array{
         *    scheme:string|null,
         *    host:string|null,
         *    port:int|null,
         *    user:string|null,
         *    pass:string|null,
         *    path:string|null
         * } $parsedDSN
         */

        $queryString = (string) ($parsedDSN['query'] ?? 'charset=UTF-8');

        \parse_str($queryString, $this->queryParameters);

        /** @var array{charset:string|null, max_connections:int|null, idle_timeout:int|null} $queryParameters */
        $queryParameters = $this->queryParameters;

        $this->originalDSN  = $connectionDSN;
        $this->scheme       = $parsedDSN['scheme'] ?? null;
        $this->host         = $parsedDSN['host'] ?? null;
        $this->port         = $parsedDSN['port'] ?? null;
        $this->username     = $parsedDSN['user'] ?? null;
        $this->password     = $parsedDSN['pass'] ?? null;
        $this->databaseName = $parsedDSN['path'] ? \ltrim((string) $parsedDSN['path'], '/') : null;
        $this->encoding     = $queryParameters['charset'] ?? 'UTF-8';
    }

    /**
     * Has specified credentials
     *
     * @return bool
     */
    public function hasCredentials(): bool
    {
        return '' !== (string) $this->username || '' !== (string) $this->password;
    }
}
