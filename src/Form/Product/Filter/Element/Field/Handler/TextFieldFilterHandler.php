<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;

class TextFieldFilterHandler implements FieldFilterHandlerInterface
{
    public function supports(FieldType $fieldType): bool
    {
        return \in_array(
            $fieldType->getInputType(),
            [
                InputType::String,
                InputType::Text,
                InputType::TextEditor,
                InputType::Email,
                InputType::Telephone,
                InputType::Url,
                InputType::Color,
                InputType::Country,
                InputType::Language,
                InputType::Locale,
                InputType::Select,
                InputType::MultiSelect,
                InputType::File,
                InputType::Image,
            ],
            true,
        );
    }

    public function getFormType(): string
    {
        return TextFilterType::class;
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
        $value = $filterDataDto->getValue();

        if (!\is_string($value)) {
            return;
        }

        [$operator, $value] = match ($filterDataDto->getComparison()) {
            ComparisonType::EQ => ['=', $value],
            ComparisonType::NEQ => ['<>', $value],
            ComparisonType::CONTAINS => ['LIKE', '%'.$value.'%'],
            ComparisonType::NOT_CONTAINS => ['NOT LIKE', '%'.$value.'%'],
            ComparisonType::STARTS_WITH => ['LIKE', $value.'%'],
            ComparisonType::ENDS_WITH => ['LIKE', '%'.$value],
            default => throw new \LogicException(\sprintf('Unsupported comparison "%s".', $filterDataDto->getComparison())),
        };

        $queryBuilder
            ->andWhere(\sprintf(
                '%s %s :%s',
                $valueExpression,
                $operator,
                $parameterName,
            ))
            ->setParameter($parameterName, $value);
    }
}
