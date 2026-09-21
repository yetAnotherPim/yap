<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Repository\FieldTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(FieldTypeCrudController::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(TimestampableEntity::class)]
class FieldTypeCrudControllerTest extends MockeryTestCase
{
    /** @var TitleTranslationFieldRenderer|MockInterface */
    private TitleTranslationFieldRenderer $titleTranslationFieldRendererMock;

    /** @var TranslatorInterface|MockInterface */
    private TranslatorInterface $translatorMock;

    /** @var TitleTranslationSynchronizer|MockInterface */
    private TitleTranslationSynchronizer $titleTranslationSynchronizerMock;

    /** @var FieldTypeRepository|MockInterface */
    private FieldTypeRepository $fieldTypeRepositoryMock;

    private FieldTypeCrudController $controller;

    public function setUp(): void
    {
        $this->translatorMock = \Mockery::mock(TranslatorInterface::class);
        $this->titleTranslationFieldRendererMock = \Mockery::mock(TitleTranslationFieldRenderer::class);
        $this->titleTranslationSynchronizerMock = \Mockery::mock(TitleTranslationSynchronizer::class);
        $this->fieldTypeRepositoryMock = \Mockery::mock(FieldTypeRepository::class);

        $this->controller = new FieldTypeCrudController(
            $this->translatorMock,
            $this->titleTranslationFieldRendererMock,
            $this->titleTranslationSynchronizerMock,
            $this->fieldTypeRepositoryMock,
        );

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(FieldType::class, FieldTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $controller = new FieldTypeCrudController(
            $this->translatorMock,
            $this->titleTranslationFieldRendererMock,
            $this->titleTranslationSynchronizerMock,
            $this->fieldTypeRepositoryMock,
        );

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(9, $fields);

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
    }

    public function testConfigureFieldsForReturnsExpectedFieldsOnFormPage(): void
    {
        $controller = \Mockery::mock(
            FieldTypeCrudController::class,
            [$this->translatorMock, $this->titleTranslationFieldRendererMock, $this->titleTranslationSynchronizerMock, $this->fieldTypeRepositoryMock],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn(null);

        $this->titleTranslationFieldRendererMock
            ->shouldReceive('render')
            ->once()
            ->with(
                null,
                \Mockery::type(TranslatableMessage::class),
            )
            ->andReturn([]);

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_EDIT));

        $this->assertCount(9, $fields);

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
    }

    public function testConfigureCrudSetsAutocompleteCallback(): void
    {
        $controller = new FieldTypeCrudController(
            $this->translatorMock,
            $this->titleTranslationFieldRendererMock,
            $this->titleTranslationSynchronizerMock,
            $this->fieldTypeRepositoryMock,
        );

        $crud = Crud::new();

        $configuredCrud = $controller->configureCrud($crud);

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInPlural());
        $this->assertEquals(
            'field_type.entity.title_plural',
            $configuredCrud->getAsDto()->getEntityLabelInPlural()->getMessage()
        );

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInSingular());
        $this->assertEquals(
            'field_type.entity.title',
            $configuredCrud->getAsDto()->getEntityLabelInSingular()->getMessage()
        );

        $this->assertIsCallable(
            $configuredCrud->getAsDto()->getAutocompleteCallback()
        );
    }

    public function testUpdateEntitySynchronizesTitleTranslations(): void
    {
        $requestData = [
            'title-de_DE' => 'Deutscher Titel',
            'title-fr_FR' => 'Titre français',
        ];

        $request = new Request(
            request: [
                'FieldType' => $requestData,
            ],
        );

        $requestContext = new RequestContext(
            $request,
            null,
        );

        $context = AdminContext::forTesting(
            requestContext: $requestContext,
        );

        $controller = \Mockery::mock(
            FieldTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this->fieldTypeRepositoryMock,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn($context);

        $fieldType = \Mockery::mock(FieldType::class);

        $this->titleTranslationSynchronizerMock
            ->shouldReceive('synchronizeTitleTranslations')
            ->once()
            ->with(
                $fieldType,
                $requestData,
            );

        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($fieldType);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $controller->updateEntity(
            $entityManager,
            $fieldType,
        );
    }

    public function testCreateEntitySynchronizesTitleTranslations(): void
    {
        $requestData = [
            'title-de_DE' => 'Deutscher Titel',
            'title-fr_FR' => 'Titre français',
        ];

        $request = new Request(
            request: [
                'FieldType' => $requestData,
            ],
        );

        $requestContext = new RequestContext(
            $request,
            null,
        );

        $context = AdminContext::forTesting(
            requestContext: $requestContext,
        );

        $controller = \Mockery::mock(
            FieldTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this->fieldTypeRepositoryMock,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn($context);

        $this->titleTranslationSynchronizerMock
            ->shouldReceive('synchronizeTitleTranslations')
            ->once()
            ->with(
                \Mockery::type(FieldType::class),
                $requestData,
            );

        $entity = $controller->createEntity(FieldType::class);

        $this->assertInstanceOf(FieldType::class, $entity);
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
