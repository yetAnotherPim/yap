<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\ProductTypeFieldConfigurationCrudController;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(AssociationField::class, $fields[0]);
        $this->assertEquals('fieldType', $fields[0]->getAsDto()->getProperty());

        // TODO: readd
        // $this->assertInstanceOf(HiddenField::class, $fields[1]);
        $this->assertEquals('position', $fields[1]->getAsDto()->getProperty());
    }
}
