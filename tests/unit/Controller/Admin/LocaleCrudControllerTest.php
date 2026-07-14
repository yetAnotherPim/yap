<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\LocaleCrudController;
use App\Entity\Locale\Locale;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LocaleCrudController::class)]
class LocaleCrudControllerTest extends MockeryTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(Locale::class, LocaleCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new LocaleCrudController();
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(1, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('id', $fields[0]->getAsDto()->getProperty());
    }
}
