<?php

declare(strict_types=1);

namespace App\Tests\unit\Setup\Database\Connection;

use App\Setup\Database\Connection\Connection;
use App\Setup\Database\Connection\Enum\DbType;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(Connection::class)]
#[UsesClass(DbType::class)]
class ConnectionTest extends MockeryTestCase
{
    use SetterGetterTrait;

    public function testGetDatabaseUrl(): void
    {
        $type = DbType::PostgreSQL;

        $connection = (new Connection())
            ->setType($type)
            ->setHost('127.0.0.1')
            ->setPort(5432)
            ->setDbname('my database')
            ->setUser('app user')
            ->setPassword('p@ss:word')
            ->setVersion('16.4');

        $this->assertSame(
            \sprintf(
                'postgresql://app%%20user:p%%40ss%%3Aword@127.0.0.1:5432/my%%20database?serverVersion=16.4&charset=%s',
                $type->getDefaultCharset(),
            ),
            $connection->getDatabaseUrl(),
        );
    }

    /**
     * @param ?class-string $expectedException
     */
    #[DataProvider('setterGetterProvider')]
    public function testSetterGetter(
        string $setter,
        string $getter,
        mixed $value,
        ?string $expectedException = null,
    ): void {
        $this->performSetterGetterTesting(
            $setter,
            $getter,
            $value,
            $expectedException,
        );
    }

    protected function getTargetClass(): string
    {
        return Connection::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setType', 'getType', DbType::PostgreSQL],
            ['setType', 'getType', null, \TypeError::class],

            ['setHost', 'getHost', 'localhost'],
            ['setHost', 'getHost', null, \TypeError::class],

            ['setPort', 'getPort', 5432],
            ['setPort', 'getPort', '5432'],
            ['setPort', 'getPort', null, \TypeError::class],

            ['setDbname', 'getDbname', 'app'],
            ['setDbname', 'getDbname', null, \TypeError::class],

            ['setUser', 'getUser', 'app'],
            ['setUser', 'getUser', null, \TypeError::class],

            ['setPassword', 'getPassword', 'secret'],
            ['setPassword', 'getPassword', null, \TypeError::class],

            ['setVersion', 'getVersion', '16.4'],
            ['setVersion', 'getVersion', null, \TypeError::class],
        ];
    }
}
