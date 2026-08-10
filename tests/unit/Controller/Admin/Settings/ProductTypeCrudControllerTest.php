<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\ProductTypeCrudController;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Layout\EaFormFieldsetOpenType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductTypeCrudController::class)]
class ProductTypeCrudControllerTest extends MockeryTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(ProductType::class, ProductTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new ProductTypeCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('code', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertEquals('title', $fields[1]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsForReturnsExpectedFieldsOnFormPage(): void
    {
        $controller = new ProductTypeCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_NEW));

        $this->assertCount(4, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('code', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertEquals('title', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(FormField::class, $fields[2]);
        $this->assertEquals(EaFormFieldsetOpenType::class, $fields[2]->getAsDto()->getFormType());
        $this->assertInstanceOf(CollectionField::class, $fields[3]);
        $this->assertEquals('productTypeFieldConfigurations', $fields[3]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsOnFormPageEntryToStringMethodReturnsEmptyStringWithoutFieldType(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $controller = new ProductTypeCrudController();
        $fields = iterator_to_array(
            $controller->configureFields(Crud::PAGE_NEW)
        );

        /** @var CollectionField $collectionField */
        $collectionField = $fields[3];

        $entryToStringMethod = $collectionField
            ->getAsDto()
            ->getCustomOption('entryToStringMethod');

        $this->assertIsCallable($entryToStringMethod);
        $this->assertSame('', $entryToStringMethod($configuration));
    }

    public function testConfigureFieldsOnFormPageEntryToStringMethodReturnsFormattedFieldType(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);

        $configuration
            ->shouldReceive('getFieldType')
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Boolean field');

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('is_active');

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Boolean);

        $controller = new ProductTypeCrudController();
        $fields = iterator_to_array(
            $controller->configureFields(Crud::PAGE_NEW)
        );

        /** @var CollectionField $collectionField */
        $collectionField = $fields[3];

        $entryToStringMethod = $collectionField
            ->getAsDto()
            ->getCustomOption('entryToStringMethod');

        $this->assertIsCallable($entryToStringMethod);
        $this->assertSame(
            'Boolean field (is_active - Boolean)',
            $entryToStringMethod($configuration)
        );
    }
}
