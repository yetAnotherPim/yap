<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\Handler\FieldFilterHandlerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class FieldFilter implements FilterInterface
{
    use FilterTrait;

    private FieldType $fieldType;

    private FieldFilterHandlerInterface $handler;

    public static function new(FieldType $fieldType, FieldFilterHandlerInterface $handler, string $locale): self
    {
        $filter = new self();

        $filter->fieldType = $fieldType;
        $filter->handler = $handler;

        $fieldTypeId = str_replace(
            '-',
            '_',
            (string) $fieldType->getId(),
        );

        return $filter
            ->setFilterFqcn(self::class)
            ->setProperty('field_'.$fieldTypeId)
            ->setLabel($fieldType->getTranslatedTitleByLocale($locale))
            ->setFormType($handler->getFormType())
            ->setFormTypeOptions($handler->getFormTypeOptions());
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto,
    ): void {
        $entityAlias = $filterDataDto->getEntityAlias();
        $parameterName = $filterDataDto->getParameterName();
        $fieldAlias = $parameterName.'_field';

        $queryBuilder
            ->innerJoin(
                $entityAlias.'.fields',
                $fieldAlias,
            )
            ->andWhere(
                $fieldAlias.'.type = :'.$parameterName.'_type',
            )
            ->setParameter(
                $parameterName.'_type',
                $this->fieldType,
            );

        $this->handler->apply(
            $queryBuilder,
            $filterDataDto,
            $this->fieldType,
            $fieldAlias.'.value',
        );
    }
}
