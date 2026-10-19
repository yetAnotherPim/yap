<?php

declare(strict_types=1);

namespace App\Form\ProductType\DefaultTranslation\Synchronizer;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use Doctrine\Common\Collections\Collection;

class DefaultTranslationSynchronizer
{
    public function __construct(
        private readonly DefaultTranslationResolver $productTypeFieldConfigurationDefaultTranslationResolver,
    ) {
    }

    /**
     * @param array<array-key, mixed> $values
     * @param Collection<int, Locale> $locales
     */
    public function synchronize(
        ProductTypeFieldConfiguration $configuration,
        Collection $locales,
        array $values,
    ): void {
        $fieldType = $configuration->getFieldType();

        if (null === $fieldType) {
            return;
        }

        foreach ($values as $localeId => $value) {
            if (!\is_string($localeId)) {
                continue;
            }

            $locale = $locales->findFirst(
                static fn (
                    int $key,
                    Locale $locale,
                ): bool => $locale->getId() === $localeId
            );

            if (!$locale instanceof Locale) {
                continue;
            }

            $translation = $this->productTypeFieldConfigurationDefaultTranslationResolver->resolve(
                $configuration,
                $locale,
            );

            if (false === $fieldType->isTranslatable()) {
                $configuration->removeProductTypeFieldConfigurationDefaultTranslation($translation);
            }

            $value = \is_string($value) && '' !== $value
                ? $value
                : null;

            $translation->setValue($value);
        }
    }
}
