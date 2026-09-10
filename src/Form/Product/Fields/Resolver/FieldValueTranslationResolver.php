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
            ->getTranslationByLocale($locale->getId());

        if ($fieldValueTranslation instanceof FieldValueTranslation) {
            return $fieldValueTranslation;
        }

        return new FieldValueTranslation()
            ->setField($field)
            ->setLocale($locale)
            ->setValue($defaultValue);
    }
}
