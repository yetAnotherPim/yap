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
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductTypeCrudController::class)]
#[UsesClass(InputType::class)]
class ProductTypeCrudControllerTest extends MockeryTestCase
{
    private ProductTypeCrudController $controller;

    /** @var TranslatorInterface|MockInterface */
    private TranslatorInterface $translatorMock;

    public function setUp(): void
    {
        $this->translatorMock = \Mockery::mock(TranslatorInterface::class);
        $this->controller = new ProductTypeCrudController($this->translatorMock);

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(ProductType::class, ProductTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('code', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertEquals('title', $fields[1]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsForReturnsExpectedFieldsOnFormPage(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_NEW));

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

        $fields = iterator_to_array(
            $this->controller->configureFields(Crud::PAGE_NEW)
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

        $this->translatorMock
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de_DE');

        $configuration
            ->shouldReceive('getFieldType')
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('is_active');

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::CheckBox);

        $fieldType
            ->shouldReceive('getTranslatedTitle')
            ->once()
            ->andReturn('Boolean field');

        $fields = iterator_to_array(
            $this->controller->configureFields(Crud::PAGE_NEW)
        );

        /** @var CollectionField $collectionField */
        $collectionField = $fields[3];

        $entryToStringMethod = $collectionField
            ->getAsDto()
            ->getCustomOption('entryToStringMethod');

        $this->translatorMock
            ->shouldReceive('trans')
            ->once()
            ->with('field_type.input_type.checkbox', [], 'admin', null)
            ->andReturn('CheckBox');

        $this->assertIsCallable($entryToStringMethod);
        $this->assertSame(
            'Boolean field (is_active - CheckBox)',
            $entryToStringMethod($configuration)
        );
    }

    public function testConfigureCrud(): void
    {
        $crud = Crud::new();

        $configuredCrud = $this->controller->configureCrud($crud);

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInPlural());
        $this->assertEquals(
            'product_type.entity.title_plural',
            $configuredCrud->getAsDto()->getEntityLabelInPlural()->getMessage()
        );

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInSingular());
        $this->assertEquals(
            'product_type.entity.title',
            $configuredCrud->getAsDto()->getEntityLabelInSingular()->getMessage()
        );
    }
}
