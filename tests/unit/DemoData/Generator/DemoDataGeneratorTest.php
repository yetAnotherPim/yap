<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Generator;

use App\DemoData\Generator\DemoDataGenerator;
use App\DemoData\Generator\ProductGenerator;
use App\DemoData\Provider\FieldTypeProvider;
use App\DemoData\Provider\IdentifierTypeProvider;
use App\DemoData\Provider\LocaleProvider;
use App\DemoData\Provider\ProductTypeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DemoDataGenerator::class)]
class DemoDataGeneratorTest extends MockeryTestCase
{
    public function testGenerateRejectsNonPositiveProductCount(): void
    {
        $generator = $this->createGenerator();

        foreach ([0, -1, -100] as $count) {
            try {
                $generator->generate($count);
                $this->fail('Expected InvalidArgumentException was not thrown.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertSame(
                    'The product count must be greater than zero.',
                    $exception->getMessage(),
                );
            }
        }
    }

    public function testGenerateProcessesSingleBatch(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $localeProvider = \Mockery::mock(LocaleProvider::class);
        $identifierTypeProvider = \Mockery::mock(IdentifierTypeProvider::class);
        $fieldTypeProvider = \Mockery::mock(FieldTypeProvider::class);
        $productTypeProvider = \Mockery::mock(ProductTypeProvider::class);
        $productGenerator = \Mockery::mock(ProductGenerator::class);

        $locales = ['en' => 'locale'];
        $identifierTypes = ['sku' => 'sku'];
        $fieldTypes = ['name' => 'name'];
        $productTypes = ['apparel' => 'apparel'];

        $localeProvider
            ->shouldReceive('getLocales')
            ->once()
            ->andReturn($locales);

        $identifierTypeProvider
            ->shouldReceive('getIdentifierTypes')
            ->once()
            ->andReturn($identifierTypes);

        $fieldTypeProvider
            ->shouldReceive('getFieldTypes')
            ->once()
            ->with($locales)
            ->andReturn($fieldTypes);

        $productTypeProvider
            ->shouldReceive('getProductTypes')
            ->once()
            ->with($locales, $fieldTypes)
            ->andReturn($productTypes);

        $productGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(10, 0, $productTypes, $fieldTypes, $identifierTypes, $locales, null);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $entityManager
            ->shouldReceive('clear')
            ->once();

        $generator = new DemoDataGenerator(
            $entityManager,
            $localeProvider,
            $identifierTypeProvider,
            $fieldTypeProvider,
            $productTypeProvider,
            $productGenerator,
        );

        $generator->generate(10);
    }

    public function testGenerateProcessesProductsInBatches(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $localeProvider = \Mockery::mock(LocaleProvider::class);
        $identifierTypeProvider = \Mockery::mock(IdentifierTypeProvider::class);
        $fieldTypeProvider = \Mockery::mock(FieldTypeProvider::class);
        $productTypeProvider = \Mockery::mock(ProductTypeProvider::class);
        $productGenerator = \Mockery::mock(ProductGenerator::class);

        $locales = ['en' => 'locale'];
        $identifierTypes = ['sku' => 'sku'];
        $fieldTypes = ['name' => 'name'];
        $productTypes = ['apparel' => 'apparel'];

        $localeProvider
            ->shouldReceive('getLocales')
            ->times(3)
            ->andReturn($locales);

        $identifierTypeProvider
            ->shouldReceive('getIdentifierTypes')
            ->times(3)
            ->andReturn($identifierTypes);

        $fieldTypeProvider
            ->shouldReceive('getFieldTypes')
            ->times(3)
            ->with($locales)
            ->andReturn($fieldTypes);

        $productTypeProvider
            ->shouldReceive('getProductTypes')
            ->times(3)
            ->with($locales, $fieldTypes)
            ->andReturn($productTypes);

        $expectedBatches = [
            [100, 0],
            [100, 100],
            [1, 200],
        ];

        $productGenerator
            ->shouldReceive('generate')
            ->times(3)
            ->withArgs(function (
                int $count,
                int $offset,
                array $actualProductTypes,
                array $actualFieldTypes,
                array $actualIdentifierTypes,
                array $actualLocales,
                ?callable $onProgress,
            ) use (&$expectedBatches, $productTypes, $fieldTypes, $identifierTypes, $locales): bool {
                /** @var array{0: int, 1: int} $expected */
                $expected = array_shift($expectedBatches);

                $this->assertSame($expected[0], $count);
                $this->assertSame($expected[1], $offset);
                $this->assertSame($productTypes, $actualProductTypes);
                $this->assertSame($fieldTypes, $actualFieldTypes);
                $this->assertSame($identifierTypes, $actualIdentifierTypes);
                $this->assertSame($locales, $actualLocales);
                $this->assertNull($onProgress);

                return true;
            });

        $entityManager
            ->shouldReceive('flush')
            ->times(3);

        $entityManager
            ->shouldReceive('clear')
            ->times(3);

        $generator = new DemoDataGenerator(
            $entityManager,
            $localeProvider,
            $identifierTypeProvider,
            $fieldTypeProvider,
            $productTypeProvider,
            $productGenerator,
        );

        $generator->generate(201);

        $this->assertSame([], $expectedBatches);
    }

    public function testGeneratePassesProgressCallbackToProductGenerator(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $localeProvider = \Mockery::mock(LocaleProvider::class);
        $identifierTypeProvider = \Mockery::mock(IdentifierTypeProvider::class);
        $fieldTypeProvider = \Mockery::mock(FieldTypeProvider::class);
        $productTypeProvider = \Mockery::mock(ProductTypeProvider::class);
        $productGenerator = \Mockery::mock(ProductGenerator::class);

        $locales = [];
        $identifierTypes = [];
        $fieldTypes = [];
        $productTypes = [];

        $localeProvider
            ->shouldReceive('getLocales')
            ->once()
            ->andReturn($locales);

        $identifierTypeProvider
            ->shouldReceive('getIdentifierTypes')
            ->once()
            ->andReturn($identifierTypes);

        $fieldTypeProvider
            ->shouldReceive('getFieldTypes')
            ->once()
            ->with($locales)
            ->andReturn($fieldTypes);

        $productTypeProvider
            ->shouldReceive('getProductTypes')
            ->once()
            ->with($locales, $fieldTypes)
            ->andReturn($productTypes);

        $callback = static function (int $progress): void {};

        $productGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(1, 0, $productTypes, $fieldTypes, $identifierTypes, $locales, $callback);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $entityManager
            ->shouldReceive('clear')
            ->once();

        $generator = new DemoDataGenerator(
            $entityManager,
            $localeProvider,
            $identifierTypeProvider,
            $fieldTypeProvider,
            $productTypeProvider,
            $productGenerator,
        );

        $generator->generate(1, $callback);
    }

    private function createGenerator(): DemoDataGenerator
    {
        return new DemoDataGenerator(
            \Mockery::mock(EntityManagerInterface::class),
            \Mockery::mock(LocaleProvider::class),
            \Mockery::mock(IdentifierTypeProvider::class),
            \Mockery::mock(FieldTypeProvider::class),
            \Mockery::mock(ProductTypeProvider::class),
            \Mockery::mock(ProductGenerator::class),
        );
    }
}
