<?php

/**
 * Common storage parts.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Storage\Common\Tests;

use PHPUnit\Framework\TestCase;
use ServiceBus\Storage\Common\StorageConfiguration;

/**
 *
 */
final class StorageConfigurationTest extends TestCase
{
    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function parseSqlite(): void
    {
        $configuration = new StorageConfiguration('sqlite:///:memory:');

        static::assertSame('sqlite:///:memory:', $configuration->originalDSN);
        static::assertSame('sqlite', $configuration->scheme);
        static::assertSame('localhost', $configuration->host);
        static::assertSame(':memory:', $configuration->databaseName);
        static::assertSame('UTF-8', $configuration->encoding);
        static::assertFalse($configuration->hasCredentials());
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function parseFullDSN(): void
    {
        $configuration = new StorageConfiguration(
            'pgsql://someUser:someUserPassword@host:54332/databaseName?charset=UTF-16'
        );
        static::assertSame('pgsql', $configuration->scheme);
        static::assertSame('host', $configuration->host);
        static::assertSame(54332, $configuration->port);
        static::assertSame('databaseName', $configuration->databaseName);
        static::assertSame('UTF-16', $configuration->encoding);
        static::assertTrue($configuration->hasCredentials());
        static::assertSame('someUser', $configuration->username);
        static::assertSame('someUserPassword', $configuration->password);
    }
}
