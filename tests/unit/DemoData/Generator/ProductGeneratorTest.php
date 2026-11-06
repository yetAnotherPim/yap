<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Generator;

use App\DemoData\Definition\ProductDefinitions;
use App\DemoData\Generator\ProductGenerator;
use App\DemoData\Generator\ProductVariationGenerator;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ProductGenerator::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldValueTranslation::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(Product::class)]
class ProductGeneratorTest extends MockeryTestCase
{
    /** @var EntityManagerInterface&MockInterface */
    private EntityManagerInterface $entityManager;

    /** @var ProductVariationGenerator&MockInterface */
    private ProductVariationGenerator $productVariationGenerator;

    private ProductGenerator $productGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->productVariationGenerator = \Mockery::mock(ProductVariationGenerator::class);

        $this->productGenerator = new ProductGenerator(
            $this->entityManager,
            $this->productVariationGenerator,
        );
    }

    public function testGenerateDoesNothingWhenCountIsZero(): void
    {
        $this->productVariationGenerator
            ->shouldNotReceive('generate');

        $this->entityManager
            ->shouldNotReceive('persist');

        $this->productGenerator->generate(
            0,
            0,
            [],
            [],
            [],
            [],
        );
    }

    public function testGenerateThrowsExceptionWhenProductTypeIsMissing(): void
    {
        $this->productVariationGenerator
            ->shouldNotReceive('generate');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'ProductType "%s" is missing.',
                array_key_first(ProductDefinitions::PRODUCTS),
            ),
        );

        $this->productGenerator->generate(
            1,
            0,
            [],
            [],
            [],
            [],
        );
    }

    public function testGenerateCreatesProduct(): void
    {
        $typeCode = array_key_first(ProductDefinitions::PRODUCTS);

        $productType = \Mockery::mock(ProductType::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $skuType = \Mockery::mock(IdentifierType::class);
        $gtinType = \Mockery::mock(IdentifierType::class);
        $locale = \Mockery::mock(Locale::class);

        $this->productVariationGenerator
            ->shouldReceive('generate')
            ->once()
            ->with($typeCode)
            ->andReturn([
                'values' => [
                    'name' => 'Test Product',
                ],
                'translations' => [
                    'name' => [
                        'de_DE' => 'Testprodukt',
                    ],
                ],
            ]);

        $repository = \Mockery::mock(EntityRepository::class);

        $repository
            ->shouldReceive('findOneBy')
            ->twice()
            ->andReturn(null);

        $this->entityManager
            ->shouldReceive('getRepository')
            ->twice()
            ->with(Identifier::class)
            ->andReturn($repository);

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(\Mockery::on(
                static fn (Product $product): bool => true,
            ));

        $progress = [];

        $this->productGenerator->generate(
            1,
            0,
            [$typeCode => $productType],
            ['name' => $fieldType],
            [
                'sku' => $skuType,
                'gtin' => $gtinType,
            ],
            ['de_DE' => $locale],
            static function (int $current) use (&$progress): void {
                $progress[] = $current;
            },
        );

        $this->assertSame([1], $progress);
    }

    public function testGenerateThrowsExceptionWhenFieldTypeIsMissing(): void
    {
        $typeCode = array_key_first(ProductDefinitions::PRODUCTS);

        $productType = \Mockery::mock(ProductType::class);

        $this->productVariationGenerator
            ->shouldReceive('generate')
            ->once()
            ->with($typeCode)
            ->andReturn([
                'values' => [
                    'name' => 'Test Product',
                ],
                'translations' => [],
            ]);

        $this->entityManager
            ->shouldNotReceive('persist');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('FieldType "name" is missing.');

        $this->productGenerator->generate(
            1,
            0,
            [$typeCode => $productType],
            [],
            [],
            [],
        );
    }

    public function testGenerateUsesOffsetForProductTypeSelection(): void
    {
        $typeCodes = array_keys(ProductDefinitions::PRODUCTS);
        $offset = 1;
        $typeCode = $typeCodes[$offset];

        $productType = \Mockery::mock(ProductType::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $skuType = \Mockery::mock(IdentifierType::class);
        $gtinType = \Mockery::mock(IdentifierType::class);

        $this->productVariationGenerator
            ->shouldReceive('generate')
            ->once()
            ->with($typeCode)
            ->andReturn([
                'values' => [
                    'name' => 'Test Product',
                ],
                'translations' => [],
            ]);

        $repository = \Mockery::mock(EntityRepository::class);

        $repository
            ->shouldReceive('findOneBy')
            ->twice()
            ->andReturn(null);

        $this->entityManager
            ->shouldReceive('getRepository')
            ->twice()
            ->with(Identifier::class)
            ->andReturn($repository);

        $this->entityManager
            ->shouldReceive('persist')
            ->once();

        $this->productGenerator->generate(
            1,
            $offset,
            [$typeCode => $productType],
            ['name' => $fieldType],
            [
                'sku' => $skuType,
                'gtin' => $gtinType,
            ],
            [],
        );
    }

    public function testGenerateSkipsTranslationWhenLocaleIsMissing(): void
    {
        $typeCode = array_key_first(ProductDefinitions::PRODUCTS);

        $productType = \Mockery::mock(ProductType::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $skuType = \Mockery::mock(IdentifierType::class);
        $gtinType = \Mockery::mock(IdentifierType::class);
        $locale = \Mockery::mock(Locale::class);

        $this->productVariationGenerator
            ->shouldReceive('generate')
            ->once()
            ->with($typeCode)
            ->andReturn([
                'values' => [
                    'name' => 'Test Product',
                ],
                'translations' => [
                    'name' => [
                        'de_DE' => 'Testprodukt',
                        'fr_FR' => 'Produit de test',
                    ],
                ],
            ]);

        $repository = \Mockery::mock(EntityRepository::class);

        $repository
            ->shouldReceive('findOneBy')
            ->twice()
            ->andReturn(null);

        $this->entityManager
            ->shouldReceive('getRepository')
            ->twice()
            ->with(Identifier::class)
            ->andReturn($repository);

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(\Mockery::on(
                static function (Product $product): bool {
                    $field = $product->getFields()->first();

                    if (false === $field) {
                        return false;
                    }

                    $translations = $field->getTranslations();

                    if (1 !== $translations->count()) {
                        return false;
                    }

                    $translation = $translations->first();

                    /** @phpstan-var FieldValueTranslation $translation */
                    return 'Testprodukt' === $translation->getValue();
                },
            ));

        $this->productGenerator->generate(
            1,
            0,
            [$typeCode => $productType],
            ['name' => $fieldType],
            [
                'sku' => $skuType,
                'gtin' => $gtinType,
            ],
            ['de_DE' => $locale],
        );
    }
}
