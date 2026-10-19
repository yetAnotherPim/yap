<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Synchronizer\Translation;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Resolver\FieldValueTranslationResolver;
use App\Form\Product\Fields\Synchronizer\Extractor\FieldValueExtractor;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use App\Repository\LocaleRepository;

class FieldTranslationSynchronizer
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly FieldValueTranslationResolver $fieldValueTranslationResolver,
        private readonly FieldValueExtractor $fieldValueExtractor,
        private readonly DefaultTranslationResolver $defaultTranslationResolver,
    ) {
    }

    /**
     * @param array<mixed>|null    $requestData
     * @param array<string, mixed> $uploadedFilePaths
     */
    public function synchronizeTranslation(Field $field, ProductTypeFieldConfiguration $productTypeFieldConfiguration, ?array $requestData = null, array $uploadedFilePaths = []): void
    {
        if (false === $field->getType()->isTranslatable()) {
            return;
        }

        $locales = $this->localeRepository->getAvailableContentTranslationLocales();

        foreach ($locales as $locale) {
            $defaultTranslation = $this->defaultTranslationResolver->resolve($productTypeFieldConfiguration, $locale);
            $fieldValueTranslation = $this->fieldValueTranslationResolver->resolve($field, $locale, $defaultTranslation->getValue());

            if (null !== $requestData) {
                $fieldValueTranslation->setValue(
                    $this->fieldValueExtractor->extract($field->getType(), $requestData, $uploadedFilePaths, $locale)
                );
            }

            $field->addTranslation($fieldValueTranslation);
        }
    }
}
