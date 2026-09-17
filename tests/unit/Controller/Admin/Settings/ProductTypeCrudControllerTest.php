<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\ProductTypeCrudController;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Form\ProductType\DefaultTranslation\Synchronizer\ProductTypeFieldConfigurationDefaultTranslationSynchronizer;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FormFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Layout\EaFormFieldsetOpenType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductTypeCrudController::class)]
#[UsesClass(InputType::class)]
#[UsesClass(ProductType::class)]
class ProductTypeCrudControllerTest extends MockeryTestCase
{
    private ProductTypeCrudController $controller;

    /** @var TranslatorInterface|MockInterface */
    private TranslatorInterface $translatorMock;

    /** @var TitleTranslationFieldRenderer|MockInterface */
    private TitleTranslationFieldRenderer $titleTranslationFieldRendererMock;

    /** @var TitleTranslationSynchronizer|MockInterface */
    private TitleTranslationSynchronizer $titleTranslationSynchronizerMock;

    /** @var ProductTypeFieldConfigurationDefaultTranslationSynchronizer|MockInterface */
    private ProductTypeFieldConfigurationDefaultTranslationSynchronizer $productTypeFieldConfigurationDefaultTranslationSynchronizerMock;

    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepositoryMock;

    public function setUp(): void
    {
        $this->translatorMock = \Mockery::mock(TranslatorInterface::class);
        $this->titleTranslationFieldRendererMock = \Mockery::mock(TitleTranslationFieldRenderer::class);
        $this->titleTranslationSynchronizerMock = \Mockery::mock(TitleTranslationSynchronizer::class);
        $this->productTypeFieldConfigurationDefaultTranslationSynchronizerMock = \Mockery::mock(ProductTypeFieldConfigurationDefaultTranslationSynchronizer::class);
        $this->localeRepositoryMock = \Mockery::mock(LocaleRepository::class);

        $this->controller = new ProductTypeCrudController(
            $this->translatorMock,
            $this->titleTranslationFieldRendererMock,
            $this->titleTranslationSynchronizerMock,
            $this->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
            $this->localeRepositoryMock
        );

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(ProductType::class, ProductTypeCrudController::getEntityFqcn());
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertEquals('code', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertEquals('title', $fields[1]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsForReturnsExpectedFieldsOnFormPage(): void
    {
        $controller = \Mockery::mock(
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
            ],
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

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_NEW));

        $this->assertCount(8, $fields);

        $this->assertInstanceOf(FormField::class, $fields[0]);
        $this->assertInstanceOf(FormField::class, $fields[1]);
        $this->assertInstanceOf(TextField::class, $fields[2]);
        $this->assertEquals('code', $fields[2]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[3]);
        $this->assertEquals('title', $fields[3]->getAsDto()->getProperty());
        $this->assertInstanceOf(FormField::class, $fields[4]);
        $this->assertInstanceOf(FormField::class, $fields[5]);
        $this->assertInstanceOf(FormField::class, $fields[6]);
        $this->assertEquals(EaFormFieldsetOpenType::class, $fields[6]->getAsDto()->getFormType());
        $this->assertInstanceOf(CollectionField::class, $fields[7]);
        $this->assertEquals('productTypeFieldConfigurations', $fields[7]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsOnFormPageEntryToStringMethodReturnsEmptyStringWithoutFieldType(): void
    {
        $controller = \Mockery::mock(
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
            ])
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

        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $fields = iterator_to_array(
            $controller->configureFields(Crud::PAGE_NEW)
        );

        /** @var CollectionField $collectionField */
        $collectionField = $fields[7];

        $entryToStringMethod = $collectionField
            ->getAsDto()
            ->getCustomOption('entryToStringMethod');

        $this->assertIsCallable($entryToStringMethod);
        $this->assertSame('', $entryToStringMethod($configuration));
    }

    public function testConfigureFieldsOnFormPageEntryToStringMethodReturnsFormattedFieldType(): void
    {
        $configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);

        $controller = \Mockery::mock(
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
            ],
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

        $this->translatorMock
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de_DE');

        $configuration
            ->shouldReceive('getFieldType')
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('is_active');

        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::CheckBox);

        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->andReturn('Boolean field');

        $fields = iterator_to_array(
            $controller->configureFields(Crud::PAGE_NEW)
        );

        /** @var CollectionField $collectionField */
        $collectionField = $fields[7];

        $entryToStringMethod = $collectionField
            ->getAsDto()
            ->getCustomOption('entryToStringMethod');

        $this->translatorMock
            ->shouldReceive('trans')
            ->once()
            ->with('field_type.input_type.checkbox', [], 'admin', null)
            ->andReturn('CheckBox');

        $this->assertIsCallable($entryToStringMethod);
        $this->assertSame(
            'Boolean field (is_active - CheckBox)',
            $entryToStringMethod($configuration)
        );
    }

