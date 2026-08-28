<?php

declare(strict_types=1);

namespace App\Form\Title\Translation\Resolver;

use App\Entity\Locale\Locale;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;

class TitleTranslationResolver
{
    /**
     * @template TTitleTranslation of TitleTranslationInterface
     *
     * @param TranslatableTitleInterface<TTitleTranslation>|null $entity
     *
     * @return TTitleTranslation|null
     */
    public function resolve(?TranslatableTitleInterface $entity, Locale $locale): ?TitleTranslationInterface
    {
        if (null === $entity) {
            return null;
        }

        $translation = $entity
            ->getTitleTranslations()
            ->findFirst(
                static function (
                    int $key,
                    TitleTranslationInterface $translation,
                ) use ($locale): bool {
                    return $translation->getLocale() === $locale;
                }
            );

        if (null === $translation) {
            $translation = $entity->createTitleTranslation()
                ->setLocale($locale);
        }

        return $translation;
    }
}
