<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Composer;

use App\Entity\Product\Type\ProductType;
use App\Form\Product\Filter\Element\Category\FilterCategory;
use App\Form\Product\Filter\Element\Field\FieldFilter;
use App\Form\Product\Filter\Element\Field\Registry\FieldFilterHandlerRegistry;
use App\Form\Product\Filter\Element\Identifier\IdentifierExistsFilter;
use App\Form\Product\Filter\Element\Identifier\IdentifierFilter;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductFilterComposer
{
    public function __construct(
        private readonly IdentifierTypeRepository $identifierTypeRepository,
        private readonly FieldTypeRepository $fieldTypeRepository,
        private readonly TranslatorInterface $translator,
        private readonly FieldFilterHandlerRegistry $fieldFilterHandlerRegistry,
    ) {
    }

    public function compose(Filters $filters): Filters
    {
        $this->addIdentifierFilter($filters);

        $this->addFieldFilter($filters);

        $filters->add(
            FilterCategory::new(
                '_field_translations',
                new TranslatableMessage(
                    'filter.labels.translations',
                    domain: 'admin',
                ),
            ),
        );

        $filters->add(
            FilterCategory::new(
                '_general',
                new TranslatableMessage(
                    'filter.labels.general',
                    domain: 'admin',
                ),
            ),
        );

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

    private function addIdentifierFilter(Filters $filters): void
    {
        $identifierTypes = $this->identifierTypeRepository->findAll();

        if (true === empty($identifierTypes)) {
            return;
        }

        $filters->add(
            FilterCategory::new(
                '_identifier',
                new TranslatableMessage(
                    'filter.labels.identifiers',
                    domain: 'admin',
                ),
            ),
        );

        foreach ($identifierTypes as $identifierType) {
            $filters
                ->add(IdentifierExistsFilter::new($identifierType))
                ->add(IdentifierFilter::new($identifierType));
        }
    }

    private function addFieldFilter(Filters $filters): void
    {
        $fieldTypes = $this->fieldTypeRepository->findBy([
            'filterable' => true,
        ]);

        if (true === empty($fieldTypes)) {
            return;
        }

        $filters->add(
            FilterCategory::new(
                '_fields',
                new TranslatableMessage(
                    'filter.labels.fields',
                    domain: 'admin',
                ),
            ),
        );

        foreach ($fieldTypes as $fieldType) {
            $filters->add(
                FieldFilter::new(
                    $fieldType,
                    $this->fieldFilterHandlerRegistry->get($fieldType),
                    $this->translator->getLocale(),
                )
            );
        }
    }
}
