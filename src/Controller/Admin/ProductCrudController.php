<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Repository\IdentifierTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\Uid\Uuid;

class ProductCrudController extends AbstractCrudController
{
    public function __construct(
        readonly private IdentifierTypeRepository $identifierTypeRepository
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // TODO: identifiers first in list
        // TODO: on save -> remove empty identifiers
        // TODO: on update -> add missing identifiers as empty

        return [


            AssociationField::new('productType')
                ->autocomplete(
                    callback: static fn (ProductType $productType): string => $productType->getTitle()
                )
                ->formatValue(
                    static fn (ProductType $productType): string => $productType->getTitle()
                )
            ,
            FormField::addFieldset('Identifier'),
            CollectionField::new('identifiers')
                ->allowAdd(false)
                ->allowDelete(false)
                //->setEntryIsComplex()
                //->renderExpanded()

/*
                //TODO: render collection
                ->formatValue(
                    fn (Identifier $identifier): string => $identifier->getValue()
                )*/
                ->setEntryToStringMethod(
                    fn (Identifier $identifier): string => 'tes'
                ),
        ];
    }

    public function createEntity(string $entityFqcn): object
    {
        $identifierTypes = $this->identifierTypeRepository->findAll();

        $product = new Product();

        foreach ($identifierTypes as $identifierType) {
            $product->addIdentifier(
                new Identifier()
                    //TODO: should work without id, remove setter
                    ->setId(Uuid::v7())
                    ->setType($identifierType)
                    ->setValue('')
            );
        }

        return $product;
    }
}
