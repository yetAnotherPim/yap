<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer;

use App\Entity\Locale\Locale;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Resolver\FieldResolver;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductFieldsRenderer
{
    public function __construct(
        private readonly RendererRegistry $rendererRegistry,
        private readonly FieldResolver $fieldResolver,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<FieldInterface>
     */
    public function render(Product $product, ?Locale $locale = null): array
    {
        $fields = [];

        foreach ($product->getProductType()->getProductTypeFieldConfigurations() as $productTypeFieldConfiguration) {
            $fieldType = $productTypeFieldConfiguration->getFieldType();

            if (null === $fieldType) {
                continue;
            }

            $field = $this->fieldResolver->resolve(
                $product,
                $fieldType,
                (null === $locale) ? $productTypeFieldConfiguration->getDefaultValue() : null,
            );

            $fields[] = $this->rendererRegistry
                ->getRenderer($field->getType())
                ->render(
                    $field,
                    $productTypeFieldConfiguration,
                    $fieldType->getTranslatedTitleByLocale(
                        $this->translator->getLocale(),
                    ),
                    $locale
                );
        }

        return $fields;
    }
}
