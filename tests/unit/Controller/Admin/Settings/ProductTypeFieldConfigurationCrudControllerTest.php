<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\ProductTypeFieldConfigurationCrudController;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductTypeFieldConfigurationCrudController::class)]
class ProductTypeFieldConfigurationCrudControllerTest extends MockeryTestCase
{
    private ProductTypeFieldConfigurationCrudController $controller;

    /** @var TranslatorInterface|MockInterface */
    private TranslatorInterface $translator;

    public function setUp(): void
    {
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->controller = new ProductTypeFieldConfigurationCrudController($this->translator);

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(ProductTypeFieldConfiguration::class, ProductTypeFieldConfigurationCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $this->translator
            ->shouldReceive('trans')
            ->once()
            ->with('product_type_field_configuration.alert.duplicate_field_type', [], 'admin')
            ->andReturn('This field type is already used.');

        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(6, $fields);

        $this->assertInstanceOf(AssociationField::class, $fields[0]);
        $this->assertEquals('fieldType', $fields[0]->getAsDto()->getProperty());

        $this->assertInstanceOf(ArrayField::class, $fields[1]);
        $this->assertEquals('options', $fields[1]->getAsDto()->getProperty());

        $this->assertInstanceOf(TextareaField::class, $fields[2]);
        $this->assertEquals('defaultValue', $fields[2]->getAsDto()->getProperty());

        $this->assertInstanceOf(Field::class, $fields[3]);
        $this->assertEquals('productTypeFieldConfigurationDefaultTranslations', $fields[3]->getAsDto()->getProperty());

        $this->assertInstanceOf(BooleanField::class, $fields[4]);
        $this->assertEquals('required', $fields[4]->getAsDto()->getProperty());

        $this->assertInstanceOf(HiddenField::class, $fields[5]);
        $this->assertEquals('position', $fields[5]->getAsDto()->getProperty());
    }

    public function testAjaxGetFieldTypeReturnsExpectedResponse(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Select);

        $fieldType
            ->shouldReceive('isTranslatable')
            ->once()
            ->andReturn(true);

        $container = new Container();
        $container->set(
            'serializer',
            new Serializer(
                [new ObjectNormalizer()],
                [new JsonEncoder()],
            ),
        );

        $this->controller->setContainer($container);

        $response = $this->controller->ajaxGetFieldType($fieldType);
        /** @var string $content */
        $content = $response->getContent();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            [
                'inputType' => InputType::Select->value,
                'isTranslatable' => true,
            ],
            json_decode($content, true, 512, JSON_THROW_ON_ERROR),
        );
    }
}
