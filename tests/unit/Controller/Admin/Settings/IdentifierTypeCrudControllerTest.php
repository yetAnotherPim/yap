<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\IdentifierTypeCrudController;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Repository\IdentifierTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(IdentifierTypeCrudController::class)]
class IdentifierTypeCrudControllerTest extends MockeryTestCase
{
    /** @var IdentifierTypeRepository|MockInterface */
    private IdentifierTypeRepository $identifierTypeRepositoryMock;

    private IdentifierTypeCrudController $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->identifierTypeRepositoryMock = \Mockery::mock(IdentifierTypeRepository::class);

        $this->controller = new IdentifierTypeCrudController($this->identifierTypeRepositoryMock);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(IdentifierType::class, IdentifierTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new IdentifierTypeCrudController($this->identifierTypeRepositoryMock);
        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('title', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(ChoiceField::class, $fields[1]);
        $this->assertEquals('dataType', $fields[1]->getAsDto()->getProperty());
    }

    public function testConfigureCrud(): void
    {
        $controller = new IdentifierTypeCrudController($this->identifierTypeRepositoryMock);

        $crud = Crud::new();

        $configuredCrud = $controller->configureCrud($crud);

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInPlural());
        $this->assertEquals(
            'identifier_type.entity.title_plural',
            $configuredCrud->getAsDto()->getEntityLabelInPlural()->getMessage()
        );

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInSingular());
        $this->assertEquals(
            'identifier_type.entity.title',
            $configuredCrud->getAsDto()->getEntityLabelInSingular()->getMessage()
        );
    }

    public function testConfigureActions(): void
    {
        $actions = Actions::new()
            ->add(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_DETAIL, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::BATCH_DELETE);

        $result = $this->controller->configureActions($actions);

        $this->assertSame($actions, $result);

        $indexActions = $result->getAsDto(Crud::PAGE_INDEX);
        $detailActions = $result->getAsDto(Crud::PAGE_DETAIL);

        $this->assertNotNull(
            $indexActions->getAction(
                Crud::PAGE_INDEX,
                Action::DELETE,
            ),
        );

        $this->assertNotNull(
            $detailActions->getAction(
                Crud::PAGE_DETAIL,
                Action::DELETE,
            ),
        );
    }
}
