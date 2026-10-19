<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

trait DatabaseTrait
{
    protected EntityManagerInterface $entityManager;

    /**
     * @throws Exception
     */
    public function tableExists(string $tableName): bool
    {
        $query = <<<SQL
            SELECT * FROM information_schema.tables WHERE table_name = ?;
        SQL;

        return 1 === $this->entityManager
            ->getConnection()
            ->executeQuery($query, [$tableName])
            ->rowCount();
    }

    /**
     * @throws Exception
     */
    public function columnExists(string $tableName, string $columnName): bool
    {
        $query = <<<SQL
            SELECT * FROM information_schema.columns
            WHERE table_name = ? AND column_name = ?;
        SQL;

        return 1 === $this->entityManager
            ->getConnection()
            ->executeQuery($query, [$tableName, $columnName])
            ->rowCount();
    }
}
