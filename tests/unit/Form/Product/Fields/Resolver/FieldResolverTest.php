<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Resolver;

use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Resolver\FieldResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FieldResolver::class)]
#[UsesClass(Field::class)]
#[UsesClass(TimestampableEntity::class)]
class FieldResolverTest extends MockeryTestCase
{
    private Product&MockInterface $product;

    private FieldType&MockInterface $fieldType;

    private FieldResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = \Mockery::mock(Product::class);
        $this->fieldType = \Mockery::mock(FieldType::class);

        $this->resolver = new FieldResolver();
    }

    public function testResolveReturnsExistingField(): void
    {
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$field]));

        $field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->twice()
            ->andReturn('foo');

        $result = $this->resolver->resolve(
            $this->product,
            $this->fieldType
        );

        $this->assertSame($field, $result);
    }

    public function testResolveCreatesFieldWhenNoExistingFieldMatches(): void
    {
        $existingField = \Mockery::mock(Field::class);
        $existingFieldType = \Mockery::mock(FieldType::class);

        $this->product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$existingField]));

        $existingField
            ->shouldReceive('getType')
            ->once()
            ->andReturn($existingFieldType);

        $existingFieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('other');

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('foo');

        $result = $this->resolver->resolve(
            $this->product,
            $this->fieldType,
            'default value'
        );

        $this->assertNotSame($existingField, $result);
        $this->assertSame($this->fieldType, $result->getType());
        $this->assertSame($this->product, $result->getProduct());
        $this->assertSame('default value', $result->getValue());
    }

    public function testResolveCreatesFieldWithNullValueWhenNoDefaultValueIsGiven(): void
    {
        $this->product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->resolver->resolve(
            $this->product,
            $this->fieldType
        );

        $this->assertSame($this->fieldType, $result->getType());
        $this->assertSame($this->product, $result->getProduct());
        $this->assertNull($result->getValue());
    }
}
