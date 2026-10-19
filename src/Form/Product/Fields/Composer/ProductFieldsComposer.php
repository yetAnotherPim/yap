<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Composer;

use App\Entity\Product\Product;
use App\Form\Product\Fields\Composer\Translation\ProductFieldTranslationComposer;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Symfony\Component\Translation\TranslatableMessage;

class ProductFieldsComposer
{
    public function __construct(
        private readonly ProductFieldTranslationComposer $productFieldTranslationComposer,
        private readonly ProductFieldsRenderer $productFieldsRenderer,
    ) {
    }

    /**
     * @return array<FieldInterface>
     */
    public function compose(Product $product): array
    {
        return array_merge(
            $this->getFormFields(),
            $this->productFieldsRenderer->render($product),
            $this->productFieldTranslationComposer->compose($product),
        );
    }

    /**
     * @return array<FieldInterface>
     */
    private function getFormFields(): array
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

        return $fields;
    }
}
