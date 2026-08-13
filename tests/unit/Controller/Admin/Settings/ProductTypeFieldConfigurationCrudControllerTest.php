<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\ProductTypeFieldConfigurationCrudController;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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

        $this->assertCount(4, $fields);

        $this->assertInstanceOf(AssociationField::class, $fields[0]);
        $this->assertEquals('fieldType', $fields[0]->getAsDto()->getProperty());

        $this->assertInstanceOf(TextareaField::class, $fields[1]);
        $this->assertEquals('defaultValue', $fields[1]->getAsDto()->getProperty());

        $this->assertInstanceOf(BooleanField::class, $fields[2]);
        $this->assertEquals('required', $fields[2]->getAsDto()->getProperty());

        $this->assertInstanceOf(HiddenField::class, $fields[3]);
        $this->assertEquals('position', $fields[3]->getAsDto()->getProperty());
    }
}
