<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\FieldType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;

abstract class AbstractComparableFieldFilterHandler implements FieldFilterHandlerInterface
{
    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        FieldType $fieldType,
        string $valueExpression,
    ): void {
        $parameterName = $filterDataDto->getParameterName();

        if (ComparisonType::BETWEEN === $filterDataDto->getComparison()) {
            $queryBuilder
                ->andWhere(\sprintf(
                    '%s BETWEEN :%s_from AND :%s_to',
                    $valueExpression,
                    $parameterName,
                    $parameterName,
                ))
                ->setParameter(
                    $parameterName.'_from',
                    $this->normalizeValue($filterDataDto->getValue(), $fieldType),
                )
                ->setParameter(
                    $parameterName.'_to',
                    $this->normalizeValue($filterDataDto->getValue2(), $fieldType),
                );

            return;
        }

        $operator = match ($filterDataDto->getComparison()) {
            ComparisonType::EQ => '=',
            ComparisonType::NEQ => '<>',
            ComparisonType::GT => '>',
            ComparisonType::GTE => '>=',
            ComparisonType::LT => '<',
            ComparisonType::LTE => '<=',
            default => throw new \LogicException(\sprintf('Unsupported comparison "%s".', $filterDataDto->getComparison())),
        };

        $queryBuilder
            ->andWhere(\sprintf(
                '%s %s :%s',
                $valueExpression,
                $operator,
                $parameterName,
            ))
            ->setParameter(
                $parameterName,
                $this->normalizeValue($filterDataDto->getValue(), $fieldType),
            );
    }

    abstract protected function normalizeValue(mixed $value, FieldType $fieldType): mixed;
}
