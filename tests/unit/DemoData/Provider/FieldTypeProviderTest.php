<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Provider;

use App\DemoData\Definition\FieldTypeDefinitions;
use App\DemoData\Provider\FieldTypeProvider;
use App\DemoData\Synchronizer\TitleTranslationSynchronizer;
use App\Entity\Product\Field\Type\FieldType;
use App\Repository\FieldTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FieldTypeProvider::class)]
#[UsesClass(FieldType::class)]
class FieldTypeProviderTest extends MockeryTestCase
{
    public function testGetFieldTypesCreatesMissingTypes(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $repository = \Mockery::mock(FieldTypeRepository::class);
        $synchronizer = \Mockery::mock(TitleTranslationSynchronizer::class);

        foreach (FieldTypeDefinitions::FIELD_TYPES as $code => $definition) {
            $repository
                ->shouldReceive('findOneBy')
                ->once()
                ->with(['code' => $code])
                ->andReturnNull();
        }

        $entityManager
            ->shouldReceive('persist')
            ->times(\count(FieldTypeDefinitions::FIELD_TYPES))
            ->with(\Mockery::type(FieldType::class));

        foreach (FieldTypeDefinitions::FIELD_TYPES as $definition) {
            $synchronizer
                ->shouldReceive('synchronize')
                ->once()
                ->with(
                    \Mockery::type(FieldType::class),
                    $definition['title'],
                    [],
                );
        }

        $provider = new FieldTypeProvider(
            $entityManager,
            $repository,
            $synchronizer,
        );

        $result = $provider->getFieldTypes([]);

        $this->assertSame(
            array_keys(FieldTypeDefinitions::FIELD_TYPES),
            array_keys($result),
        );

        foreach (FieldTypeDefinitions::FIELD_TYPES as $code => $definition) {
            $fieldType = $result[$code];

            $this->assertInstanceOf(FieldType::class, $fieldType);
            $this->assertSame($code, $fieldType->getCode());
            $this->assertSame($definition['title']['en_GB'], $fieldType->getTitle());
            $this->assertSame($definition['inputType'], $fieldType->getInputType());
            $this->assertSame($definition['filterable'], $fieldType->isFilterable());
            $this->assertSame($definition['translatable'], $fieldType->isTranslatable());
            $this->assertSame(
                $definition['decimalSeparator'] ?? null,
                $fieldType->getDecimalSeparator(),
            );
        }
    }

    public function testGetFieldTypesReusesExistingTypes(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $repository = \Mockery::mock(FieldTypeRepository::class);
        $synchronizer = \Mockery::mock(TitleTranslationSynchronizer::class);

        $existingTypes = [];

        foreach (FieldTypeDefinitions::FIELD_TYPES as $code => $definition) {
            $fieldType = \Mockery::mock(FieldType::class);

            $existingTypes[$code] = $fieldType;

            $repository
                ->shouldReceive('findOneBy')
                ->once()
                ->with(['code' => $code])
                ->andReturn($fieldType);

            $synchronizer
                ->shouldReceive('synchronize')
                ->once()
                ->with(
                    $fieldType,
                    $definition['title'],
                    [],
                );
        }

        $entityManager
            ->shouldNotReceive('persist');

        $provider = new FieldTypeProvider(
            $entityManager,
            $repository,
            $synchronizer,
        );

        $result = $provider->getFieldTypes([]);

        foreach ($existingTypes as $code => $fieldType) {
            $this->assertSame($fieldType, $result[$code]);
        }
    }
}
