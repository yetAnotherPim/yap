<?php

declare(strict_types=1);

namespace App\Form\Product\Columns\Renderer;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;

class ColumnValueRenderer
{
    public function render(
        Product $product,
        FieldType $fieldType,
    ): ?string {
        $field = $product->getFields()->findFirst(
            static function (int|string $key, Field $field) use ($fieldType): bool {
                return $field->getType()->getId()->equals($fieldType->getId());
            },
        );

        if (null === $field) {
            return null;
        }

        if (InputType::CheckBox === $fieldType->getInputType()) {
            return match ($field->getValue()) {
                null => null,
                '1' => '✓',
                default => '✕',
            };
        }

        return $field->getValue();
    }
}
