<?php

/**
 * PHP Service Bus storage common parts
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
     * @return void
     *
     * @throws \Throwable
     */
    public function parseSqlite(): void
    {
        $configuration = new StorageConfiguration('sqlite:///:memory:');

        static::assertEquals('sqlite:///:memory:', $configuration->originalDSN);
        static::assertEquals('sqlite', $configuration->scheme);
        static::assertEquals('localhost', $configuration->host);
        static::assertEquals(':memory:', $configuration->databaseName);
        static::assertEquals('UTF-8', $configuration->encoding);
        static::assertFalse($configuration->hasCredentials());
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function parseFullDSN(): void
    {
        $configuration = new StorageConfiguration(
            'pgsql://someUser:someUserPassword@host:54332/databaseName?charset=UTF-16'
        );
        static::assertEquals('pgsql', $configuration->scheme);
        static::assertEquals('host', $configuration->host);
        static::assertEquals(54332, $configuration->port);
        static::assertEquals('databaseName', $configuration->databaseName);
        static::assertEquals('UTF-16', $configuration->encoding);
        static::assertTrue($configuration->hasCredentials());
        static::assertEquals('someUser', $configuration->username);
        static::assertEquals('someUserPassword', $configuration->password);
    }
}
