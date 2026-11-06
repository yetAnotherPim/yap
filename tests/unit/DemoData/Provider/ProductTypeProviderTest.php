<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Provider;

use App\DemoData\Definition\ProductTypeDefinitions;
use App\DemoData\Provider\ProductTypeProvider;
use App\DemoData\Synchronizer\TitleTranslationSynchronizer;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Repository\ProductTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ProductTypeProvider::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(ProductTypeFieldConfiguration::class)]
class ProductTypeProviderTest extends MockeryTestCase
{
    public function testGetProductTypesCreatesMissingTypesAndConfigurations(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $repository = \Mockery::mock(ProductTypeRepository::class);
        $synchronizer = \Mockery::mock(TitleTranslationSynchronizer::class);

        foreach (ProductTypeDefinitions::PRODUCT_TYPES as $code => $definition) {
            $repository
                ->shouldReceive('findOneBy')
                ->once()
                ->with(['code' => $code])
                ->andReturnNull();

            $synchronizer
                ->shouldReceive('synchronize')
                ->once()
                ->with(
                    \Mockery::type(ProductType::class),
                    $definition['title'],
                    [],
                );
        }

        $entityManager
            ->shouldReceive('persist')
            ->times(\count(ProductTypeDefinitions::PRODUCT_TYPES))
            ->with(\Mockery::type(ProductType::class));

        $fieldTypes = $this->createFieldTypes();

        $provider = new ProductTypeProvider(
            $entityManager,
            $repository,
            $synchronizer,
        );

        $result = $provider->getProductTypes([], $fieldTypes);

        $this->assertSame(
            array_keys(ProductTypeDefinitions::PRODUCT_TYPES),
            array_keys($result),
        );

        foreach (ProductTypeDefinitions::PRODUCT_TYPES as $code => $definition) {
            $productType = $result[$code];

            $this->assertInstanceOf(ProductType::class, $productType);
            $this->assertSame($code, $productType->getCode());
            $this->assertSame($definition['title']['en_GB'], $productType->getTitle());

            $configurations = $productType->getProductTypeFieldConfigurations();

            $this->assertCount(\count($definition['fields']), $configurations);

            foreach ($definition['fields'] as $fieldCode => $fieldDefinition) {
                $configuration = $configurations->findFirst(
                    static fn (
                        int $key,
                        ProductTypeFieldConfiguration $configuration,
                    ): bool => $configuration->getFieldType()?->getCode() === $fieldCode,
                );

                $this->assertNotNull($configuration);
                $this->assertSame($fieldTypes[$fieldCode], $configuration->getFieldType());

                $this->assertSame(
                    array_search($fieldCode, array_keys($definition['fields']), true),
                    $configuration->getPosition(),
                );

                $this->assertSame(
                    $fieldDefinition['required'] ?? false,
                    $configuration->isRequired(),
                );

                $this->assertSame(
                    $fieldDefinition['options'] ?? [],
                    $configuration->getOptions(),
                );
            }
        }
    }

    public function testGetProductTypesReusesExistingTypesAndConfigurations(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $repository = \Mockery::mock(ProductTypeRepository::class);
        $synchronizer = \Mockery::mock(TitleTranslationSynchronizer::class);

        $fieldTypes = $this->createFieldTypes();
        $existingTypes = [];

        foreach (ProductTypeDefinitions::PRODUCT_TYPES as $code => $definition) {
            $configurations = [];

            foreach ($definition['fields'] as $fieldCode => $fieldDefinition) {
                $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

                $configuration->shouldReceive('getFieldType')
                    ->andReturn($fieldTypes[$fieldCode]);

                $configuration->shouldReceive('setPosition')
                    ->once()
                    ->with(array_search($fieldCode, array_keys($definition['fields']), true))
                    ->andReturnSelf();

                $configuration->shouldReceive('setRequired')
                    ->once()
                    ->with($fieldDefinition['required'] ?? false)
                    ->andReturnSelf();

                $configuration->shouldReceive('setOptions')
                    ->once()
                    ->with($fieldDefinition['options'] ?? [])
                    ->andReturnSelf();

                $configurations[] = $configuration;
            }

            $productType = \Mockery::mock(ProductType::class);

            $productType->shouldReceive('getCode')
                ->andReturn($code);

            $productType->shouldReceive('setTitle')
                ->once()
                ->with($definition['title']['en_GB']);

            $productType->shouldReceive('getProductTypeFieldConfigurations')
                ->andReturn(new ArrayCollection($configurations));

            $productType->shouldNotReceive('addProductTypeFieldConfiguration');

            $synchronizer
                ->shouldReceive('synchronize')
                ->once()
                ->with(
                    $productType,
                    $definition['title'],
                    [],
                );

            $existingTypes[$code] = $productType;

            $repository
                ->shouldReceive('findOneBy')
                ->once()
                ->with(['code' => $code])
                ->andReturn($productType);
        }

        $entityManager
            ->shouldNotReceive('persist');

        $provider = new ProductTypeProvider(
            $entityManager,
            $repository,
            $synchronizer,
        );

        $result = $provider->getProductTypes([], $fieldTypes);

        foreach ($existingTypes as $code => $productType) {
            $this->assertSame($productType, $result[$code]);
        }
    }

    public function testGetProductTypesThrowsExceptionWhenFieldTypeIsMissing(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $repository = \Mockery::mock(ProductTypeRepository::class);
        $synchronizer = \Mockery::mock(TitleTranslationSynchronizer::class);

        $firstProductTypeCode = array_key_first(ProductTypeDefinitions::PRODUCT_TYPES);
        $firstDefinition = ProductTypeDefinitions::PRODUCT_TYPES[$firstProductTypeCode];
        $missingFieldCode = array_key_first($firstDefinition['fields']);

        $repository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['code' => $firstProductTypeCode])
            ->andReturnNull();

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(\Mockery::type(ProductType::class));

        $synchronizer
            ->shouldReceive('synchronize')
            ->once()
            ->with(
                \Mockery::type(ProductType::class),
                $firstDefinition['title'],
                [],
            );

        $fieldTypes = $this->createFieldTypes();
        unset($fieldTypes[$missingFieldCode]);

        $provider = new ProductTypeProvider(
            $entityManager,
            $repository,
            $synchronizer,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'FieldType "%s" required by ProductType "%s" does not exist.',
                $missingFieldCode,
                $firstProductTypeCode,
            ),
        );

        $provider->getProductTypes([], $fieldTypes);
    }

    /**
     * @return array<string, FieldType>
     */
    private function createFieldTypes(): array
    {
        $fieldCodes = [];

        foreach (ProductTypeDefinitions::PRODUCT_TYPES as $definition) {
            $fieldCodes = array_merge(
                $fieldCodes,
                array_keys($definition['fields']),
            );
        }

        $fieldTypes = [];

        foreach (array_unique($fieldCodes) as $fieldCode) {
            $fieldType = \Mockery::mock(FieldType::class);

            $fieldType->shouldReceive('getCode')
                ->andReturn($fieldCode);

            $fieldTypes[$fieldCode] = $fieldType;
        }

        return $fieldTypes;
    }
}
