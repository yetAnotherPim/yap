<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\LocaleCrudController;
use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LocaleCrudController::class)]
class LocaleCrudControllerTest extends MockeryTestCase
{
    private LocaleRepository $localeRepository;
    private LocaleCrudController $controller;

    public function setUp(): void
    {
        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->controller = new LocaleCrudController($this->localeRepository);

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(Locale::class, LocaleCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('id', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[1]);
        $this->assertEquals('isDefault', $fields[1]->getAsDto()->getProperty());
    }
}
