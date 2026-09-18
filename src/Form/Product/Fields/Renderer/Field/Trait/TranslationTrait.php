<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field\Trait;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Field\Section\SectionField;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @phpstan-type RenderedField BooleanField
 *     |ChoiceField
 *     |ColorField
 *     |CountryField
 *     |DateField
 *     |DateTimeField
 *     |EmailField
 *     |FileField
 *     |ImageField
 *     |IntegerField
 *     |LanguageField
 *     |LocaleField
 *     |NumberField
 *     |SectionField
 *     |TelephoneField
 *     |TextareaField
 *     |TextEditorField
 *     |TextField
 *     |TimeField
 *     |UrlField
 */
trait TranslationTrait
{
    public function __construct(
        protected readonly DefaultTranslationResolver $defaultTranslationResolver,
    ) {
    }

    protected function getTranslationSuffix(?Locale $locale): string
    {
        if (null === $locale) {
            return '';
        }

        return '-'.$locale->getId();
    }

    /**
     * @param RenderedField $element
     */
    protected function addTranslationOptions(FieldInterface $element, Field $field, ?Locale $locale): FieldInterface
    {
        if (null === $locale) {
            return $element;
        }

        if (false === $field->getType()->isTranslatable()) {
            $element->addCssClass('translation-field-spacer');

            // disable non-translatable fields for reducing payload
            $element->setFormTypeOption('attr', [
                'disabled' => true,
            ]);
        }

        $element->setFormTypeOption('row_attr', [
            'data-content-translation-locale' => $locale->getId(),
            'hidden' => true,
        ]);

        return $element;
    }

    protected function getFieldValue(Field $field, ProductTypeFieldConfiguration $configuration, ?Locale $locale): ?string
    {
        if (null === $locale) {
            return $field->getValue();
        }

        if (false === $field->getType()->isTranslatable()) {
            return null;
        }

        $fieldTranslation = $field->getTranslationByLocale($locale->getId());

        if (null === $fieldTranslation) {
            return $this->defaultTranslationResolver->resolve($configuration, $locale)->getValue();
        }

        return $fieldTranslation->getValue();
    }
}
