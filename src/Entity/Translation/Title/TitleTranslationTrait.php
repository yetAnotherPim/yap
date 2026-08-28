<?php

declare(strict_types=1);

namespace App\Entity\Translation\Title;

trait TitleTranslationTrait
{
    public function getTranslatedTitle(string $locale): ?string
    {
        $translation = $this->titleTranslations->findFirst(
            static fn (
                int $key,
                TitleTranslationInterface $translation,
            ): bool => $translation->getLocale()->getId() === $locale,
        );

        return $translation?->getValue() ?? $this->title;
    }
}
