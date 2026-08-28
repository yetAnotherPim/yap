<?php

declare(strict_types=1);

namespace App\Form\Title\Translation\Synchronizer;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Form\Title\Translation\Resolver\TitleTranslationResolver;
use App\Repository\LocaleRepository;

class TitleTranslationSynchronizer
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly TitleTranslationResolver $titleTranslationResolver,
    ) {
    }

    /**
     * @template TTitleTranslation of TitleTranslationInterface
     *
     * @param TranslatableTitleInterface<TTitleTranslation> $entity
     * @param array<mixed>|null                             $requestData
     */
    public function synchronizeTitleTranslations(TranslatableTitleInterface $entity, ?array $requestData = null): void
    {
        // TODO: frontend test
        // -> 1.translations are added on create if value is not empty
        // -> 2.translations are added on update if value is not empty
        // -> 3.translations are removed on update if value is empty

        // TODO: integration test
        // -> 1.fieldtype deletion will remove assigned translations
        // -> 2.translations aren't doubled

        if (null === $requestData) {
            return;
        }

        $locales = $this->localeRepository->getAvailableInterfaceTranslationLocales();

        foreach ($locales as $locale) {
            $translation = $this->titleTranslationResolver->resolve($entity, $locale);
            $value = $this->getTranslatedTitle($locale->getId(), $requestData);

            if (null === $translation) {
                continue;
            }

            if (true === empty($value)) {
                $entity->removeTitleTranslation($translation);
                continue;
            }

            $translation->setValue($value);
            $entity->addTitleTranslation($translation);
        }
    }

    /**
     * @param array<mixed> $requestData
     */
    private function getTranslatedTitle(string $localeId, array $requestData): ?string
    {
        $value = $requestData['title-'.$localeId] ?? null;

        if (false === \is_string($value)) {
            return null;
        }

        return trim($value);
    }
}
