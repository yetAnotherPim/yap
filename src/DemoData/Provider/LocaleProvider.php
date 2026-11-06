<?php

declare(strict_types=1);

namespace App\DemoData\Provider;

use App\DemoData\Definition\LocaleDefinitions;
use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;

class LocaleProvider
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    /**
     * @return array<string, Locale>
     */
    public function getLocales(): array
    {
        $locales = [];

        foreach (LocaleDefinitions::REQUIRED_LOCALES as $localeCode) {
            $locale = $this->localeRepository
                ->find($localeCode);

            if (!$locale instanceof Locale) {
                throw new \RuntimeException(\sprintf('Required locale "%s" does not exist. Run the migrations first.', $localeCode));
            }

            if (
                \in_array($localeCode, LocaleDefinitions::ENABLE_TRANSLATIONS, true)
                && false === $locale->isContentTranslationEnabled()
            ) {
                $locale->setContentTranslationEnabled(true);
            }

            $locales[$localeCode] = $locale;
        }

        return $locales;
    }
}
