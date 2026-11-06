<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Generator;

use App\DemoData\Definition\ProductDefinitions;
use App\DemoData\Generator\ProductVariationGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductVariationGenerator::class)]
class ProductVariationGeneratorTest extends MockeryTestCase
{
    private ProductVariationGenerator $productVariationGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productVariationGenerator = new ProductVariationGenerator();
    }

    public function testGenerateReturnsValuesAndTranslations(): void
    {
        $result = $this->productVariationGenerator->generate('apparel');

        $this->assertArrayHasKey('values', $result);
        $this->assertArrayHasKey('translations', $result);

        $this->assertIsArray($result['values']);
        $this->assertIsArray($result['translations']);
    }

    public function testGenerateMergesDefaultValues(): void
    {
        foreach (ProductDefinitions::PRODUCTS as $typeCode => $definition) {
            $result = $this->productVariationGenerator->generate($typeCode);

            foreach ($definition['defaults']['values'] as $fieldCode => $value) {
                $this->assertArrayHasKey($fieldCode, $result['values']);
            }
        }
    }

    public function testGenerateMergesDefaultTranslations(): void
    {
        foreach (ProductDefinitions::PRODUCTS as $typeCode => $definition) {
            $result = $this->productVariationGenerator->generate($typeCode);

            foreach ($definition['defaults']['translations'] as $fieldCode => $translations) {
                foreach ($translations as $localeCode => $translation) {
                    $this->assertArrayHasKey(
                        $localeCode,
                        $result['translations'][$fieldCode],
                    );

                    $this->assertSame(
                        $translation,
                        $result['translations'][$fieldCode][$localeCode],
                    );
                }
            }
        }
    }

    public function testGenerateReturnsCompleteProductDataForAllProductTypes(): void
    {
        foreach (ProductDefinitions::PRODUCTS as $typeCode => $definition) {
            $result = $this->productVariationGenerator->generate($typeCode);

            $this->assertNotEmpty($result['values']['name']);

            foreach ($result['values'] as $value) {
                $this->assertIsString($value);
            }

            foreach ($result['translations'] as $localeTranslations) {
                $this->assertIsArray($localeTranslations);

                foreach ($localeTranslations as $translation) {
                    $this->assertIsString($translation);
                }
            }
        }
    }

    public function testGenerateSetsProductNameAndTranslations(): void
    {
        foreach (ProductDefinitions::PRODUCTS as $typeCode => $definition) {
            $result = $this->productVariationGenerator->generate($typeCode);

            $this->assertNotEmpty($result['values']['name']);

            foreach ($result['translations']['name'] ?? [] as $name) {
                $this->assertNotEmpty($name);
            }
        }
    }

    public function testGenerateWorksForAllProductTypes(): void
    {
        foreach (ProductDefinitions::PRODUCTS as $typeCode => $definition) {
            $result = $this->productVariationGenerator->generate($typeCode);

            $this->assertArrayHasKey('values', $result);
            $this->assertArrayHasKey('translations', $result);
            $this->assertNotEmpty($result['values']);
        }
    }
}
