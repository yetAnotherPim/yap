<?php

declare(strict_types=1);

namespace App\Controller\Admin\Products;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Repository\IdentifierTypeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<Product>
 */
#[IsGranted('ROLE_EDITOR')]
class ProductCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly IdentifierTypeRepository $identifierTypeRepository,
        private readonly ProductFieldsRenderer $productFieldsRenderer,
        private readonly FieldSynchronizer $fieldSynchronizer,
    ) {
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE,
                static fn (Action $action) => $action->asPrimaryAction()
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                static fn (Action $action) => $action->asDefaultAction()
            );
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->hideNullValues()
            ->setFormThemes(['easyadmin/fields/identifier/identifier_collection.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                CollectionField::new('identifiers')
                    ->formatValue(
                        fn (Collection $identifiers): string => $identifiers
                                ->matching(
                                    Criteria::create()
                                     ->orderBy(['type.title' => Order::Ascending])
                                )
                                ->reduce(
                                    /** @phpstan-ignore-next-line */
                                    static fn (string $formattedValue, Identifier $identifier): string => $formattedValue .= '<b>'.$identifier->getType()->getTitle().':</b> '.$identifier->getValue().'<br/>',
                                    ''
                                )
                    ),
                AssociationField::new('productType')
                    ->formatValue(
                        static fn (ProductType $productType): string => $productType->getTitle()
                    ),
                DateTimeField::new('createdAt'),
                DateTimeField::new('updatedAt'),
            ];
        }

        /** @var array<FieldInterface> $fields */
        $fields = [
            FormField::addTab('Master Data'),
            AssociationField::new('productType')
                ->autocomplete(
                    callback: static fn (ProductType $productType): string => $productType->getTitle()
                )
                ->setDisabled(Crud::PAGE_EDIT === $pageName),
            CollectionField::new('identifiers')
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
            DateTimeField::new('createdAt')
                ->setDisabled(),
            DateTimeField::new('updatedAt')
                ->setDisabled(),
        ];

        if (Crud::PAGE_EDIT === $pageName) {
            $product = $this->getContext()
                ?->getEntity()
                ->getInstance();

            if (!$product instanceof Product) {
                return $fields;
            }

            $fields = array_merge($fields, $this->productFieldsRenderer->render($product));
        }

        return $fields;
    }

    public function createEntity(string $entityFqcn): object
    {
        $product = new Product();

        $this->initializeIdentifiers($product);

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

        $this->initializeIdentifiers($product);

        return parent::createEditForm($entityDto, $formOptions, $context);
    }

    /**
     * @phpstan-param Product $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->clearUnsetIdentifiers($entityInstance);

        $this->fieldSynchronizer->synchronizeFields($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @phpstan-param Product $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->clearUnsetIdentifiers($entityInstance);
        $entityInstance->updateUpdatedAt();

        $requestData = $this->getContext()
            ?->getRequest()
            ->request
            ->all('Product');

        if (null === $requestData) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $this->fieldSynchronizer->synchronizeFields($entityInstance, $requestData);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function clearUnsetIdentifiers(Product $product): void
    {
        foreach ($product->getIdentifiers() as $identifier) {
            if (true === empty($identifier->getValue())) {
                $product->removeIdentifier($identifier);
            }
        }
    }

    private function initializeIdentifiers(Product $product): void
    {
        $identifierTypes = $this->identifierTypeRepository->findAll();

        foreach ($identifierTypes as $identifierType) {
            $identifier = $product->getIdentifiers()->findFirst(
                static fn (int $key, Identifier $identifier): bool => $identifier->getType()->getId() === $identifierType->getId()
            );

            if (null !== $identifier) {
                continue;
            }

            $product->addIdentifier(
                new Identifier()
                    ->setType($identifierType)
            );
        }
    }
}
