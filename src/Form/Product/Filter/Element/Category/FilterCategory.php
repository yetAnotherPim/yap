<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Category;

use App\Form\Product\Filter\Element\Category\Form\FilterCategoryType\FilterCategoryType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

class FilterCategory implements FilterInterface
{
    use FilterTrait;

    public static function new(string $property, string|TranslatableInterface $label): self
    {
        return new self()
            ->setFilterFqcn(self::class)
            ->setProperty($property)
            ->setLabel($label)
            ->setFormType(FilterCategoryType::class)
            ->setFormTypeOption('label_attr', [
                'class' => 'filter-category',
            ]);
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto,
    ): void {
        // Separator only; never modifies the query.
    }
}
