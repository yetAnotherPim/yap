<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;

class BooleanFieldFilterHandler implements FieldFilterHandlerInterface
{
    public function supports(FieldType $fieldType): bool
    {
        return InputType::CheckBox === $fieldType->getInputType();
    }

    public function getFormType(): string
    {
        return BooleanFilterType::class;
    }

    public function getFormTypeOptions(): array
    {
        return [];
    }

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        FieldType $fieldType,
        string $valueExpression,
    ): void {
        $parameterName = $filterDataDto->getParameterName();

        if (true === $filterDataDto->getValue()) {
            $queryBuilder
                ->andWhere($valueExpression.' = :'.$parameterName)
                ->setParameter($parameterName, '1');

            return;
        }

        $queryBuilder->andWhere(
            \sprintf(
                '(%s IS NULL OR %s = :%s)',
                $valueExpression,
                $valueExpression,
                $parameterName,
            ),
        )
            ->setParameter($parameterName, '');
    }
}
