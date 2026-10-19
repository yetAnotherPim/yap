<?php

declare(strict_types=1);

namespace App\Entity\Translation\Field;

use App\Entity\Product\Field\Translation\FieldValueTranslation;

trait FieldValueTranslationTrait
{
    public function getTranslationByLocale(string $locale): ?FieldValueTranslation
    {
        return $this->translations->findFirst(
            static fn (
                int $key,
                FieldValueTranslation $translation,
            ): bool => $translation->getLocale()->getId() === $locale,
        );
    }
}
