<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Field\Registry;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\Handler\FieldFilterHandlerInterface;
use App\Form\Product\Filter\Element\Field\Registry\FieldFilterHandlerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldFilterHandlerRegistry::class)]
class FieldFilterHandlerRegistryTest extends MockeryTestCase
{
    public function testGetReturnsSupportingHandler(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $unsupportedHandler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );
        $unsupportedHandler
            ->shouldReceive('supports')
            ->once()
            ->with($fieldType)
            ->andReturnFalse();

        $supportedHandler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );
        $supportedHandler
            ->shouldReceive('supports')
            ->once()
            ->with($fieldType)
            ->andReturnTrue();

        $registry = new FieldFilterHandlerRegistry([
            $unsupportedHandler,
            $supportedHandler,
        ]);

        $this->assertSame(
            $supportedHandler,
            $registry->get($fieldType),
        );
    }

    public function testGetStopsAtFirstSupportingHandler(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $firstHandler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );
        $firstHandler
            ->shouldReceive('supports')
            ->once()
            ->with($fieldType)
            ->andReturnTrue();

        $secondHandler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );
        $secondHandler
            ->shouldNotReceive('supports');

        $registry = new FieldFilterHandlerRegistry([
            $firstHandler,
            $secondHandler,
        ]);

        $this->assertSame(
            $firstHandler,
            $registry->get($fieldType),
        );
    }

    public function testGetThrowsExceptionWhenNoHandlerSupportsFieldType(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Integer);

        $firstHandler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );
        $firstHandler
            ->shouldReceive('supports')
            ->once()
            ->with($fieldType)
            ->andReturnFalse();

        $secondHandler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );
        $secondHandler
            ->shouldReceive('supports')
            ->once()
            ->with($fieldType)
            ->andReturnFalse();

        $registry = new FieldFilterHandlerRegistry([
            $firstHandler,
            $secondHandler,
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'No filter handler supports input type "Integer".',
        );

        $registry->get($fieldType);
    }

    public function testGetThrowsExceptionWhenRegistryIsEmpty(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Integer);

        $registry = new FieldFilterHandlerRegistry([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'No filter handler supports input type "Integer".',
        );

        $registry->get($fieldType);
    }
}
