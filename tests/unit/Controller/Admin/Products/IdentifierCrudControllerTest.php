<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Products;

use App\Controller\Admin\Products\IdentifierCrudController;
use App\Entity\Product\Identifier\Identifier;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IdentifierCrudController::class)]
class IdentifierCrudControllerTest extends MockeryTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(Identifier::class, IdentifierCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new IdentifierCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(1, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('value', $fields[0]->getAsDto()->getProperty());
    }
}
