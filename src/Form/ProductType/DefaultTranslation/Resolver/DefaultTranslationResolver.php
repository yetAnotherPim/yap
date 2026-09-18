<?php

declare(strict_types=1);

namespace App\Form\ProductType\DefaultTranslation\Resolver;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;

class DefaultTranslationResolver
{
    public function resolve(
        ProductTypeFieldConfiguration $configuration,
        Locale $locale,
    ): ProductTypeFieldConfigurationDefaultTranslation {
        $defaultTranslation = $configuration
            ->getProductTypeFieldConfigurationDefaultTranslations()
            ->findFirst(
                static fn (int $key, ProductTypeFieldConfigurationDefaultTranslation $defaultTranslation): bool => $defaultTranslation->getLocale()->getId() === $locale->getId()
            );

        if (null !== $defaultTranslation) {
            return $defaultTranslation;
        }

        $translation = new ProductTypeFieldConfigurationDefaultTranslation();

        $translation->setLocale($locale);
        $translation->setProductTypeFieldConfiguration(
            $configuration,
        );

        $configuration
            ->addProductTypeFieldConfigurationDefaultTranslation(
                $translation,
            );

        return $translation;
    }
}
