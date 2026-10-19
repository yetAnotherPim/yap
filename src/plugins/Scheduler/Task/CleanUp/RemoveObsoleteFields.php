<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Task\CleanUp;

use App\Entity\Product\Field\Field;
use App\plugins\Scheduler\Task\AbstractTask;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask('*/15 * * * *')]
class RemoveObsoleteFields extends AbstractTask
{
    private const int LIMIT = 25;

    public function run(): void
    {
        /** @var list<Field> $fields */
        $fields = $this->entityManager
            ->createQueryBuilder()
            ->select('field')
            ->from(Field::class, 'field')
            ->join('field.product', 'product')
            ->join('product.productType', 'productType')
            ->join('field.type', 'fieldType')
            ->leftJoin(
                'productType.productTypeFieldConfigurations',
                'configuration',
                'WITH',
                'configuration.fieldType = fieldType',
            )
            ->where('configuration.id IS NULL')
            ->orderBy('field.createdAt', 'ASC')
            ->setMaxResults(self::LIMIT)
            ->getQuery()
            ->getResult();

        foreach ($fields as $field) {
            $this->entityManager->remove($field);
        }

        $this->entityManager->flush();
    }
}
