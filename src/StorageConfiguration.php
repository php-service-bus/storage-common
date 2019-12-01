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

use ServiceBus\Storage\Common\Exceptions\InvalidConfigurationOptions;

/**
 * Adapter configuration for storage.
 *
 * @psalm-readonly
 */
final class StorageConfiguration
{
    /**
     * Original DSN.
     */
    public string $originalDSN;

    /**
     * Scheme.
     */
    public ?string $scheme;

    /**
     * Database host.
     */
    public ?string $host;

    /**
     * Database port.
     */
    public ?int $port;

    /**
     * Database user.
     */
    public ?string $username;

    /**
     * Database user password.
     */
    public ?string $password;

    /**
     * Database name.
     */
    public ?string $databaseName;

    /**
     * Connection encoding.
     */
    public string $encoding;

    /**
     * All query parameters.
     */
    public array $queryParameters = [];

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

        /**
         * @psalm-var array{
         *    scheme:string|null,
         *    host:string|null,
         *    port:int|null,
         *    user:string|null,
         *    pass:string|null,
         *    path:string|null
         * }|null|false $parsedDSN
         *
         * @var array|false|null $parsedDSN
         */
        $parsedDSN = \parse_url((string) $preparedDSN);

        // @codeCoverageIgnoreStart
        if (false === \is_array($parsedDSN))
        {
            throw new InvalidConfigurationOptions('Error while parsing connection DSN');
        }
        // @codeCoverageIgnoreEnd

        $queryString = 'charset=UTF-8';

        if (true === isset($parsedDSN['query']) && '' !== $parsedDSN['query'])
        {
            $queryString = (string) $parsedDSN['query'];
        }

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
     * Has specified credentials.
     */
    public function hasCredentials(): bool
    {
        return '' !== (string) $this->username || '' !== (string) $this->password;
    }
}
