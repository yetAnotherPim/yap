<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\ImageFieldRenderer;
use App\Form\Product\Fields\Resolver\Enum\FileType;
use App\Form\Product\Fields\Resolver\FileResolver;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\File;

#[CoversClass(ImageFieldRenderer::class)]
class ImageFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private FileResolver&MockInterface $fileResolver;

    private ImageFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $this->fileResolver = \Mockery::mock(FileResolver::class);

        $this->renderer = new ImageFieldRenderer($this->fileResolver);
    }

    public function testSupportsReturnsTrueForFileInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Image);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType),
        );
    }

    public function testSupportsReturnsFalseForNonFileInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType),
        );
    }

    public function testRenderReturnsFileField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('image');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('2026/08/14/example.pdf');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $this->fileResolver
            ->shouldReceive('getUploadDirectory')
            ->once()
            ->with(FileType::IMAGE)
            ->andReturn('public/uploads/images/');

        $this->fileResolver
            ->shouldReceive('resolve')
            ->once()
            ->with('2026/08/14/example.pdf', FileType::IMAGE)
            ->andReturn(null);

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label'
        );

        $this->assertInstanceOf(ImageField::class, $result);
    }

    public function testRenderConfiguresFileFieldCorrectly(): void
    {
        $filePath = '2026/08/14/example.pdf';

        $file = \Mockery::mock(File::class);

        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('image');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn($filePath);

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $this->fileResolver
            ->shouldReceive('getUploadDirectory')
            ->once()
            ->with(FileType::IMAGE)
            ->andReturn('public/uploads/images/');

        $this->fileResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($filePath, FileType::IMAGE)
            ->andReturn($file);

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label'
        );

        $this->assertInstanceOf(ImageField::class, $result);

        $this->assertSame(
            'field_image',
            $result->getAsDto()->getProperty(),
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertSame($file, $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertTrue($formTypeOptions['required']);
    }
}
