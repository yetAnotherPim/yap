<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\FileFieldRenderer;
use App\Form\Product\Fields\Resolver\Enum\FileType;
use App\Form\Product\Fields\Resolver\FileResolver;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField as EasyAdminFileField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\File;

#[CoversClass(FileFieldRenderer::class)]
class FileFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private FileResolver&MockInterface $fileResolver;

    private FileFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $this->fileResolver = \Mockery::mock(FileResolver::class);

        $this->renderer = new FileFieldRenderer($this->fileResolver);
    }

    public function testSupportsReturnsTrueForFileInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::File);

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
            ->andReturn('file');

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
            ->with(FileType::FILE)
            ->andReturn('public/uploads/files/');

        $this->fileResolver
            ->shouldReceive('resolve')
            ->once()
            ->with('2026/08/14/example.pdf', FileType::FILE)
            ->andReturn(null);

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label'
        );

        $this->assertInstanceOf(EasyAdminFileField::class, $result);
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
            ->andReturn('file');

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
            ->with(FileType::FILE)
            ->andReturn('public/uploads/files/');

        $this->fileResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($filePath, FileType::FILE)
            ->andReturn($file);

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label'
        );

        $this->assertInstanceOf(EasyAdminFileField::class, $result);

        $this->assertSame(
            'field_file',
            $result->getAsDto()->getProperty(),
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertSame($file, $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertTrue($formTypeOptions['required']);
    }
}
