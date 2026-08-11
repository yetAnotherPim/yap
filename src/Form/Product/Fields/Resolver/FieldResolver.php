<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Resolver;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;

class FieldResolver
{
    public function resolve(Product $product, FieldType $fieldType, ?string $defaultValue = null): Field
    {
        $field = $product
            ->getFields()
            ->findFirst(
                static fn (int $key, Field $value): bool => $value->getType()->getCode() === $fieldType->getCode()
            );

        if ($field instanceof Field) {
            return $field;
        }

        return new Field()
            ->setType($fieldType)
            ->setProduct($product)
            ->setValue($defaultValue);
    }
}
