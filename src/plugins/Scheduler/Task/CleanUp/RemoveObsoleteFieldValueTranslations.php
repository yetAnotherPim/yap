<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Task\CleanUp;

use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\plugins\Scheduler\Task\AbstractTask;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask('*/15 * * * *')]
class RemoveObsoleteFieldValueTranslations extends AbstractTask
{
    private const int LIMIT = 25;

    public function run(): void
    {
        /** @var list<FieldValueTranslation> $translations */
        $translations = $this->entityManager
            ->createQueryBuilder()
            ->select('translation')
            ->from(FieldValueTranslation::class, 'translation')
            ->join('translation.field', 'field')
            ->join('field.type', 'fieldType')
            ->where('fieldType.translatable = :translatable')
            ->setParameter('translatable', false)
            ->orderBy('translation.createdAt', 'ASC')
            ->setMaxResults(self::LIMIT)
            ->getQuery()
            ->getResult();

        foreach ($translations as $translation) {
            $this->entityManager->remove($translation);
        }

        $this->entityManager->flush();
    }
}
