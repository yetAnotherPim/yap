<?php

declare(strict_types=1);

namespace App\DemoData\Provider;

use App\DemoData\Definition\ProductTypeDefinitions;
use App\DemoData\Synchronizer\TitleTranslationSynchronizer;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Repository\ProductTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductTypeProvider
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductTypeRepository $productTypeRepository,
        private readonly TitleTranslationSynchronizer $titleTranslationSynchronizer,
    ) {
    }

    /**
     * @param array<string, Locale>    $locales
     * @param array<string, FieldType> $fieldTypes
     *
     * @return array<string, ProductType>
     */
    public function getProductTypes(
        array $locales,
        array $fieldTypes,
    ): array {
        $productTypes = [];

        foreach (ProductTypeDefinitions::PRODUCT_TYPES as $code => $definition) {
            $productType = $this->getOrCreate($code, $definition);

            $this->titleTranslationSynchronizer->synchronize(
                $productType,
                $definition['title'],
                $locales,
            );

            $this->synchronizeFieldConfigurations(
                $productType,
                $definition['fields'],
                $fieldTypes,
            );

            $productTypes[$code] = $productType;
        }

        return $productTypes;
    }

    /**
     * @param array{
     *     title: array<string, string>,
     *     fields: array<string, array{
     *         required?: bool,
     *         options?: list<string>
     *     }>
     * } $definition
     */
    private function getOrCreate(
        string $code,
        array $definition,
    ): ProductType {
        $productType = $this->productTypeRepository->findOneBy([
            'code' => $code,
        ]);

        if (!$productType instanceof ProductType) {
            $productType = new ProductType()
                ->setCode($code);

            $this->entityManager->persist($productType);
        }

        $productType->setTitle($definition['title']['en_GB']);

        return $productType;
    }

    /**
     * @param array<string, array{
     *     required?: bool,
     *     options?: list<string>
     * }> $fieldDefinitions
     * @param array<string, FieldType> $fieldTypes
     */
    private function synchronizeFieldConfigurations(
        ProductType $productType,
        array $fieldDefinitions,
        array $fieldTypes,
    ): void {
        foreach ($fieldDefinitions as $fieldCode => $definition) {
            if (!isset($fieldTypes[$fieldCode])) {
                throw new \RuntimeException(\sprintf('FieldType "%s" required by ProductType "%s" does not exist.', $fieldCode, $productType->getCode()));
            }

            $fieldType = $fieldTypes[$fieldCode];

            $configuration = $productType
                ->getProductTypeFieldConfigurations()
                ->findFirst(
                    static fn (
                        int $key,
                        ProductTypeFieldConfiguration $configuration,
                    ): bool => $configuration->getFieldType()?->getCode() === $fieldCode,
                );

            if (null === $configuration) {
                $configuration = new ProductTypeFieldConfiguration()
                    ->setFieldType($fieldType);

                $productType->addProductTypeFieldConfiguration(
                    $configuration,
                );
            }

            /** @var int $position */
            $position = array_search($fieldCode, array_keys($fieldDefinitions), true);

            $configuration
                ->setPosition($position)
                ->setRequired($definition['required'] ?? false)
                ->setOptions($definition['options'] ?? []);
        }
    }
}
