<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer;

use App\Entity\Product\Product;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Renderer\Translation\ProductFieldsTranslationRenderer;
use App\Form\Product\Fields\Resolver\FieldResolver;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductFieldsRenderer
{
    public function __construct(
        private readonly RendererRegistry $rendererRegistry,
        private readonly FieldResolver $fieldResolver,
        private readonly TranslatorInterface $translator,
        private readonly ProductFieldsTranslationRenderer $productFieldsTranslationRenderer,
    ) {
    }

    /**
     * @return array<FieldInterface>
     */
    public function render(Product $product): array
    {
        $fields = [];

        $fields[] = FormField::addTab(new TranslatableMessage('product.labels.fields', [], 'admin'));
        $fields[] = FormField::addColumn(6);
        $fields[] = FormField::addFieldset(new TranslatableMessage('product.labels.fields', [], 'admin'));
        $fields[] = LocaleField::new('translation_locale_spacer', 'Sprache')
            ->addCssClass('translation-locale-spacer')
            ->setFormTypeOptions([
                'mapped' => false,
            ]);

        foreach ($product->getProductType()->getProductTypeFieldConfigurations() as $productTypeFieldConfiguration) {
            $fieldType = $productTypeFieldConfiguration->getFieldType();

            if (null === $fieldType) {
                continue;
            }

            $field = $this->fieldResolver->resolve(
                $product,
                $fieldType,
                $productTypeFieldConfiguration->getDefaultValue()
            );

            $fields[] = $this->rendererRegistry
                ->getRenderer($field)
                ->render(
                    $field,
                    $productTypeFieldConfiguration,
                    $fieldType->getTranslatedTitle(
                        $this->translator->getLocale(),
                    )
                );
        }

        return array_merge(
            $fields,
            $this->productFieldsTranslationRenderer->render($product),
        );
    }
}
