<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\IdentifierTypeCrudController;
use App\Entity\Product\Identifier\Type\IdentifierType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IdentifierTypeCrudController::class)]
class IdentifierTypeCrudControllerTest extends MockeryTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(IdentifierType::class, IdentifierTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new IdentifierTypeCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('title', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(ChoiceField::class, $fields[1]);
        $this->assertEquals('dataType', $fields[1]->getAsDto()->getProperty());
    }
}
