<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Products;

use App\Controller\Admin\Products\BatchEditProductCrudController;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Message\ProductBatchUpdateDispatcher;
use App\Form\Product\BatchEdit\Type\BatchEditType;
use App\Form\Product\Columns\Composer\ProductColumnsComposer;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Identifier\IdentifierManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\CrudContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm\NestedAssociationResolverInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Provider\AdminContextProviderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\CrudDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterConfigDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(BatchEditProductCrudController::class)]
#[UsesClass(BatchEditData::class)]
#[UsesClass(BatchEditFieldData::class)]
class BatchEditProductCrudControllerTest extends MockeryTestCase
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

    /** @var ProductColumnsComposer&MockInterface */
    private ProductColumnsComposer $productColumnsComposer;

    private BatchEditProductCrudController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productFieldsRenderer = \Mockery::mock(ProductFieldsComposer::class);
        $this->identifierManager = \Mockery::mock(IdentifierManager::class);
        $this->fieldSynchronizer = \Mockery::mock(FieldSynchronizer::class);
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->productFilterComposer = \Mockery::mock(ProductFilterComposer::class);
        $this->productColumnsComposer = \Mockery::mock(ProductColumnsComposer::class);

        $this->controller = new BatchEditProductCrudController(
            $this->identifierManager,
            $this->productFieldsRenderer,
            $this->fieldSynchronizer,
            $this->translator,
            $this->productFilterComposer,
            $this->productColumnsComposer,
        );
    }

    public function testConfigureCrudDisablesSearch(): void
    {
        $crud = Crud::new();

        $result = $this->controller->configureCrud($crud);

        $this->assertNull(
            $result->getAsDto()->getSearchFields(),
        );
    }

    public function testConfigureActionsDisablesStandardCrudActions(): void
    {
        $actions = Actions::new();

        $result = $this->controller->configureActions($actions);

        $dto = $result->getAsDto(Crud::PAGE_INDEX);

        $this->assertNull(
            $dto->getAction(Crud::PAGE_INDEX, Action::INDEX),
        );

        $this->assertNull(
            $dto->getAction(Crud::PAGE_INDEX, Action::BATCH_DELETE),
        );
    }

    public function testBatchEditFormCreatesFormAndRendersTemplate(): void
    {
        /** @var AdminContext<Product> $context */
        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting(),
        );

        $form = \Mockery::mock(FormInterface::class);
        $formView = \Mockery::mock(\Symfony\Component\Form\FormView::class);

        $form
            ->shouldReceive('createView')
            ->once()
            ->andReturn($formView);

        $controller = \Mockery::mock(
            BatchEditProductCrudController::class,
            [
                $this->identifierManager,
                $this->productFieldsRenderer,
                $this->fieldSynchronizer,
                $this->translator,
                $this->productFilterComposer,
                $this->productColumnsComposer,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('createForm')
            ->once()
            ->andReturn($form);

        $controller
            ->shouldReceive('render')
            ->once()
            ->with(
                'easyadmin/page/product/batch_edit/batch_edit.html.twig',
                ['form' => $formView],
            )
            ->andReturn(new Response('test'));

        $result = $controller->batchEditForm($context);

        $this->assertSame('test', $result->getContent());
    }

    public function testProcessBatchEditFormRedirectsWhenFormIsNotSubmitted(): void
    {
        $request = new Request();

        $filterFactory = new FilterFactory(
            \Mockery::mock(AdminContextProviderInterface::class),
            [],
            \Mockery::mock(NestedAssociationResolverInterface::class),
        );

        $productBatchUpdateDispatcher = \Mockery::mock(ProductBatchUpdateDispatcher::class);

        $form = \Mockery::mock(FormInterface::class);
        $form
            ->shouldReceive('handleRequest')
            ->once()
            ->with($request)
            ->andReturn($form);

        $form
            ->shouldReceive('isSubmitted')
            ->once()
            ->andReturn(false);

        $form->shouldNotReceive('isValid');
        $form->shouldNotReceive('getData');

        $controller = $this->createControllerMock();

        $controller
            ->shouldReceive('createForm')
            ->once()
            ->with(BatchEditType::class)
            ->andReturn($form);

        $controller
            ->shouldReceive('redirectToRoute')
            ->once()
            ->with('admin_product_index')
            ->andReturn(new RedirectResponse('/admin/products'));

        /** @var AdminContext<Product> $context */
        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting($request),
        );

        /** @var RedirectResponse $result */
        $result = $controller->processBatchEditForm(
            $request,
            $context,
            $productBatchUpdateDispatcher,
            $filterFactory,
        );

        $this->assertSame('/admin/products', $result->getTargetUrl());
    }

    public function testProcessBatchEditFormRedirectsWhenNoFieldsAreSelected(): void
    {
        $filterFactory = new FilterFactory(
            \Mockery::mock(AdminContextProviderInterface::class),
            [],
            \Mockery::mock(NestedAssociationResolverInterface::class),
        );

        $productBatchUpdateDispatcher = \Mockery::mock(ProductBatchUpdateDispatcher::class);

        $request = new Request();
        $data = new BatchEditData();

        $form = \Mockery::mock(FormInterface::class);
        $form
            ->shouldReceive('handleRequest')
            ->once()
            ->with($request)
            ->andReturn($form);

        $form
            ->shouldReceive('isSubmitted')
            ->once()
            ->andReturn(true);

        $form
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $form
            ->shouldReceive('getData')
            ->once()
            ->andReturn($data);

        $controller = $this->createControllerMock();

        $controller
            ->shouldReceive('createForm')
            ->once()
            ->with(BatchEditType::class)
            ->andReturn($form);

        $controller
            ->shouldReceive('addFlash')
            ->once()
            ->withArgs(
                static function (
                    string $type,
                    TranslatableMessage $message,
                ): bool {
                    return 'success' === $type
                        && 'product.batch_edit.dispatched_plural' === $message->getMessage()
                        && ['%count%' => 0] === $message->getParameters()
                        && 'admin' === $message->getDomain();
                },
            );

        $controller
            ->shouldReceive('redirectToRoute')
            ->once()
            ->with('admin_product_index')
            ->andReturn(new RedirectResponse('/admin/products'));

        /** @var AdminContext<Product> $context */
        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting($request),
        );

        /** @var RedirectResponse $result */
        $result = $controller->processBatchEditForm(
            $request,
            $context,
            $productBatchUpdateDispatcher,
            $filterFactory,
        );

        $this->assertSame('/admin/products', $result->getTargetUrl());
    }

    public function testProcessBatchEditFormRedirectsAfterSuccessfulSubmission(): void
    {
        $request = new Request([
            'ids' => ['product-1'],
        ]);

        $productBatchUpdateDispatcher = \Mockery::mock(
            ProductBatchUpdateDispatcher::class,
        );

        $filterFactory = new FilterFactory(
            \Mockery::mock(AdminContextProviderInterface::class),
            [],
            \Mockery::mock(NestedAssociationResolverInterface::class),
        );

        $data = \Mockery::mock(BatchEditData::class);

        $productBatchUpdateDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with($data, 'product-1');

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('handleRequest')
            ->once()
            ->with($request);

        $form
            ->shouldReceive('isSubmitted')
            ->once()
            ->andReturn(true);

        $form
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $form
            ->shouldReceive('getData')
            ->once()
            ->andReturn($data);

        $controller = $this->createControllerMock();

        $controller
            ->shouldReceive('createForm')
            ->once()
            ->with(BatchEditType::class)
            ->andReturn($form);

        $controller
            ->shouldReceive('addFlash')
            ->once()
            ->withArgs(
                static function (
                    string $type,
                    TranslatableMessage $message,
                ): bool {
                    return 'success' === $type
                        && 'product.batch_edit.dispatched_singular' === $message->getMessage()
                        && ['%count%' => 1] === $message->getParameters()
                        && 'admin' === $message->getDomain();
                },
            );

        $controller
            ->shouldReceive('redirectToRoute')
            ->once()
            ->with('admin_product_index')
            ->andReturn(new RedirectResponse('/admin/products'));

        /** @var AdminContext<Product> $context */
        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting($request),
        );

        /** @var RedirectResponse $result */
        $result = $controller->processBatchEditForm($request, $context, $productBatchUpdateDispatcher, $filterFactory);

        $this->assertSame('/admin/products', $result->getTargetUrl());
    }

    public function testGetSelectedProductIdsReturnsSelectedIds(): void
    {
        $request = new Request([
            'ids' => ['product-1', 'product-2'],
        ]);

        $filterFactory = new FilterFactory(
            \Mockery::mock(AdminContextProviderInterface::class),
            [],
            \Mockery::mock(NestedAssociationResolverInterface::class),
        );

        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting($request),
        );

        $method = new \ReflectionMethod(
            BatchEditProductCrudController::class,
            'getSelectedProductIds',
        );

        /** @var iterable<string> $result */
        $result = $method->invoke($this->controller, $context, $filterFactory);

        $this->assertSame(
            ['product-1', 'product-2'],
            iterator_to_array($result),
        );
    }

    public function testGetSelectedProductIdsReturnsAllMatchingIds(): void
    {
        $request = new Request(
            [
                'batchActionAll' => '1',
                'filters' => ['productType' => '123'],
                'sort' => 'name',
            ],
            [],
            [],
            [],
            [],
            ['REQUEST_METHOD' => Request::METHOD_POST],
        );

        $entityDto = new EntityDto(
            Product::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            \Mockery::mock(Product::class),
        );

        $searchDto = new SearchDto(
            $request,
            null,
            null,
            [],
            [],
            null,
        );

        $crudContext = CrudContext::forTesting(
            entityDto: $entityDto,
            searchDto: $searchDto,
        );

        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting($request),
            crudContext: $crudContext,
        );

        /** @var CrudDto $crud */
        $crud = $context->getCrud();
        $crud->setFiltersConfig(new FilterConfigDto());

        $filterFactory = new FilterFactory(
            \Mockery::mock(AdminContextProviderInterface::class),
            [],
            \Mockery::mock(NestedAssociationResolverInterface::class),
        );

        $container = \Mockery::mock(ContainerInterface::class);

        $query = \Mockery::mock(Query::class);

        $query
            ->shouldReceive('toIterable')
            ->once()
            ->andReturn([
                ['id' => 'product-1'],
                ['id' => 'product-2'],
            ]);

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('resetDQLPart')
            ->once()
            ->with('orderBy')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('resetDQLPart')
            ->once()
            ->with('select')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('select')
            ->once()
            ->with('DISTINCT entity.id')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);

        $controller = $this->createControllerMock();
        $controller->setContainer($container);

        $controller
            ->shouldReceive('configureFields')
            ->once()
            ->with(Crud::PAGE_INDEX)
            ->andReturn([]);

        $controller
            ->shouldReceive('createIndexQueryBuilder')
            ->once()
            ->with(
                $searchDto,
                $entityDto,
                \Mockery::type(FieldCollection::class),
                \Mockery::type(FilterCollection::class),
            )
            ->andReturnUsing(
                static function (
                    SearchDto $searchDto,
                    EntityDto $entityDto,
                    FieldCollection $fields,
                    FilterCollection $filters,
                ) use ($request, $queryBuilder): QueryBuilder {
                    if (Request::METHOD_GET !== $request->getMethod()) {
                        throw new \RuntimeException('Expected GET while creating the batch-action query.');
                    }

                    return $queryBuilder;
                },
            );

        $method = new \ReflectionMethod(
            BatchEditProductCrudController::class,
            'getSelectedProductIds',
        );

        /** @var iterable<string> $result */
        $result = $method->invoke($controller, $context, $filterFactory);

        $this->assertSame(
            ['product-1', 'product-2'],
            iterator_to_array($result),
        );

        $this->assertSame(Request::METHOD_POST, $request->getMethod());
    }

    public function testGetSelectedProductIdsThrowsExceptionWhenCrudContextIsMissing(): void
    {
        $request = new Request(['batchActionAll' => '1']);

        $filterFactory = new FilterFactory(
            \Mockery::mock(AdminContextProviderInterface::class),
            [],
            \Mockery::mock(NestedAssociationResolverInterface::class),
        );

        $crudContext = new CrudContext(
            crudDto: null,
            entityDto: null,
            searchDto: null,
            adminControllers: new \EasyCorp\Bundle\EasyAdminBundle\Registry\AdminControllerRegistry(
                new \Symfony\Component\Cache\Adapter\ArrayAdapter(),
            ),
        );

        $context = new AdminContext(
            RequestContext::forTesting($request),
            $crudContext,
            \EasyCorp\Bundle\EasyAdminBundle\Context\DashboardContext::forTesting(),
            \EasyCorp\Bundle\EasyAdminBundle\Context\I18nContext::forTesting(),
        );

        $controller = $this->createControllerMock();

        $method = new \ReflectionMethod(
            BatchEditProductCrudController::class,
            'getSelectedProductIds',
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs('The CRUD context is missing.');

        /** @var iterable<string> $result */
        $result = $method->invoke($controller, $context, $filterFactory);

        iterator_to_array($result);
    }

    public function testGetSelectedProductIdsThrowsExceptionWhenSearchContextIsMissing(): void
    {
        $filterFactory = new FilterFactory(
            \Mockery::mock(AdminContextProviderInterface::class),
            [],
            \Mockery::mock(NestedAssociationResolverInterface::class),
        );

        $request = new Request(['batchActionAll' => '1']);

        $entityDto = new EntityDto(
            Product::class,
            \Mockery::mock(ClassMetadata::class),
            null,
            \Mockery::mock(Product::class),
        );

        $crudContext = CrudContext::forTesting(
            entityDto: $entityDto,
            searchDto: null,
        );

        $context = AdminContext::forTesting(
            requestContext: RequestContext::forTesting($request),
            crudContext: $crudContext,
        );

        $controller = $this->createControllerMock();

        $method = new \ReflectionMethod(
            BatchEditProductCrudController::class,
            'getSelectedProductIds',
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs('The search context is missing.');

        /** @var iterable<string> $result */
        $result = $method->invoke($controller, $context, $filterFactory);

        iterator_to_array($result);
    }

    public function testAjaxGetBatchEditFieldRendersField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getCode')
            ->andReturn('sku');

        $field = \Mockery::mock(FormInterface::class);
        $fieldView = \Mockery::mock(\Symfony\Component\Form\FormView::class);

        $field
            ->shouldReceive('createView')
            ->once()
            ->andReturn($fieldView);

        $fields = \Mockery::mock(FormInterface::class);

        $fields
            ->shouldReceive('get')
            ->once()
            ->with('sku')
            ->andReturn($field);

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('get')
            ->once()
            ->with('fields')
            ->andReturn($fields);

        $controller = \Mockery::mock(
            BatchEditProductCrudController::class,
            [
                $this->identifierManager,
                $this->productFieldsRenderer,
                $this->fieldSynchronizer,
                $this->translator,
                $this->productFilterComposer,
                $this->productColumnsComposer,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('createForm')
            ->once()
            ->with(
                BatchEditType::class,
                \Mockery::on(
                    static function (mixed $data) use ($fieldType): bool {
                        if (!$data instanceof BatchEditData) {
                            return false;
                        }

                        if (1 !== $data->getFields()->count()) {
                            return false;
                        }

                        $fieldData = $data->getFields()->first();

                        return $fieldData instanceof BatchEditFieldData
                            && $fieldType === $fieldData->getFieldType();
                    },
                ),
            )
            ->andReturn($form);

        $controller
            ->shouldReceive('render')
            ->once()
            ->with(
                'easyadmin/page/product/batch_edit/field.html.twig',
                ['field' => $fieldView],
            )
            ->andReturn(new Response('test'));

        $result = $controller->ajaxGetBatchEditField($fieldType);

        $this->assertSame('test', $result->getContent());
    }

    /**
     * @return BatchEditProductCrudController&MockInterface
     */
    private function createControllerMock(): BatchEditProductCrudController
    {
        return \Mockery::mock(
            BatchEditProductCrudController::class,
            [
                $this->identifierManager,
                $this->productFieldsRenderer,
                $this->fieldSynchronizer,
                $this->translator,
                $this->productFilterComposer,
                $this->productColumnsComposer,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
