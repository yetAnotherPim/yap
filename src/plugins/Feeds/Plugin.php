<?php

declare(strict_types=1);

namespace App\plugins\Feeds;

use App\PluginManager\Plugin\PluginInterface;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;

class Plugin implements PluginInterface
{
    public function getName(): string
    {
        return 'Feeds';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * @throws Exception
     */
    public function activate(EntityManagerInterface $entityManager): static
    {
        $platform = $entityManager->getConnection()->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $query = <<<SQL
            CREATE TABLE IF NOT EXISTS feed
            (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                product_filters JSON DEFAULT NULL,
                filename VARCHAR(255) NOT NULL,
                head TEXT DEFAULT NULL,
                content TEXT DEFAULT NULL,
                footer TEXT DEFAULT NULL,
                latest TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id)
            )
            SQL;
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            $query = <<<SQL
            CREATE TABLE IF NOT EXISTS feed
            (
                id BINARY(16) NOT NULL,
                name VARCHAR(255) NOT NULL,
                product_filters JSON DEFAULT NULL,
                filename VARCHAR(255) NOT NULL,
                head LONGTEXT DEFAULT NULL,
                content LONGTEXT DEFAULT NULL,
                footer LONGTEXT DEFAULT NULL,
                latest DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
            SQL;
        }

        if (false === isset($query)) {
            throw new ConnectionException('Unknown database platform!');
        }

        $entityManager
            ->getConnection()
            ->executeStatement($query);

        return $this;
    }

    public function deactivate(EntityManagerInterface $entityManager): static
    {
        return $this;
    }

    public function update(string $fromVersion, EntityManagerInterface $entityManager): static
    {
        return $this;
    }
}
