<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate\Resolver;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use App\Messenger\ProductUpdate\Resolver\FieldResolver;
use App\Repository\FieldTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(FieldResolver::class)]
#[UsesClass(Field::class)]
class FieldResolverTest extends MockeryTestCase
{
    public function testResolveReturnsExistingFieldByCode(): void
    {
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);
        $identifier->shouldReceive('getType')
            ->andReturn(FieldTypeIdentifierType::CODE);
        $identifier->shouldReceive('getValue')
            ->andReturn('product_name');

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType->shouldReceive('getCode')
            ->once()
            ->andReturn('product_name');

        $field = \Mockery::mock(Field::class);
        $field->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$field]));

        $repository = \Mockery::mock(FieldTypeRepository::class);
        $repository->shouldNotReceive('findByIdentifier');

        $resolver = new FieldResolver($repository);

        $this->assertSame($field, $resolver->resolve($product, $identifier));
    }

    public function testResolveReturnsNullWhenFieldTypeDoesNotExist(): void
    {
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);
        $identifier->shouldReceive('getType')
            ->andReturn(FieldTypeIdentifierType::CODE);
        $identifier->shouldReceive('getValue')
            ->andReturn('unknown_field');

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection());

        $repository = \Mockery::mock(FieldTypeRepository::class);
        $repository->shouldReceive('findByIdentifier')
            ->once()
            ->with($identifier)
            ->andReturnNull();

        $resolver = new FieldResolver($repository);

        $this->assertNull($resolver->resolve($product, $identifier));
    }

    public function testResolveReturnsNullWhenFieldTypeIsNotConfigured(): void
    {
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);
        $identifier->shouldReceive('getType')
            ->andReturn(FieldTypeIdentifierType::CODE);
        $identifier->shouldReceive('getValue')
            ->andReturn('product_name');

        $fieldType = \Mockery::mock(FieldType::class);

        $productType = \Mockery::mock(ProductType::class);
        $productType->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection());

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection());
        $product->shouldReceive('getProductType')
            ->once()
            ->andReturn($productType);
        $product->shouldNotReceive('addField');

        $repository = \Mockery::mock(FieldTypeRepository::class);
        $repository->shouldReceive('findByIdentifier')
            ->once()
            ->with($identifier)
            ->andReturn($fieldType);

        $resolver = new FieldResolver($repository);

        $this->assertNull($resolver->resolve($product, $identifier));
    }

    public function testResolveCreatesFieldWhenFieldTypeIsConfigured(): void
    {
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);
        $identifier->shouldReceive('getType')
            ->andReturn(FieldTypeIdentifierType::CODE);
        $identifier->shouldReceive('getValue')
            ->andReturn('product_name');

        $fieldTypeId = Uuid::v4();

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType->shouldReceive('getId')
            ->andReturn($fieldTypeId);

        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $configuration->shouldReceive('getFieldType')
            ->andReturn($fieldType);

        $productType = \Mockery::mock(ProductType::class);
        $productType->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection());
        $product->shouldReceive('getProductType')
            ->once()
            ->andReturn($productType);
        $product->shouldReceive('addField')
            ->once()
            ->with(\Mockery::on(
                static fn (Field $field): bool => $field->getType() === $fieldType,
            ));

        $repository = \Mockery::mock(FieldTypeRepository::class);
        $repository->shouldReceive('findByIdentifier')
            ->once()
            ->with($identifier)
            ->andReturn($fieldType);

        $resolver = new FieldResolver($repository);

        $result = $resolver->resolve($product, $identifier);

        $this->assertInstanceOf(Field::class, $result);
        $this->assertSame($fieldType, $result->getType());
    }
}
