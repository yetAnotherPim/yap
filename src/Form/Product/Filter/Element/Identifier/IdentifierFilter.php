<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Identifier;

use App\Entity\Product\Identifier\Type\IdentifierType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;

final class IdentifierFilter implements FilterInterface
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
            ->setProperty('identifier_'.$identifierTypeId)
            ->setLabel($identifierType->getTitle())
            ->setFormType(TextFilterType::class);
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
            ->innerJoin(
                $entityAlias.'.identifiers',
                $identifierAlias,
            )
            ->andWhere(
                $identifierAlias.'.type = :'.$parameterName.'_type',
            )
            ->setParameter(
                $parameterName.'_type',
                $this->identifierType,
            );

        $value = $filterDataDto->getValue();

        if (!\is_string($value)) {
            return;
        }

        $this->applyFilterQuery($filterDataDto, $queryBuilder, $identifierAlias, $parameterName, $value);
    }

    private function applyFilterQuery(FilterDataDto $filterDataDto, QueryBuilder $queryBuilder, string $identifierAlias, string $parameterName, string $value): void
    {
        [$operator, $value] = match ($filterDataDto->getComparison()) {
            ComparisonType::EQ => [
                '=',
                $value,
            ],
            ComparisonType::NEQ => [
                '<>',
                $value,
            ],
            ComparisonType::CONTAINS => [
                'LIKE',
                '%'.$value.'%',
            ],
            ComparisonType::NOT_CONTAINS => [
                'NOT LIKE',
                '%'.$value.'%',
            ],
            ComparisonType::STARTS_WITH => [
                'LIKE',
                $value.'%',
            ],
            ComparisonType::ENDS_WITH => [
                'LIKE',
                '%'.$value,
            ],
            default => throw new \LogicException(\sprintf('Unsupported comparison "%s".', $filterDataDto->getComparison())),
        };

        $queryBuilder
            ->andWhere(\sprintf(
                '%s.value %s :%s',
                $identifierAlias,
                $operator,
                $parameterName,
            ))
            ->setParameter($parameterName, $value);
    }
}
