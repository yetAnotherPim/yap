<?php

declare(strict_types=1);

namespace App\Tests\unit\Setup\Database\Connection\Enum;

use App\Setup\Database\Connection\Enum\DbType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DbType::class)]
class DbTypeTest extends TestCase
{
    public function testGetDefaultPort(): void
    {
        $this->assertSame(5432, DbType::PostgreSQL->getDefaultPort());
    }

    public function testGetDefaultCharset(): void
    {
        $this->assertSame('utf8', DbType::PostgreSQL->getDefaultCharset());
    }

    public function testGetDriver(): void
    {
        $this->assertSame('pdo_pgsql', DbType::PostgreSQL->getDriver());
    }
}
