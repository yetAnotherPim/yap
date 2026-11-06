<?php

declare(strict_types=1);

namespace App\DemoData\Generator;

use App\DemoData\Definition\ProductDefinitions;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use Doctrine\ORM\EntityManagerInterface;

class ProductGenerator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductVariationGenerator $productVariationGenerator,
    ) {
    }

    /**
     * @param array<string, ProductType>    $productTypes
     * @param array<string, FieldType>      $fieldTypes
     * @param array<string, IdentifierType> $identifierTypes
     * @param array<string, Locale>         $locales
     * @param callable(int): void|null      $onProgress
     */
    public function generate(
        int $count,
        int $offset,
        array $productTypes,
        array $fieldTypes,
        array $identifierTypes,
        array $locales,
        ?callable $onProgress = null,
    ): void {
        /** @var list<key-of<ProductDefinitions::PRODUCTS>> $typeCodes */
        $typeCodes = array_keys(ProductDefinitions::PRODUCTS);

        for ($i = 0; $i < $count; ++$i) {
            $typeCode = $typeCodes[($offset + $i) % \count($typeCodes)];

            if (!isset($productTypes[$typeCode])) {
                throw new \RuntimeException(\sprintf('ProductType "%s" is missing.', $typeCode));
            }

            $this->generateProduct(
                $typeCode,
                $offset + $i + 1,
                $productTypes[$typeCode],
                $fieldTypes,
                $identifierTypes,
                $locales,
            );

            if (null !== $onProgress) {
                $onProgress($offset + $i + 1);
            }
        }
    }

    /**
     * @param key-of<ProductDefinitions::PRODUCTS> $typeCode
     * @param array<string, FieldType>             $fieldTypes
     * @param array<string, IdentifierType>        $identifierTypes
     * @param array<string, Locale>                $locales
     */
    private function generateProduct(
        string $typeCode,
        int $counter,
        ProductType $productType,
        array $fieldTypes,
        array $identifierTypes,
        array $locales,
    ): void {
        $definition = $this->productVariationGenerator->generate($typeCode);

        $product = new Product()
            ->setProductType($productType);

        foreach ($definition['values'] as $fieldCode => $value) {
            if (!isset($fieldTypes[$fieldCode])) {
                throw new \RuntimeException(\sprintf('FieldType "%s" is missing.', $fieldCode));
            }

            $field = new Field()
                ->setType($fieldTypes[$fieldCode])
                ->setValue($value);

            $product->addField($field);

            foreach (
                $definition['translations'][$fieldCode] ?? [] as $localeCode => $translatedValue
            ) {
                if (!isset($locales[$localeCode])) {
                    continue;
                }

                $translation = new FieldValueTranslation()
                    ->setLocale($locales[$localeCode])
                    ->setValue($translatedValue);

                $field->addTranslation($translation);
            }
        }

        $this->addIdentifier(
            $product,
            $identifierTypes['sku'],
            $this->generateSku($counter),
        );

        $this->addIdentifier(
            $product,
            $identifierTypes['gtin'],
            $this->generateGtin(),
        );

        $this->entityManager->persist($product);
    }

    private function addIdentifier(
        Product $product,
        IdentifierType $identifierType,
        string $value,
    ): void {
        $identifier = new Identifier()
            ->setType($identifierType)
            ->setValue($value);

        $product->addIdentifier($identifier);
    }

    private function generateSku(int $counter): string
    {
        do {
            $sku = \sprintf(
                'DEMO-%s-%05d',
                new \DateTimeImmutable()->format('YmdHisv'),
                $counter,
            );
        } while ($this->identifierExists($sku));

        return $sku;
    }

    private function generateGtin(): string
    {
        do {
            // Prefix 20 is used here for synthetic internal/demo identifiers.
            $base = '20'.str_pad(
                (string) random_int(0, 9_999_999_999),
                10,
                '0',
                STR_PAD_LEFT,
            );

            $gtin = $base.$this->calculateGtinCheckDigit($base);
        } while ($this->identifierExists($gtin));

        return $gtin;
    }

    private function calculateGtinCheckDigit(string $base): int
    {
        $sum = 0;

        foreach (str_split(strrev($base)) as $position => $digit) {
            $sum += (int) $digit * (0 === $position % 2 ? 3 : 1);
        }

        return (10 - ($sum % 10)) % 10;
    }

    private function identifierExists(string $value): bool
    {
        return null !== $this->entityManager
                ->getRepository(Identifier::class)
                ->findOneBy(['value' => $value]);
    }
}
