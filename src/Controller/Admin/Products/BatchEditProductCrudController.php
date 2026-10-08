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
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    /*    private function getSelectedProductIds(
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
        }*/

    /*    private function createIndexQueryBuilderForBatchAction(
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
        }*/
}
