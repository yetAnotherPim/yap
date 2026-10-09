<?php

declare(strict_types=1);

namespace App\Controller\Admin\Products;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
    ): Response {
        $form = $this->createForm(BatchEditType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            // TODO: add translation
            $this->addFlash('error', 'Batch edit was not successful');

            return $this->redirectToRoute('admin_product_index');
        }

        /** @var BatchEditData $data */
        $data = $form->getData();

        if (true === $data->getFields()->isEmpty()) {
            return $this->redirectToRoute(
                'admin_product_index',
            );
        }

        $productIds = $this->getSelectedProductIds(
            $context,
        );

        /*                dd([
                            'productIds' => iterator_to_array($productIds),
                            'data' => $data,
                            'fields' => $data->getFields()->toArray(),
                            'method' => $request->getMethod(),
                            'queryIds' => $request->query->all('ids'),
                            'batchActionAll' => $request->query->getBoolean('batchActionAll'),
                            'uri' => $request->getUri(),
                        ]);*/

        // TODO: handler for batch edit
        // TODO: add translation
        $this->addFlash('success', 'Batch edit was successful');

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
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSelectedProductIds(
        AdminContext $context,
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

        /** @var FilterFactory $filterFactory */
        $filterFactory = $this->container->get(FilterFactory::class);

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
