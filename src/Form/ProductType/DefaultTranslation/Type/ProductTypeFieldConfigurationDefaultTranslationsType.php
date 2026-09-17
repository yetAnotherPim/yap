<?php

declare(strict_types=1);

namespace App\Form\ProductType\DefaultTranslation\Type;

use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\ProductType\DefaultTranslation\Resolver\ProductTypeFieldConfigurationDefaultTranslationResolver;
use App\Repository\LocaleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, ?string>>
 */
class ProductTypeFieldConfigurationDefaultTranslationsType extends AbstractType
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly ProductTypeFieldConfigurationDefaultTranslationResolver $productTypeFieldConfigurationDefaultTranslationResolver,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            $this->addTranslationFields(...),
        );
    }

    private function addTranslationFields(FormEvent $event): void
    {
        $form = $event->getForm();

        $configuration = $form->getParent()?->getData();

        if (!$configuration instanceof ProductTypeFieldConfiguration) {
            return;
        }

        $locales = $this->localeRepository
            ->getAvailableContentTranslationLocales();

        foreach ($locales as $locale) {
            $translation = $this->productTypeFieldConfigurationDefaultTranslationResolver->resolve($configuration, $locale);

            $form->add(
                $locale->getId(),
                TextareaType::class,
                [
                    'label' => $locale->getId(),
                    'required' => false,
                    'mapped' => false,
                    'data' => $translation->getValue(),
                ],
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            'allow_add',
            'allow_delete',
            'delete_empty',
            'entry_options',
            'entry_type',
            'prototype_data',
            'prototype_name',
            'prototype_options',
        ]);
    }
}
