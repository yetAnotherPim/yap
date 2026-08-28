<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\SectionRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SectionRenderer::class)]
class SectionRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private SectionRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);

        $this->renderer = new SectionRenderer();
    }

    public function testSupportsReturnsTrueForSectionInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Section);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonSectionInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsFormField(): void
    {
        $result = $this->renderer->render($this->field, \Mockery::mock(ProductTypeFieldConfiguration::class), 'label');

        $this->assertInstanceOf(FormField::class, $result);
    }

    public function testRenderUsesFieldTypeTitle(): void
    {
        $result = $this->renderer->render($this->field, \Mockery::mock(ProductTypeFieldConfiguration::class), 'label');

        $this->assertSame(
            'label',
            $result->getAsDto()->getLabel()
        );
    }
}
