<?php

declare(strict_types=1);

namespace App\DemoData\Generator;

use App\DemoData\Provider\FieldTypeProvider;
use App\DemoData\Provider\IdentifierTypeProvider;
use App\DemoData\Provider\LocaleProvider;
use App\DemoData\Provider\ProductTypeProvider;
use Doctrine\ORM\EntityManagerInterface;

class DemoDataGenerator
{
    private const int BATCH_SIZE = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LocaleProvider $localeProvider,
        private readonly IdentifierTypeProvider $identifierTypeProvider,
        private readonly FieldTypeProvider $fieldTypeProvider,
        private readonly ProductTypeProvider $productTypeProvider,
        private readonly ProductGenerator $productGenerator,
    ) {
    }

    /**
     * @param callable(int): void|null $onProgress
     */
    public function generate(
        int $count,
        ?callable $onProgress = null,
    ): void {
        if ($count < 1) {
            throw new \InvalidArgumentException('The product count must be greater than zero.');
        }

        for ($offset = 0; $offset < $count; $offset += self::BATCH_SIZE) {
            $batchCount = min(self::BATCH_SIZE, $count - $offset);

            $locales = $this->localeProvider->getLocales();

            $identifierTypes = $this->identifierTypeProvider
                ->getIdentifierTypes();

            $fieldTypes = $this->fieldTypeProvider
                ->getFieldTypes($locales);

            $productTypes = $this->productTypeProvider->getProductTypes(
                $locales,
                $fieldTypes,
            );

            $this->productGenerator->generate(
                $batchCount,
                $offset,
                $productTypes,
                $fieldTypes,
                $identifierTypes,
                $locales,
                $onProgress,
            );

            $this->entityManager->flush();

            // Release all managed entities, including fields and translations.
            $this->entityManager->clear();
        }
    }
}
