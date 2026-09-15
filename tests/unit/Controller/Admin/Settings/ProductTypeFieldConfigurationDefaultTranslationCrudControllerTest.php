<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\ProductTypeFieldConfigurationDefaultTranslationCrudController;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductTypeFieldConfigurationDefaultTranslationCrudController::class)]
class ProductTypeFieldConfigurationDefaultTranslationCrudControllerTest extends TestCase
{
    private readonly ProductTypeFieldConfigurationDefaultTranslationCrudController $controller;

    public function setUp(): void
    {
        $this->controller = new ProductTypeFieldConfigurationDefaultTranslationCrudController();

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(ProductTypeFieldConfigurationDefaultTranslation::class, ProductTypeFieldConfigurationDefaultTranslationCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(AssociationField::class, $fields[0]);
        $this->assertEquals('locale', $fields[0]->getAsDto()->getProperty());

        $this->assertInstanceOf(TextareaField::class, $fields[1]);
        $this->assertEquals('value', $fields[1]->getAsDto()->getProperty());
    }
}
