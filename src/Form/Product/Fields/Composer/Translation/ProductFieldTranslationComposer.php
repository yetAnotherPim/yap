<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Composer\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Symfony\Component\Translation\TranslatableMessage;

class ProductFieldTranslationComposer
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly ProductFieldsRenderer $productFieldsRenderer,
    ) {
    }

    /**
     * @return array<FieldInterface>
     */
    public function compose(Product $product): array
    {
        $locales = $this->localeRepository->getAvailableContentTranslationLocales();

        if (true === $locales->isEmpty()) {
            return [];
        }

        $fields = $this->getFormFields($locales);

        foreach ($locales as $locale) {
            $fields = array_merge($fields, $this->productFieldsRenderer->render($product, $locale));
        }

        return $fields;
    }

    /**
     * @param Collection<int, Locale> $locales
     *
     * @return array<FieldInterface>
     */
    private function getFormFields(Collection $locales): array
    {
        $localeIds = $this->localeRepository->extractLocaleIds($locales);

        $fields = [];
        $fields[] = FormField::addColumn(6);
        $fields[] = FormField::addFieldset(new TranslatableMessage('general.labels.translation', [], 'admin'));
        $fields[] = LocaleField::new('translation_locale', new TranslatableMessage('general.labels.locale', [], 'admin'))
            ->includeOnly($localeIds)
            ->addCssClass('translation-locale')
            ->setFormTypeOptions([
                'data' => '',
                'mapped' => false,
            ])
            ->setHtmlAttribute(
                'data-controller',
                'content-translation',
            )
            ->setHtmlAttribute(
                'data-action',
                'change->content-translation#update',
            );

        return $fields;
    }
}
