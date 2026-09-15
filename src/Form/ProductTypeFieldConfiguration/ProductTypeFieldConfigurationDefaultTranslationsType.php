<?php

declare(strict_types=1);

namespace App\Form\ProductTypeFieldConfiguration;

use App\Repository\LocaleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, ?string>>
 */
class ProductTypeFieldConfigurationDefaultTranslationsType extends AbstractType
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $locales = $this->localeRepository->getAvailableContentTranslationLocales();

        foreach ($locales as $locale) {
            $builder->add(
                $locale->getId(),
                TextareaType::class,
                [
                    'label' => $locale->getId(),
                    'required' => false,
                    'mapped' => false,
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
