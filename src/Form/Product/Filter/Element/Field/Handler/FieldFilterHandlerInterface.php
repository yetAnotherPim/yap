<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\FieldType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('form.product.filter.field.handler')]
interface FieldFilterHandlerInterface
{
    public function supports(FieldType $fieldType): bool;

    /**
     * @return class-string
     */
    public function getFormType(): string;

    /**
     * @return array<string, mixed>
     */
    public function getFormTypeOptions(): array;

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        FieldType $fieldType,
        string $valueExpression,
    ): void;
}
