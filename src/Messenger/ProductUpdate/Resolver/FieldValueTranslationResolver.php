<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Resolver;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Repository\LocaleRepository;

class FieldValueTranslationResolver
{
    public function __construct(private readonly LocaleRepository $localeRepository)
    {
    }

    public function resolve(Field $field, string $localeId): ?FieldValueTranslation
    {
        if (false === $field->getType()->isTranslatable()) {
            return null;
        }

        $fieldValueTranslation = $field
            ->getTranslationByLocale($localeId);

        if ($fieldValueTranslation instanceof FieldValueTranslation) {
            return $fieldValueTranslation;
        }

        $locale = $this->localeRepository->getContentTranslationLocaleById($localeId);

        if (null === $locale) {
            return null;
        }

        $fieldValueTranslation = new FieldValueTranslation()
            ->setLocale($locale);

        $field->addTranslation($fieldValueTranslation);

        return $fieldValueTranslation;
    }
}