    public function testConfigureCrud(): void
    {
        $crud = Crud::new();

        $configuredCrud = $this->controller->configureCrud($crud);

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInPlural());
        $this->assertEquals(
            'product_type.entity.title_plural',
            $configuredCrud->getAsDto()->getEntityLabelInPlural()->getMessage()
        );

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInSingular());
        $this->assertEquals(
            'product_type.entity.title',
            $configuredCrud->getAsDto()->getEntityLabelInSingular()->getMessage()
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
                'ProductType' => $requestData,
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
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn($context);

        $productType = \Mockery::mock(ProductType::class);

        $this->titleTranslationSynchronizerMock
            ->shouldReceive('synchronizeTitleTranslations')
            ->once()
            ->with(
                $productType,
                $requestData,
            );

        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($productType);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $controller->updateEntity(
            $entityManager,
            $productType,
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
                'ProductType' => $requestData,
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
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
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
                \Mockery::type(ProductType::class),
                $requestData,
            );

        $entity = $controller->createEntity(ProductType::class);

        $this->assertInstanceOf(ProductType::class, $entity);
    }

    public function testAddDefaultTranslationListenerSynchronizesTranslations(): void
    {
        $configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class,
        );

        $deTranslationForm = \Mockery::mock(FormInterface::class);
        $deTranslationForm
            ->shouldReceive('getData')
            ->once()
            ->andReturn('Deutscher Standardwert');

        $esTranslationForm = \Mockery::mock(FormInterface::class);
        $esTranslationForm
            ->shouldReceive('getData')
            ->once()
            ->andReturn(null);

        $invalidTranslationForm = \Mockery::mock(FormInterface::class);
        $invalidTranslationForm
            ->shouldReceive('getData')
            ->once()
            ->andReturn(['invalid']);

        $translationsForm = \Mockery::mock(FormInterface::class);
        $translationsForm
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(
                new \ArrayIterator([
                    'de_DE' => $deTranslationForm,
                    'es_ES' => $esTranslationForm,
                    'invalid' => $invalidTranslationForm,
                ]),
            );

        $configurationForm = \Mockery::mock(FormInterface::class);
        $configurationForm
            ->shouldReceive('getData')
            ->once()
            ->andReturn($configuration);

        $configurationForm
            ->shouldReceive('has')
            ->once()
            ->with(
                'productTypeFieldConfigurationDefaultTranslations',
            )
            ->andReturnTrue();

        $configurationForm
            ->shouldReceive('get')
            ->once()
            ->with(
                'productTypeFieldConfigurationDefaultTranslations',
            )
            ->andReturn($translationsForm);

        $configurationsForm = \Mockery::mock(FormInterface::class);
        $configurationsForm
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(
                new \ArrayIterator([
                    $configurationForm,
                ]),
            );

        $form = \Mockery::mock(FormInterface::class);
        $form
            ->shouldReceive('has')
            ->once()
            ->with('productTypeFieldConfigurations')
            ->andReturnTrue();

        $form
            ->shouldReceive('get')
            ->once()
            ->with('productTypeFieldConfigurations')
            ->andReturn($configurationsForm);

        $locales = new ArrayCollection();

        $this->localeRepositoryMock
            ->shouldReceive(
                'getAvailableContentTranslationLocales',
            )
            ->once()
            ->andReturn($locales);

        $this
            ->productTypeFieldConfigurationDefaultTranslationSynchronizerMock
            ->shouldReceive('synchronize')
            ->once()
            ->with(
                $configuration,
                $locales,
                [
                    'de_DE' => 'Deutscher Standardwert',
                    'es_ES' => null,
                ],
            );

        /** @var FormBuilderInterface<ProductType>&MockInterface $builder */
        $builder = \Mockery::mock(FormBuilderInterface::class);

        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::POST_SUBMIT,
                \Mockery::on(
                    static function (mixed $callback) use (&$listener): bool {
                        if (!$callback instanceof \Closure) {
                            return false;
                        }

                        $listener = $callback;

                        return true;
                    },
                ),
            )
            ->andReturnSelf();

        $controller = \Mockery::mock(
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this
                    ->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        /* @phpstan-ignore-next-line */
        $controller->addDefaultTranslationListener($builder);

        if (!$listener instanceof \Closure) {
            $this->fail('POST_SUBMIT listener was not registered.');
        }

        $listener(
            new FormEvent(
                $form,
                null,
            ),
        );
    }

    public function testAddDefaultTranslationListenerReturnsWhenConfigurationsFieldDoesNotExist(): void
    {
        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('has')
            ->once()
            ->with('productTypeFieldConfigurations')
            ->andReturnFalse();

        $this
            ->productTypeFieldConfigurationDefaultTranslationSynchronizerMock
            ->shouldNotReceive('synchronize');

        $this->localeRepositoryMock
            ->shouldNotReceive(
                'getAvailableContentTranslationLocales',
            );

        $listener = $this->getDefaultTranslationListener();

        $listener(
            new FormEvent(
                $form,
                null,
            ),
        );
    }

    public function testAddDefaultTranslationListenerIgnoresInvalidConfiguration(): void
    {
        $configurationForm = \Mockery::mock(FormInterface::class);

        $configurationForm
            ->shouldReceive('getData')
            ->once()
            ->andReturn(null);

        $configurationForm
            ->shouldNotReceive('has');

        $configurationsForm = \Mockery::mock(FormInterface::class);

        $configurationsForm
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(
                new \ArrayIterator([
                    $configurationForm,
                ]),
            );

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('has')
            ->once()
            ->with('productTypeFieldConfigurations')
            ->andReturnTrue();

        $form
            ->shouldReceive('get')
            ->once()
            ->with('productTypeFieldConfigurations')
            ->andReturn($configurationsForm);

        $this
            ->productTypeFieldConfigurationDefaultTranslationSynchronizerMock
            ->shouldNotReceive('synchronize');

        $this->localeRepositoryMock
            ->shouldNotReceive(
                'getAvailableContentTranslationLocales',
            );

        $listener = $this->getDefaultTranslationListener();

        $listener(
            new FormEvent(
                $form,
                null,
            ),
        );
    }

    public function testAddDefaultTranslationListenerIgnoresConfigurationWithoutTranslationsField(): void
    {
        $configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class,
        );

        $configurationForm = \Mockery::mock(FormInterface::class);

        $configurationForm
            ->shouldReceive('getData')
            ->once()
            ->andReturn($configuration);

        $configurationForm
            ->shouldReceive('has')
            ->once()
            ->with(
                'productTypeFieldConfigurationDefaultTranslations',
            )
            ->andReturnFalse();

        $configurationForm
            ->shouldNotReceive('get');

        $configurationsForm = \Mockery::mock(FormInterface::class);

        $configurationsForm
            ->shouldReceive('getIterator')
            ->once()
            ->andReturn(
                new \ArrayIterator([
                    $configurationForm,
                ]),
            );

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('has')
            ->once()
            ->with('productTypeFieldConfigurations')
            ->andReturnTrue();

        $form
            ->shouldReceive('get')
            ->once()
            ->with('productTypeFieldConfigurations')
            ->andReturn($configurationsForm);

        $this
            ->productTypeFieldConfigurationDefaultTranslationSynchronizerMock
            ->shouldNotReceive('synchronize');

        $this->localeRepositoryMock
            ->shouldNotReceive(
                'getAvailableContentTranslationLocales',
            );

        $listener = $this->getDefaultTranslationListener();

        $listener(
            new FormEvent(
                $form,
                null,
            ),
        );
    }

    /**
     * @return \Closure(FormEvent): void
     */
    private function getDefaultTranslationListener(): \Closure
    {
        /**
         * @var FormBuilderInterface<ProductType>&MockInterface $builder
         */
        $builder = \Mockery::mock(
            FormBuilderInterface::class,
        );

        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::POST_SUBMIT,
                \Mockery::on(
                    static function (mixed $callback) use (&$listener): bool {
                        if (!$callback instanceof \Closure) {
                            return false;
                        }

                        $listener = $callback;

                        return true;
                    },
                ),
            )
            ->andReturnSelf();

        $controller = \Mockery::mock(
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this
                    ->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        /* @phpstan-ignore-next-line */
        $controller->addDefaultTranslationListener(
            $builder,
        );

        if (!$listener instanceof \Closure) {
            $this->fail(
                'POST_SUBMIT listener was not registered.',
            );
        }

        return $listener;
    }

    public function testCreateEditFormBuilderAddsDefaultTranslationListener(): void
    {
        /** @phpstan-var EntityDto<ProductType> $entityDto */
        $entityDto = new EntityDto(
            ProductType::class,
            \Mockery::mock(ClassMetadata::class),
        );

        $formOptions = KeyValueStore::new();

        /** @phpstan-var AdminContext<ProductType> $context */
        $context = AdminContext::forTesting();

        /**
         * @var FormBuilderInterface<ProductType>&MockInterface $builder
         */
        $builder = \Mockery::mock(
            FormBuilderInterface::class,
        );

        $formFactory = \Mockery::mock(\stdClass::class);

        $formFactory
            ->shouldReceive('createEditFormBuilder')
            ->once()
            ->with(
                $entityDto,
                $formOptions,
                $context,
            )
            ->andReturn($builder);

        $container = \Mockery::mock(
            ContainerInterface::class,
        );

        $container
            ->shouldReceive('get')
            ->once()
            ->with(FormFactory::class)
            ->andReturn($formFactory);

        $controller = \Mockery::mock(
            ProductTypeCrudController::class,
            [
                $this->translatorMock,
                $this->titleTranslationFieldRendererMock,
                $this->titleTranslationSynchronizerMock,
                $this
                    ->productTypeFieldConfigurationDefaultTranslationSynchronizerMock,
                $this->localeRepositoryMock,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->setContainer($container);

        $controller
            ->shouldReceive('addDefaultTranslationListener')
            ->once()
            ->with($builder);

        $result = $controller->createEditFormBuilder(
            $entityDto,
            $formOptions,
            $context,
        );

        $this->assertSame(
            $builder,
            $result,
        );
    }
}
