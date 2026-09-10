<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Synchronizer\Translation;

use App\Entity\Product\Field\Field;
use App\Form\Product\Fields\Resolver\FieldValueTranslationResolver;
use App\Form\Product\Fields\Synchronizer\Extractor\FieldValueExtractor;
use App\Repository\LocaleRepository;

class FieldTranslationSynchronizer
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly FieldValueTranslationResolver $fieldValueTranslationResolver,
        private readonly FieldValueExtractor $fieldValueExtractor,
    ) {
    }

    /**
     * @param array<mixed>|null    $requestData
     * @param array<string, mixed> $uploadedFilePaths
     */
    public function synchronizeTranslation(Field $field, ?array $requestData = null, array $uploadedFilePaths = []): void
    {
        if (false === $field->getType()->isTranslatable()) {
            return;
        }

        $locales = $this->localeRepository->getAvailableContentTranslationLocales();

        foreach ($locales as $locale) {
            // TODO: set defaultValue
            $fieldValueTranslation = $this->fieldValueTranslationResolver->resolve($field, $locale);

            if (null !== $requestData) {
                $fieldValueTranslation->setValue(
                    $this->fieldValueExtractor->extract($field->getType(), $requestData, $uploadedFilePaths, $locale)
                );
            }

            $field->addTranslation($fieldValueTranslation);
        }
    }
}
