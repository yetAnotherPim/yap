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
        $this->assertSame(3306, DbType::MySQL->getDefaultPort());
        $this->assertSame(3306, DbType::MariaDB->getDefaultPort());
    }

    public function testGetDefaultCharset(): void
    {
        $this->assertSame('utf8', DbType::PostgreSQL->getDefaultCharset());
        $this->assertSame('utf8mb4', DbType::MySQL->getDefaultCharset());
        $this->assertSame('utf8mb4', DbType::MariaDB->getDefaultCharset());
    }

    public function testGetDriver(): void
    {
        $this->assertSame('pdo_pgsql', DbType::PostgreSQL->getDriver());
        $this->assertSame('pdo_mysql', DbType::MySQL->getDriver());
        $this->assertSame('pdo_mysql', DbType::MariaDB->getDriver());
    }

    public function testGetDatabaseUrlScheme(): void
    {
        $this->assertSame('postgresql', DbType::PostgreSQL->getDatabaseUrlScheme());
        $this->assertSame('mysql', DbType::MySQL->getDatabaseUrlScheme());
        $this->assertSame('mysql', DbType::MariaDB->getDatabaseUrlScheme());
    }
}
