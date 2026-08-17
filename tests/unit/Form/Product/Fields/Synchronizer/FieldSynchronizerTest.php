<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Synchronizer;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Form\Product\Fields\Resolver\FieldResolver;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldSynchronizer::class)]
class FieldSynchronizerTest extends MockeryTestCase
{
    private Product&MockInterface $product;

    private ProductType&MockInterface $productType;

    private FieldResolver&MockInterface $fieldResolver;

    private FieldSynchronizer $synchronizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = \Mockery::mock(Product::class);
        $this->productType = \Mockery::mock(ProductType::class);
        $this->fieldResolver = \Mockery::mock(FieldResolver::class);

        $this->synchronizer = new FieldSynchronizer(
            $this->fieldResolver
        );
    }

    public function testSynchronizeFieldsSkipsConfigurationWithoutFieldType(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $this->fieldResolver
            ->shouldNotReceive('resolve');

        $this->product
            ->shouldNotReceive('addField');

        $this->synchronizer->synchronizeFields($this->product);
    }

    public function testSynchronizeFieldsSkipsSectionFields(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Section);

        $this->fieldResolver
            ->shouldNotReceive('resolve');

        $this->product
            ->shouldNotReceive('addField');

        $this->synchronizer->synchronizeFields($this->product);
    }

    public function testSynchronizeFieldsCreatesFieldWithDefaultValue(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn('Default');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldType, 'Default')
            ->andReturn($field);

        $field
            ->shouldNotReceive('setValue');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields($this->product);
    }

    public function testSynchronizeFieldsSetsRequestValueForRegularField(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(5)
            ->andReturn(InputType::Text);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn('Default');

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('title');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldType, 'Default')
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with('Test value');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_title' => 'Test value']
        );
    }

    public function testSynchronizeFieldsConvertsEmptyRequestValueToNull(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(5)
            ->andReturn(InputType::Text);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('title');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with(null);

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_title' => '']
        );
    }

    public function testSynchronizeFieldsSetsBooleanValueWhenRequestContainsField(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->twice()
            ->andReturn(InputType::CheckBox);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('active');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with('1');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_active' => '1']
        );
    }

    public function testSynchronizeFieldsSetsEmptyBooleanValueWhenRequestDoesNotContainField(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->twice()
            ->andReturn(InputType::CheckBox);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('active');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with('');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            []
        );
    }

    public function testSynchronizeFieldsSetsMultiSelectValueWhenRequestContainsArray(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(5)
            ->andReturn(InputType::MultiSelect);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('tags');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with('tag1,tag2');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_tags' => ['tag1', 'tag2']]
        );
    }

    public function testSynchronizeFieldsSetsNullForMultiSelectWhenRequestValueIsNull(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(5)
            ->andReturn(InputType::MultiSelect);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('tags');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with(null);

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_tags' => null]
        );
    }

    public function testSynchronizeFieldsHandlesMultipleFieldsInRequestData(): void
    {
        $config1 = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType1 = \Mockery::mock(FieldType::class);
        $field1 = \Mockery::mock(Field::class);

        $config2 = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType2 = \Mockery::mock(FieldType::class);
        $field2 = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$config1, $config2]));

        $config1->shouldReceive('getFieldType')->once()->andReturn($fieldType1);
        $fieldType1->shouldReceive('getInputType')->times(5)->andReturn(InputType::Text);
        $fieldType1->shouldReceive('getCode')->once()->andReturn('title');
        $config1->shouldReceive('getDefaultValue')->once()->andReturn(null);

        $config2->shouldReceive('getFieldType')->once()->andReturn($fieldType2);
        $fieldType2->shouldReceive('getInputType')->twice()->andReturn(InputType::CheckBox);
        $fieldType2->shouldReceive('getCode')->once()->andReturn('active');
        $config2->shouldReceive('getDefaultValue')->once()->andReturn(null);

        $this->fieldResolver->shouldReceive('resolve')->twice()->andReturn($field1, $field2);

        $field1->shouldReceive('setValue')->once()->with('New Title');
        $field2->shouldReceive('setValue')->once()->with('1');

        $this->product->shouldReceive('addField')->once()->with($field1);
        $this->product->shouldReceive('addField')->once()->with($field2);

        $this->synchronizer->synchronizeFields(
            $this->product,
            [
                'field_title' => 'New Title',
                'field_active' => '1',
            ]
        );
    }

    public function testSynchronizeFieldsSetsUploadedFilePath(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(3)
            ->andReturn(InputType::File);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('file');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldType, null)
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with('2026/08/14/indexcontroller-file.pdf');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_file' => ['deleted_files' => '']],
            [
                'field_file' => '2026/08/14/indexcontroller-file.pdf',
            ],
        );
    }

    public function testSynchronizeFieldsSetsNullForFileWithoutUploadedFilePath(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(3)
            ->andReturn(InputType::File);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('file');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldType, null)
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with(null);

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_file' => ['deleted_files' => '']],
            [],
        );
    }

    public function testSynchronizeFieldsSetsUploadedImagePath(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(4)
            ->andReturn(InputType::Image);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('image');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldType, null)
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with('2026/08/14/product-image.jpg');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_image' => ['deleted_files' => '']],
            [
                'field_image' => '2026/08/14/product-image.jpg',
            ],
        );
    }

    public function testSynchronizeFieldsConvertsArrayValueToEmptyStringForRegularField(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $fieldType
            ->shouldReceive('getInputType')
            ->times(5)
            ->andReturn(InputType::Text);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('title');

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldType, null)
            ->andReturn($field);

        $field
            ->shouldReceive('setValue')
            ->once()
            ->with('');

        $this->product
            ->shouldReceive('addField')
            ->once()
            ->with($field);

        $this->synchronizer->synchronizeFields(
            $this->product,
            ['field_title' => ['unexpected', 'array']],
        );
    }
}
