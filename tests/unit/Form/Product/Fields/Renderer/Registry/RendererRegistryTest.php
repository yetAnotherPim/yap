<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Registry;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RendererRegistry::class)]
class RendererRegistryTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
    }

    public function testGetRendererReturnsMatchingRenderer(): void
    {
        $renderer = \Mockery::mock(FieldRendererInterface::class);

        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $renderer
            ->shouldReceive('supports')
            ->once()
            ->with($this->fieldType)
            ->andReturnTrue();

        $registry = new RendererRegistry([$renderer]);

        $this->assertSame(
            $renderer,
            $registry->getRenderer($this->field)
        );
    }

    public function testGetRendererSkipsUnsupportedRenderers(): void
    {
        $firstRenderer = \Mockery::mock(FieldRendererInterface::class);
        $secondRenderer = \Mockery::mock(FieldRendererInterface::class);

        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $firstRenderer
            ->shouldReceive('supports')
            ->once()
            ->with($this->fieldType)
            ->andReturnFalse();

        $secondRenderer
            ->shouldReceive('supports')
            ->once()
            ->with($this->fieldType)
            ->andReturnTrue();

        $registry = new RendererRegistry([
            $firstRenderer,
            $secondRenderer,
        ]);

        $this->assertSame(
            $secondRenderer,
            $registry->getRenderer($this->field)
        );
    }

    public function testGetRendererThrowsExceptionWhenNoRendererSupportsField(): void
    {
        $renderer = \Mockery::mock(FieldRendererInterface::class);

        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $renderer
            ->shouldReceive('supports')
            ->once()
            ->with($this->fieldType)
            ->andReturnFalse();

        $registry = new RendererRegistry([$renderer]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs('No renderer for "Text"');

        $registry->getRenderer($this->field);
    }
}
