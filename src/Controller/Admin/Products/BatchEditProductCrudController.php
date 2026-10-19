<?php

declare(strict_types=1);

namespace App\Controller\Admin\Products;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Message\ProductBatchUpdateDispatcher;
use App\Form\Product\BatchEdit\Type\BatchEditType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_EDITOR')]
class BatchEditProductCrudController extends ProductCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setSearchFields(null);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(
                Action::INDEX,
                Action::NEW,
                Action::EDIT,
                Action::DETAIL,
                Action::DELETE,
                Action::BATCH_DELETE,
            );
    }

    /**
     * @param AdminContext<Product> $context
     */
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

    /**
     * @param AdminContext<Product> $context
     */
    #[AdminRoute(
        '/batch-edit-form',
        'process_batch_edit_form',
        options: ['methods' => ['POST']],
    )]
    public function processBatchEditForm(
        Request $request,
        AdminContext $context,
        ProductBatchUpdateDispatcher $productBatchUpdateDispatcher,
        FilterFactory $filterFactory,
    ): Response {
        $messageCount = 0;
        $form = $this->createForm(BatchEditType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->redirectToRoute('admin_product_index');
        }

        /** @var BatchEditData $data */
        $data = $form->getData();

        foreach ($this->getSelectedProductIds($context, $filterFactory) as $productId) {
            $productBatchUpdateDispatcher->dispatch($data, $productId);
            ++$messageCount;
        }

        $this->addFlash(
            'success',
            new TranslatableMessage(
                1 === $messageCount
                    ? 'product.batch_edit.dispatched_singular'
                    : 'product.batch_edit.dispatched_plural',
                ['%count%' => $messageCount],
                'admin',
            ),
        );

        return $this->redirectToRoute(
            'admin_product_index',
        );
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
     * @param AdminContext<Product> $context
     *
     * @return iterable<string>
     */
    private function getSelectedProductIds(
        AdminContext $context,
        FilterFactory $filterFactory,
    ): iterable {
        $request = $context->getRequest();

        if (false === $request->query->getBoolean('batchActionAll')) {
            /** @var list<string> $ids */
            $ids = $request->query->all('ids');

            foreach ($ids as $id) {
                yield $id;
            }

            return;
        }

        $crudDto = $context->getCrud()
            ?? throw new \LogicException('The CRUD context is missing.');

        $searchDto = $context->getSearch()
            ?? throw new \LogicException('The search context is missing.');

        $fields = new FieldCollection(
            $this->configureFields(Crud::PAGE_INDEX),
        );

        $filters = $filterFactory->create(
            $crudDto->getFiltersConfig(),
            $fields,
            $context->getEntity(),
        );

        $queryBuilder = $this->createIndexQueryBuilderForBatchAction(
            $request,
            $searchDto,
            $context->getEntity(),
            $fields,
            $filters,
        );

        $queryBuilder
            ->resetDQLPart('orderBy')
            ->resetDQLPart('select')
            ->select('DISTINCT entity.id');

        /** @var array<array{id: string|Uuid}> $rows */
        $rows = $queryBuilder->getQuery()->toIterable();

        foreach ($rows as $row) {
            yield (string) $row['id'];
        }
    }

    /**
     * @param EntityDto<Product> $entityDto
     */
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
}
