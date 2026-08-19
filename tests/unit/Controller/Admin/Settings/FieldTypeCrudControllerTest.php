<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Entity\Product\Field\Type\FieldType;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldTypeCrudController::class)]
class FieldTypeCrudControllerTest extends MockeryTestCase
{
    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepositoryMock;

    public function setUp(): void
    {
        $this->localeRepositoryMock = \Mockery::mock(LocaleRepository::class);

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(FieldType::class, FieldTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new FieldTypeCrudController($this->localeRepositoryMock);

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableLocaleIds')
            ->once()
            ->andReturn(['es_ES', 'de_DE']);

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(12, $fields);

        $this->assertInstanceOf(FormField::class, $fields[0]);
        $this->assertInstanceOf(FormField::class, $fields[1]);
        $this->assertInstanceOf(TextField::class, $fields[2]);
        $this->assertEquals('code', $fields[2]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[3]);
        $this->assertEquals('title', $fields[3]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[4]);
        $this->assertEquals('translatable', $fields[4]->getAsDto()->getProperty());
        $this->assertInstanceOf(BooleanField::class, $fields[5]);
        $this->assertEquals('filterable', $fields[5]->getAsDto()->getProperty());
        $this->assertInstanceOf(ChoiceField::class, $fields[6]);
        $this->assertEquals('inputType', $fields[6]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[7]);
        $this->assertEquals('decimalSeparator', $fields[7]->getAsDto()->getProperty());
        $this->assertInstanceOf(FormField::class, $fields[8]);
        $this->assertInstanceOf(FormField::class, $fields[9]);
        $this->assertInstanceOf(LocaleField::class, $fields[10]);

        // TODO: add translation fields
    }

    public function testConfigureCrudSetsAutocompleteCallback(): void
    {
        $controller = new FieldTypeCrudController($this->localeRepositoryMock);

        $crud = Crud::new();

        $configuredCrud = $controller->configureCrud($crud);

        $this->assertIsCallable(
            $configuredCrud->getAsDto()->getAutocompleteCallback()
        );
    }
}
