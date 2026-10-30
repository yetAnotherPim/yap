<?php

declare(strict_types=1);

namespace App\Setup\Database\Connection\Enum;

enum DbType: string
{
    case PostgreSQL = 'postgresql';

    public function getDefaultPort(): int
    {
        return match ($this) {
            self::PostgreSQL => 5432,
            // self::MySQL => 3306,
        };
    }

    public function getDefaultCharset(): string
    {
        return match ($this) {
            self::PostgreSQL => 'utf8',
            // self::MySQL => 'utf8mb4',
        };
    }

    /** @return 'pdo_pgsql'|'pdo_mysql' */
    public function getDriver(): string
    {
        return match ($this) {
            self::PostgreSQL => 'pdo_pgsql',
            // self::MySQL => 'pdo_mysql',
        };
    }
}
