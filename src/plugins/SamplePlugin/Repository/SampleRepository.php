<?php

declare(strict_types=1);

namespace App\plugins\SamplePlugin\Repository;

use App\plugins\SamplePlugin\Entity\SampleEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SampleEntity>
 */
class SampleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SampleEntity::class);
    }
}
