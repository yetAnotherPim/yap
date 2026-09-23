<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Products;

use App\Controller\Admin\Products\ProductCrudController;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Identifier\IdentifierManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\CrudContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Model\FileUploadState;
use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option\ButtonVariant;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductCrudController::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(Product::class)]
#[UsesClass(TimestampableEntity::class)]
class ProductCrudControllerTest extends MockeryTestCase
{
    /** @var ProductFieldsComposer&MockInterface */
    private ProductFieldsComposer $productFieldsRenderer;
    /** @var IdentifierManager&MockInterface */
    private IdentifierManager $identifierManager;

    /** @var FieldSynchronizer&MockInterface */
    private FieldSynchronizer $fieldSynchronizer;

    /** @var TranslatorInterface&MockInterface */
    private TranslatorInterface $translator;

    /** @var ProductFilterComposer&MockInterface */
    private ProductFilterComposer $productFilterComposer;
    private ProductCrudController $controller;

    public function setUp(): void
    {
        $this->productFieldsRenderer = \Mockery::mock(ProductFieldsComposer::class);
        $this->identifierManager = \Mockery::mock(IdentifierManager::class);
        $this->fieldSynchronizer = \Mockery::mock(FieldSynchronizer::class);
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->productFilterComposer = \Mockery::mock(ProductFilterComposer::class);

        $this->controller = new ProductCrudController(
            $this->identifierManager,
            $this->productFieldsRenderer,
            $this->fieldSynchronizer,
            $this->translator,
            $this->productFilterComposer
        );

        parent::setUp();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(Product::class, ProductCrudController::getEntityFqcn());
    }

    public function testCreateEntityCreatesProduct(): void
    {
        $this->identifierManager
            ->shouldReceive('initializeIdentifiers')
            ->once();

        $product = $this->controller->createEntity(Product::class);

        $this->assertInstanceOf(Product::class, $product);
    }

    public function testConfigureActions(): void
    {
        $actions = Actions::new()
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_RETURN);

        $result = $this->controller->configureActions($actions);

