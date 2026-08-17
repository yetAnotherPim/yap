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
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(ProductTypeFieldConfigurationCrudController::class)]
class ProductTypeFieldConfigurationCrudControllerTest extends MockeryTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(ProductTypeFieldConfiguration::class, ProductTypeFieldConfigurationCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new ProductTypeFieldConfigurationCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(5, $fields);

        $this->assertInstanceOf(AssociationField::class, $fields[0]);
        $this->assertEquals('fieldType', $fields[0]->getAsDto()->getProperty());

        $this->assertInstanceOf(ArrayField::class, $fields[1]);
        $this->assertEquals('options', $fields[1]->getAsDto()->getProperty());

        $this->assertInstanceOf(TextareaField::class, $fields[2]);
        $this->assertEquals('defaultValue', $fields[2]->getAsDto()->getProperty());

        $this->assertInstanceOf(BooleanField::class, $fields[3]);
        $this->assertEquals('required', $fields[3]->getAsDto()->getProperty());

        $this->assertInstanceOf(HiddenField::class, $fields[4]);
        $this->assertEquals('position', $fields[4]->getAsDto()->getProperty());
    }

    public function testAjaxGetInputTypeReturnsExpectedResponse(): void
    {
        $controller = new ProductTypeFieldConfigurationCrudController();
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Select);

        $container = new Container();
        $container->set(
            'serializer',
            new Serializer(
                [new ObjectNormalizer()],
                [new JsonEncoder()],
            ),
        );

        $controller->setContainer($container);

        $response = $controller->ajaxGetInputType($fieldType);
        /** @var string $content */
        $content = $response->getContent();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            ['inputType' => InputType::Select->value],
            json_decode($content, true, 512, JSON_THROW_ON_ERROR),
        );
    }
}
