<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Identifier;

use App\Entity\Product\Identifier\Type\IdentifierType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use Symfony\Component\Translation\TranslatableMessage;

final class IdentifierExistsFilter implements FilterInterface
{
    use FilterTrait;

    private IdentifierType $identifierType;

    public static function new(IdentifierType $identifierType): self
    {
        $filter = new self();

        $filter->identifierType = $identifierType;

        $identifierTypeId = str_replace(
            '-',
            '_',
            (string) $identifierType->getId(),
        );

        return $filter
            ->setFilterFqcn(self::class)
            ->setProperty('has_identifier_'.$identifierTypeId)
            ->setLabel(
                new TranslatableMessage(
                    'filter.is_set',
                    [
                        '%name%' => $identifierType->getTitle(),
                    ],
                    'admin',
                ),
            )
            ->setFormType(BooleanFilterType::class);
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto,
    ): void {
        $entityAlias = $filterDataDto->getEntityAlias();
        $parameterName = $filterDataDto->getParameterName();
        $identifierAlias = $parameterName.'_identifier';

        $queryBuilder
            ->leftJoin(
                $entityAlias.'.identifiers',
                $identifierAlias,
                'WITH',
                $identifierAlias.'.type = :'.$parameterName.'_type',
            )
            ->setParameter(
                $parameterName.'_type',
                $this->identifierType,
            );

        $queryBuilder->andWhere(
            $identifierAlias.'.id '.(
                $filterDataDto->getValue()
                    ? 'IS NOT NULL'
                    : 'IS NULL'
            ),
        );
    }
}