        $this->assertCount(3, $result->getAsDto(Crud::PAGE_NEW)->getActions());
        $this->assertEquals(ButtonVariant::Default, $result->getAsDto(Crud::PAGE_NEW)->getAction(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)?->getVariant());
        $this->assertEquals(ButtonVariant::Default, $result->getAsDto(Crud::PAGE_NEW)->getAction(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)?->getVariant());
        $this->assertEquals(ButtonVariant::Primary, $result->getAsDto(Crud::PAGE_NEW)->getAction(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)?->getVariant());
    }

    public function testCreateEntityInitializesIdentifiers(): void
    {
        $this->identifierManager
            ->shouldReceive('initializeIdentifiers')
            ->once()
            ->with(Product::class);

        $this->controller->createEntity(Product::class);
    }

    public function testCreateEditFormInitializesMissingIdentifiers(): void
    {
        $product = \Mockery::mock(Product::class);

        $this->identifierManager
            ->shouldReceive('initializeIdentifiers')
            ->once()
            ->with($product);

        /** @var EntityDto<Product> $entityDto */
        $entityDto = new EntityDto(
            Product::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            $product
        );

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [$this->identifierManager, $this->productFieldsRenderer, $this->fieldSynchronizer, $this->translator, $this->productFilterComposer]
        )->makePartial();

        /** @phpstan-var AdminContext<Product> $context */
        $context = AdminContext::forTesting();

        $this->expectException(\Error::class);
        $this->expectExceptionMessageIsOrContains(
            'AbstractController::$container'
        );

        $controller->createEditForm(
            $entityDto,
            KeyValueStore::new(),
            $context
        );
    }

    public function testCreateEditFormSkipsInitializationForNonProductInstance(): void
    {
        $this->identifierManager
            ->shouldNotReceive('initializeIdentifiers')
            ->never();

        /** @var EntityDto<Product> $entityDto */
        $entityDto = new EntityDto(
            \stdClass::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            new \stdClass()
        );

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [$this->identifierManager, $this->productFieldsRenderer, $this->fieldSynchronizer, $this->translator, $this->productFilterComposer]
        )->makePartial();

        $this->expectException(\Error::class);
        $this->expectExceptionMessageIsOrContains(
            'AbstractController::$container'
        );

        /** @phpstan-var AdminContext<Product> $context */
        $context = AdminContext::forTesting();

        $controller->createEditForm(
            $entityDto,
            KeyValueStore::new(),
            $context
        );
    }

    public function testPersistEntityRemovesEmptyIdentifiers(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $product = \Mockery::mock(Product::class);

        $this->identifierManager
            ->shouldReceive('clearUnsetIdentifiers')
            ->once()
            ->with($product);

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($product);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $this->fieldSynchronizer
            ->shouldReceive('synchronizeFields')
            ->once()
            ->with($product);

        $this->controller->persistEntity($entityManager, $product);
    }

    public function testUpdateEntityRemovesEmptyIdentifiers(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('updateUpdatedAt')
            ->once();

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($product);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $request = new Request(
            [],
            ['Product' => []],
        );

        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting($request),
        );

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [
                $this->identifierManager,
                $this->productFieldsRenderer,
                $this->fieldSynchronizer,
                $this->translator,
                $this->productFilterComposer,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->identifierManager
            ->shouldReceive('clearUnsetIdentifiers')
            ->once()
            ->with($product);

        $this->fieldSynchronizer
            ->shouldReceive('synchronizeFields')
            ->once()
            ->with($product, [], []);

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn($context);

        $controller->updateEntity($entityManager, $product);
    }

    public function testUpdateEntityRemovesEmptyIdentifiersIfRequestDataIsNotAvailable(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('updateUpdatedAt')
            ->once();

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($product);

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $this->identifierManager
            ->shouldReceive('clearUnsetIdentifiers')
            ->once()
            ->with($product);

        $this->fieldSynchronizer
            ->shouldReceive('synchronizeFields')
            ->never();

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [
                $this->identifierManager,
                $this->productFieldsRenderer,
                $this->fieldSynchronizer,
                $this->translator,
                $this->productFilterComposer,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn(null);

        $controller->updateEntity($entityManager, $product);
    }

    public function testConfigureFieldsForIndexPageReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertCount(4, $fields);

        $this->assertInstanceOf(CollectionField::class, $fields[0]);
        $this->assertEquals('identifiers', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(AssociationField::class, $fields[1]);
        $this->assertEquals('productType', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(DateTimeField::class, $fields[2]);
        $this->assertEquals('createdAt', $fields[2]->getAsDto()->getProperty());
        $this->assertInstanceOf(DateTimeField::class, $fields[3]);
        $this->assertEquals('updatedAt', $fields[3]->getAsDto()->getProperty());
    }

    public function testConfigureFieldsForFormPageReturnsExpectedFields(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_NEW));

        $this->assertCount(5, $fields);

        $this->assertInstanceOf(\EasyCorp\Bundle\EasyAdminBundle\Field\FormField::class, $fields[0]);
        $this->assertInstanceOf(AssociationField::class, $fields[1]);
        $this->assertEquals('productType', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(CollectionField::class, $fields[2]);
        $this->assertEquals('identifiers', $fields[2]->getAsDto()->getProperty());
        $this->assertEquals(false, $fields[2]->getAsDto()->getFormTypeOption('allowAdd'));
        $this->assertEquals(false, $fields[2]->getAsDto()->getFormTypeOption('allowDelete'));
        $this->assertInstanceOf(DateTimeField::class, $fields[3]);
        $this->assertEquals('createdAt', $fields[3]->getAsDto()->getProperty());
        $this->assertEquals('disabled', $fields[3]->getAsDto()->getFormTypeOption('disabled'));
        $this->assertInstanceOf(DateTimeField::class, $fields[4]);
        $this->assertEquals('updatedAt', $fields[4]->getAsDto()->getProperty());
        $this->assertEquals('disabled', $fields[4]->getAsDto()->getFormTypeOption('disabled'));
    }

    public function testConfigureFieldsForEditPageRendersProductFields(): void
    {
        $product = \Mockery::mock(Product::class);

        $this->productFieldsRenderer
            ->shouldReceive('compose')
            ->once()
            ->with($product)
            ->andReturn([]);

        $entityDto = new EntityDto(
            Product::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            $product
        );

        $crudContext = CrudContext::forTesting(
            entityDto: $entityDto,
        );

        $context = AdminContext::forTesting(
            crudContext: $crudContext,
        );

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [$this->identifierManager, $this->productFieldsRenderer, $this->fieldSynchronizer, $this->translator, $this->productFilterComposer]
        )->makePartial()
        ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn($context);

        $fields = iterator_to_array(
            $controller->configureFields(Crud::PAGE_EDIT)
        );

        $this->assertCount(5, $fields);
    }

    public function testConfigureFieldsForEditPageSkipsProductFieldsForNonProductEntity(): void
    {
        $entityDto = new EntityDto(
            \stdClass::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            new \stdClass()
        );

        $crudContext = CrudContext::forTesting(
            entityDto: $entityDto,
        );

        $context = AdminContext::forTesting(
            crudContext: $crudContext,
        );

        $this->productFieldsRenderer
            ->shouldNotReceive('compose');

        $controller = \Mockery::mock(
            ProductCrudController::class,
            [$this->identifierManager, $this->productFieldsRenderer, $this->fieldSynchronizer, $this->translator, $this->productFilterComposer]
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getContext')
            ->once()
            ->andReturn($context);

        $fields = iterator_to_array(
            $controller->configureFields(Crud::PAGE_EDIT)
        );

        $this->assertCount(5, $fields);
    }

    public function testConfigureCrudHidesNullValues(): void
    {
        $crud = Crud::new();

        $configuredCrud = $this->controller->configureCrud($crud);

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInPlural());
        $this->assertEquals(
            'product.entity.title_plural',
            $configuredCrud->getAsDto()->getEntityLabelInPlural()->getMessage()
        );

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInSingular());
        $this->assertEquals(
            'product.entity.title',
            $configuredCrud->getAsDto()->getEntityLabelInSingular()->getMessage()
        );

        $this->assertTrue(
            $configuredCrud->getAsDto()->areNullValuesHidden()
        );
    }

    public function testProcessUploadedFilesCollectsUploadedFilePaths(): void
    {
        $file = $this->createFileUploadFormMock(
            name: 'field_file',
            data: '2026/08/14/indexcontroller-file.pdf',
        );

        $form = $this->createFormMock([$file]);

        $method = new \ReflectionMethod(
            ProductCrudController::class,
            'processUploadedFiles',
        );

        $method->invoke($this->controller, $form);

        $this->assertSame(
            [
                'field_file' => '2026/08/14/indexcontroller-file.pdf',
            ],
            $this->getUploadedFilePaths(),
        );
    }

    public function testCollectUploadedFilePathsCollectsFilePath(): void
    {
        $file = $this->createFileUploadFormMock(
            name: 'field_file',
            data: '2026/08/14/indexcontroller-file.pdf',
        );

        $form = $this->createFormMock([$file]);

        $this->invokeCollectUploadedFilePaths($form);

        $this->assertSame(
            [
                'field_file' => '2026/08/14/indexcontroller-file.pdf',
            ],
            $this->getUploadedFilePaths(),
        );
    }

    public function testCollectUploadedFilePathsCollectsNestedFilePath(): void
    {
        $file = $this->createFileUploadFormMock(
            name: 'field_file',
            data: '2026/08/14/indexcontroller-file.pdf',
        );

        $configuration = $this->createFormMock(
            [$file],
            name: '1',
            compound: true,
        );

        $form = $this->createFormMock([$configuration]);

        $this->invokeCollectUploadedFilePaths($form);

        $this->assertSame(
            [
                '1[field_file]' => '2026/08/14/indexcontroller-file.pdf',
            ],
            $this->getUploadedFilePaths(),
        );
    }

    public function testCollectUploadedFilePathsIgnoresNonFileUploadFields(): void
    {
        $field = $this->createNonFileFormMock('field_title');

        $form = $this->createFormMock([$field]);

        $this->invokeCollectUploadedFilePaths($form);

        $this->assertSame([], $this->getUploadedFilePaths());
    }

    public function testCollectUploadedFilePathsIgnoresEmptyFilePath(): void
    {
        $file = $this->createFileUploadFormMock(
            name: 'field_file',
            data: '',
        );

        $form = $this->createFormMock([$file]);

        $this->invokeCollectUploadedFilePaths($form);

        $this->assertSame(['field_file' => ''], $this->getUploadedFilePaths());
    }

    /**
     * @param FormInterface<Product> $form
     */
    private function invokeCollectUploadedFilePaths(FormInterface $form): void
    {
        $method = new \ReflectionMethod(
            ProductCrudController::class,
            'collectUploadedFilePaths',
        );

        $method->invoke($this->controller, $form);
    }

    /**
     * @return array<string, string>
     */
    private function getUploadedFilePaths(): array
    {
        $property = new \ReflectionProperty(
            ProductCrudController::class,
            'uploadedFilePaths',
        );

        /** @var array<string,string> $value */
        $value = $property->getValue($this->controller);

        return $value;
    }

    /**
     * @return FormInterface<Product>
     */
    private function createFileUploadFormMock(
        string $name,
        string $data,
    ): FormInterface {
        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('getName')
            ->andReturn($name);

        $form
            ->shouldReceive('getData')
            ->andReturn($data);

        $fileUploadType = \Mockery::mock(FileUploadType::class);

        $resolvedType = \Mockery::mock(ResolvedFormTypeInterface::class);

        $resolvedType
            ->shouldReceive('getInnerType')
            ->andReturn($fileUploadType);

        $state = \Mockery::mock(FileUploadState::class);

        $state
            ->shouldReceive('isModified')
            ->andReturn(false);

        $config = \Mockery::mock(FormConfigInterface::class);

        $config
            ->shouldReceive('getType')
            ->andReturn($resolvedType);

        $config
            ->shouldReceive('getCompound')
            ->andReturn(false);

        $config
            ->shouldReceive('getAttribute')
            ->with('state')
            ->andReturn($state);

        $config
            ->shouldReceive('getOption')
            ->with('upload_dir')
            ->andReturn('');

        $form
            ->shouldReceive('getConfig')
            ->andReturn($config);

        return $form;
    }

    /**
     * @param array<int, FormInterface<Product>> $children
     *
     * @return FormInterface<Product>
     */
    private function createFormMock(
        array $children,
        string $name = 'root',
        bool $compound = false,
    ): FormInterface {
        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('getIterator')
            ->andReturn(new \ArrayIterator($children));

        $form
            ->shouldReceive('getName')
            ->andReturn($name);

        $formType = \Mockery::mock(FormTypeInterface::class);

        $resolvedType = \Mockery::mock(ResolvedFormTypeInterface::class);

        $resolvedType
            ->shouldReceive('getInnerType')
            ->andReturn($formType);

        $config = \Mockery::mock(FormConfigInterface::class);

        $config
            ->shouldReceive('getType')
            ->andReturn($resolvedType);

        $config
            ->shouldReceive('getCompound')
            ->andReturn($compound);

        $form
            ->shouldReceive('getConfig')
            ->andReturn($config);

        return $form;
    }

    /**
     * @return FormInterface<Product>
     */
    private function createNonFileFormMock(
        string $name,
    ): FormInterface {
        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('getName')
            ->andReturn($name);

        $formType = \Mockery::mock(FormTypeInterface::class);

        $resolvedType = \Mockery::mock(ResolvedFormTypeInterface::class);

        $resolvedType
            ->shouldReceive('getInnerType')
            ->andReturn($formType);

        $config = \Mockery::mock(FormConfigInterface::class);

        $config
            ->shouldReceive('getType')
            ->andReturn($resolvedType);

        $config
            ->shouldReceive('getCompound')
            ->andReturn(false);

        $form
            ->shouldReceive('getConfig')
            ->andReturn($config);

        return $form;
    }

    public function testConfigureFilters(): void
    {
        $filters = Filters::new();

        $this->productFilterComposer
            ->shouldReceive('compose')
            ->once()
            ->with($filters)
            ->andReturn($filters);

        $result = $this->controller->configureFilters(
            $filters
        );

        $this->assertSame($filters, $result);
    }
}
