<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Translation;

use App\Entity\Product\Product;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Resolver\FieldResolver;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductFieldsTranslationRenderer
{
    public function __construct(
        private readonly RendererRegistry $rendererRegistry,
        private readonly FieldResolver $fieldResolver,
        private readonly TranslatorInterface $translator,
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    /**
     * @return array<FieldInterface>
     */
    public function render(Product $product): array
    {
        $fields = [];

        // TODO: load only locales for content translation
        $locales = $this->localeRepository->getAvailableInterfaceTranslationLocales();

        if (true === $locales->isEmpty()) {
            return $fields;
        }

        $localeIds = $this->localeRepository->extractLocaleIds($locales);

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

        // TODO: beautify - avoid foreach inside foreach
        // TODO: hide initially
        foreach ($locales as $locale) {
            foreach ($product->getProductType()->getProductTypeFieldConfigurations() as $productTypeFieldConfiguration) {
                $fieldType = $productTypeFieldConfiguration->getFieldType();

                if (null === $fieldType) {
                    continue;
                }

                // TODO: different resolver for translation value
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
                        ),
                        $locale
                    );
            }
        }

        return $fields;
    }
}
