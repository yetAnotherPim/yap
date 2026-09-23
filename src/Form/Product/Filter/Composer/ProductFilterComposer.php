<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Composer;

use App\Entity\Product\Type\ProductType;
use App\Form\Product\Filter\Identifier\IdentifierExistsFilter;
use App\Form\Product\Filter\Identifier\IdentifierFilter;
use App\Repository\IdentifierTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductFilterComposer
{
    public function __construct(
        private readonly IdentifierTypeRepository $identifierTypeRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function compose(Filters $filters): Filters
    {
        foreach ($this->identifierTypeRepository->findAll() as $identifierType) {
            $filters
                ->add(
                    IdentifierExistsFilter::new($identifierType),
                )
                ->add(
                    IdentifierFilter::new($identifierType),
                );
        }

        $filters
            ->add(
                EntityFilter::new('productType')
                    ->setFormTypeOption(
                        'value_type_options.choice_label',
                        fn (ProductType $productType): string => $productType->getTranslatedTitleByLocale(
                            $this->translator->getLocale(),
                        ),
                    ),
            )
            ->add(DateTimeFilter::new('updatedAt'))
            ->add(DateTimeFilter::new('createdAt'));

        return $filters;
    }
}
