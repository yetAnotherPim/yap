<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Repository\IdentifierTypeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Form\FormInterface;

/**
 * @extends AbstractCrudController<Product>
 */
class ProductCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly IdentifierTypeRepository $identifierTypeRepository,
    ) {
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

        return [
            FormField::addTab('Master Data'),
            AssociationField::new('productType')
                ->autocomplete(
                    callback: static fn (ProductType $productType): string => $productType->getTitle()
                ),
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

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @phpstan-param Product $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->clearUnsetIdentifiers($entityInstance);
        $entityInstance->updateUpdatedAt();

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
