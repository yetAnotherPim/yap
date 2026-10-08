<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Products;

use App\Controller\Admin\Products\BatchEditProductCrudController;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Type\BatchEditType;
use App\Form\Product\Columns\Composer\ProductColumnsComposer;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Identifier\IdentifierManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
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
}
