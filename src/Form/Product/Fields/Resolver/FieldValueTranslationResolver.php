<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Resolver;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;

class FieldValueTranslationResolver
{
    public function resolve(Field $field, Locale $locale, ?string $defaultValue = null): FieldValueTranslation
    {
        $fieldValueTranslation = $field
            ->getTranslations()
            ->findFirst(
                static fn (int $key, FieldValueTranslation $value): bool => $value->getLocale() === $locale
            );

        if ($fieldValueTranslation instanceof FieldValueTranslation) {
            return $fieldValueTranslation;
        }

        return new FieldValueTranslation()
            ->setField($field)
            ->setLocale($locale)
            ->setValue($defaultValue);
    }
}
