<?php

declare(strict_types=1);

namespace App\Controller\Admin\Products;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Entity\User\User;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Type\BatchEditType;
use App\Form\Product\Columns\Composer\ProductColumnsComposer;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Identifier\IdentifierManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\UuidV7;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractCrudController<Product>
 */
#[IsGranted('ROLE_EDITOR')]
class ProductCrudController extends AbstractCrudController
{
    /**
     * @var array<string, mixed>
     */
    private array $uploadedFilePaths = [];

    public function __construct(
        private readonly IdentifierManager $identfierManager,
        private readonly ProductFieldsComposer $productFieldsComposer,
        private readonly FieldSynchronizer $fieldSynchronizer,
        private readonly TranslatorInterface $translator,
        private readonly ProductFilterComposer $productFilterComposer,
        private readonly ProductColumnsComposer $productColumnsComposer,
    ) {
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(
                Crud::PAGE_INDEX,
                Action::BATCH_DELETE,
                static fn (Action $action) => $action->setHtmlAttributes([
                    'data-product-batch-count-action' => 'delete',
                ]),
            )
            ->add(Crud::PAGE_INDEX,
                Action::new(
                    'configureColumns',
                    new TranslatableMessage('product_column_configuration.title', [], 'admin'),
                )
                    ->linkToUrl($this->generateUrl('admin_product_columns'))
                    ->setHtmlAttributes([
                        'data-controller' => 'product-column-configuration',
                        'data-action' => 'click->product-column-configuration#load',
                        'data-save-url' => $this->generateUrl('admin_product_columns_save'),
                        'data-target-url' => $this->generateUrl('admin_product_index'),
                    ])
                    ->setIcon('fa fa-columns')
                    ->createAsGlobalAction()
            )
            ->addBatchAction(
                Action::new(
                    'batch_edit',
                    new TranslatableMessage('action.edit', domain: 'EasyAdminBundle'),
                )
                ->linkToCrudAction('batchEdit')
                ->askConfirmation(false)
                ->setIcon('fa fa-edit')
                ->setHtmlAttributes([
                        'data-action-batch-no-confirm' => 'true',
                        'data-product-batch-selection-action' => 'true',
                        'data-product-batch-count-action' => 'edit',
                    ]),
            )
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE,
                static fn (Action $action) => $action->asPrimaryAction()
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                static fn (Action $action) => $action->asDefaultAction()
            );
    }

    #[AdminRoute(
        '/batch-edit',
        'batch_edit',
        options: ['methods' => ['POST']],
    )]
    public function batchEdit(
        AdminContext $context,
        BatchActionDto $batchActionDto,
    ): Response {
        $request = $context->getRequest();

        if ($request->request->getBoolean('batchActionAll')) {
            return $this->redirectToRoute(
                'admin_product_batch_edit_form',
                [
                    ...$request->query->all(),
                    'batchActionAll' => '1',
                ],
            );
        }

        return $this->redirect(
            $this->generateUrl('admin_product_batch_edit_form')
            .'?'
            .http_build_query([
                'ids' => $batchActionDto->getEntityIds(),
            ]),
        );
    }

    #[AdminRoute('/batch-edit-form', 'batch_edit_form', options: ['methods' => ['GET']])]
    public function batchEditForm(
        AdminContext $context,
    ): Response {
        $data = new BatchEditData();
        $data->addField(new BatchEditFieldData(null));

        $form = $this->createForm(BatchEditType::class, $data);

        return $this->render('easyadmin/page/product/batch_edit/batch_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        '/admin/product/batch-edit/field/{id}',
        name: 'admin_product_batch_edit_field',
        methods: ['GET'],
    )]
    public function ajaxGetBatchEditField(
        FieldType $fieldType,
    ): Response {
        $data = new BatchEditData();

        $data->addField(
            new BatchEditFieldData($fieldType),
        );

        $form = $this->createForm(
            BatchEditType::class,
            $data,
        );

        return $this->render(
            'easyadmin/page/product/batch_edit/field.html.twig',
            [
                'field' => $form
                    ->get('fields')
                    ->get($fieldType->getCode())
                    ->createView(),
            ],
        );
    }

    /**
     * TODO: Move.
     *
     * @return iterable<UuidV7>
     */
    private function getSelectedProductIds(
        BatchActionDto $batchActionDto,
        AdminContext $context,
    ): iterable {
        $request = $context->getRequest();

        if (false === $request->query->getBoolean('batchActionAll')) {
            return $batchActionDto->getEntityIds();
        }

        $fields = new FieldCollection(
            $this->configureFields(Crud::PAGE_INDEX),
        );

        $filters = $this->container
            ->get(FilterFactory::class)
            ->create(
                $context->getCrud()->getFiltersConfig(),
                $fields,
                $context->getEntity(),
            );

        $queryBuilder = $this->createIndexQueryBuilderForBatchAction(
            $request,
            $context->getSearch(),
            $context->getEntity(),
            $fields,
            $filters,
        );

        $queryBuilder
            ->resetDQLPart('orderBy')
            ->resetDQLPart('select')
            ->select('DISTINCT entity.id');

        foreach ($queryBuilder->getQuery()->toIterable() as $row) {
            yield $row['id'];
        }
    }

    private function createIndexQueryBuilderForBatchAction(
        Request $request,
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $originalMethod = $request->getMethod();

        try {
            $request->setMethod(Request::METHOD_GET);

            return $this->createIndexQueryBuilder(
                $searchDto,
                $entityDto,
                $fields,
                $filters,
            );
        } finally {
            $request->setMethod($originalMethod);
        }
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('product.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('product.entity.title_plural', [], 'admin'))
            ->setPaginatorUseOutputWalkers(true)
            ->hideNullValues()
            ->setSearchFields([
                'fields.value',
                'fields.translations.value',
                'identifiers.value',
            ])
            ->overrideTemplate(
                'crud/index',
                'easyadmin/page/product/index.html.twig',
            )
            ->setFormThemes(['easyadmin/identifier/identifier_collection.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            /** @phpstan-var User $user */
            $user = $this->getUser();

            return $this->productColumnsComposer->compose($user);
        }

        /** @var array<FieldInterface> $fields */
        $fields = [
            FormField::addTab(new TranslatableMessage('product.labels.master_data', [], 'admin')),
            AssociationField::new('productType', new TranslatableMessage('product.labels.product_type', [], 'admin'))
                ->autocomplete(
                    callback: fn (ProductType $productType): string => $productType->getTranslatedTitleByLocale($this->translator->getLocale())
                )
                ->setDisabled(Crud::PAGE_EDIT === $pageName),
            CollectionField::new('identifiers', new TranslatableMessage('product.labels.identifiers', [], 'admin'))
                ->allowAdd(false)
                ->allowDelete(false)
                ->setEntryIsComplex()
                ->renderExpanded()
                ->setEntryToStringMethod(
                    fn (Identifier $identifier): string => $identifier->getType()->getTitle() ?? ''
                )
                ->setFormTypeOptions([
                    'block_name' => 'identifier_collection',
                ]),
            DateTimeField::new('createdAt', new TranslatableMessage('product.labels.created_at', [], 'admin'))
                ->setDisabled(),
            DateTimeField::new('updatedAt', new TranslatableMessage('product.labels.updated_at', [], 'admin'))
                ->setDisabled(),
        ];

        if (Crud::PAGE_EDIT === $pageName) {
            $product = $this->getContext()
                ?->getEntity()
                ->getInstance();

            if (!$product instanceof Product) {
                return $fields;
            }

            $fields = array_merge($fields, $this->productFieldsComposer->compose($product));
        }

        return $fields;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $this->productFilterComposer->compose($filters);
    }

    public function createEntity(string $entityFqcn): object
    {
        $product = new Product();

        $this->identfierManager->initializeIdentifiers($product);

        return $product;
    }

    /**
     * @return FormInterface<Product>
     */
    public function createEditForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        $product = $entityDto->getInstance();

        if (!$product instanceof Product) {
            return parent::createEditForm($entityDto, $formOptions, $context);
        }

        $this->identfierManager->initializeIdentifiers($product);

        return parent::createEditForm($entityDto, $formOptions, $context);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters,
        );

        /** @phpstan-var User $user */
        $user = $this->getUser();

        return $this->productColumnsComposer->composeQuery(
            $queryBuilder,
            $searchDto,
            $user,
        );
    }

    /**
     * @phpstan-param Product $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->identfierManager->clearUnsetIdentifiers($entityInstance);

        $this->fieldSynchronizer->synchronizeFields($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @phpstan-param Product $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->identfierManager->clearUnsetIdentifiers($entityInstance);
        $entityInstance->updateUpdatedAt();

        $requestData = $this->getContext()
            ?->getRequest()
            ->request
            ->all('Product');

        if (null === $requestData) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $this->fieldSynchronizer->synchronizeFields($entityInstance, $requestData, $this->uploadedFilePaths);

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @param FormInterface<Product> $form
     */
    protected function processUploadedFiles(FormInterface $form): void
    {
        $this->collectUploadedFilePaths($form);

        parent::processUploadedFiles($form);
    }

    /**
     * @param FormInterface<Product> $form
     */
    private function collectUploadedFilePaths(
        FormInterface $form,
        string $parentPath = '',
    ): void {
        foreach ($form as $child) {
            $path = $parentPath
                ? $parentPath.'['.$child->getName().']'
                : $child->getName();

            $config = $child->getConfig();

            if ($config->getType()->getInnerType() instanceof FileUploadType) {
                $this->uploadedFilePaths[$path] = $child->getData();

                continue;
            }

            if ($config->getCompound()) {
                /** @phpstan-var FormInterface<Product> $child */
                $this->collectUploadedFilePaths($child, $path);
            }
        }
    }
}
