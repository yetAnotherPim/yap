<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Entity\Product\Field\Type\FieldType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldTypeCrudController::class)]
class FieldTypeCrudControllerTest extends MockeryTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(FieldType::class, FieldTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new FieldTypeCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(5, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('code', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertEquals('title', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[2]);
        $this->assertEquals('translatable', $fields[2]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[3]);
        $this->assertEquals('filterable', $fields[3]->getAsDto()->getProperty());
        $this->assertInstanceOf(ChoiceField::class, $fields[4]);
        $this->assertEquals('inputType', $fields[4]->getAsDto()->getProperty());
    }

    public function testConfigureCrudSetsAutocompleteCallback(): void
    {
        $controller = new FieldTypeCrudController();

        $crud = Crud::new();

        $configuredCrud = $controller->configureCrud($crud);

        $this->assertIsCallable(
            $configuredCrud->getAsDto()->getAutocompleteCallback()
        );
    }
}
