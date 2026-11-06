<?php

declare(strict_types=1);

namespace App\DemoData\Synchronizer;

use App\Entity\Locale\Locale;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;

class TitleTranslationSynchronizer
{
    /**
     * @template TTitleTranslation of TitleTranslationInterface
     *
     * @param TranslatableTitleInterface<TTitleTranslation> $entity
     * @param array<string, string>                         $translations
     * @param array<string, Locale>                         $locales
     */
    public function synchronize(
        TranslatableTitleInterface $entity,
        array $translations,
        array $locales,
    ): void {
        foreach ($translations as $localeCode => $value) {
            if (!isset($locales[$localeCode])) {
                continue;
            }

            $translation = $entity->getTitleTranslations()->findFirst(
                static fn (
                    int $key,
                    TitleTranslationInterface $translation,
                ): bool => $translation->getLocale()->getId() === $localeCode,
            );

            if (null === $translation) {
                $translation = $entity->createTitleTranslation();
                $translation->setLocale($locales[$localeCode]);

                $entity->addTitleTranslation($translation);
            }

            $translation->setValue($value);
        }
    }
}
