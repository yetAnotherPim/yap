<?php

declare(strict_types=1);

namespace App\DemoData\Provider;

use App\DemoData\Definition\FieldTypeDefinitions;
use App\DemoData\Synchronizer\TitleTranslationSynchronizer;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Repository\FieldTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class FieldTypeProvider
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FieldTypeRepository $fieldTypeRepository,
        private readonly TitleTranslationSynchronizer $titleTranslationSynchronizer,
    ) {
    }

    /**
     * @param array<string, Locale> $locales
     *
     * @return array<string, FieldType>
     */
    public function getFieldTypes(array $locales): array
    {
        $fieldTypes = [];

        foreach (FieldTypeDefinitions::FIELD_TYPES as $code => $definition) {
            $fieldType = $this->getOrCreate($code, $definition);

            $this->titleTranslationSynchronizer->synchronize(
                $fieldType,
                $definition['title'],
                $locales,
            );

            $fieldTypes[$code] = $fieldType;
        }

        return $fieldTypes;
    }

    /**
     * @param array{
     *     title: array<string, string>,
     *     inputType: InputType,
     *     filterable: bool,
     *     translatable: bool,
     *     decimalSeparator?: string
     * } $definition
     */
    private function getOrCreate(
        string $code,
        array $definition,
    ): FieldType {
        $fieldType = $this->fieldTypeRepository->findOneBy([
            'code' => $code,
        ]);

        if (!$fieldType instanceof FieldType) {
            $fieldType = new FieldType()
                ->setCode($code);

            $this->entityManager->persist($fieldType);
        }

        $fieldType
            ->setTitle($definition['title']['en_GB'])
            ->setInputType($definition['inputType'])
            ->setFilterable($definition['filterable'])
            ->setTranslatable($definition['translatable'])
            ->setDecimalSeparator(
                $definition['decimalSeparator'] ?? null,
            );

        return $fieldType;
    }
}
