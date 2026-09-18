<?php

declare(strict_types=1);

namespace App\Form\ProductType\DefaultTranslation\Type;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, ?string>>
 */
class DefaultTranslationsType extends AbstractType
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly DefaultTranslationResolver $defaultTranslationResolver,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $locales = $this->localeRepository
            ->getAvailableContentTranslationLocales();

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($locales): void {
                $this->addTranslationFields(
                    $event,
                    $locales,
                );
            },
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($locales): void {
                $this->addSubmittedTranslationFields(
                    $event,
                    $locales,
                );
            },
        );
    }

    /**
     * @param Collection<int, Locale> $locales
     */
    private function addTranslationFields(
        FormEvent $event,
        Collection $locales,
    ): void {
        $form = $event->getForm();

        $configuration = $form->getParent()?->getData();

        if (!$configuration instanceof ProductTypeFieldConfiguration) {
            return;
        }

        foreach ($locales as $locale) {
            $localeId = $locale->getId();

            $translation = $this
                ->defaultTranslationResolver
                ->resolve(
                    $configuration,
                    $locale,
                );

            $this->addTranslationField(
                $form,
                $localeId,
                [
                    'data' => $translation->getValue(),
                ],
            );
        }
    }

    /**
     * @param Collection<int, Locale> $locales
     */
    private function addSubmittedTranslationFields(
        FormEvent $event,
        Collection $locales,
    ): void {
        $form = $event->getForm();

        foreach ($locales as $locale) {
            $localeId = $locale->getId();

            if ($form->has($localeId)) {
                continue;
            }

            $this->addTranslationField(
                $form,
                $localeId,
            );
        }
    }

    /**
     * @param FormInterface<array<string, ?string>> $form
     * @param array<string, mixed>                  $options
     */
    private function addTranslationField(
        FormInterface $form,
        string $localeId,
        array $options = [],
    ): void {
        $form->add(
            $localeId,
            TextareaType::class,
            array_merge(
                [
                    'label' => $localeId,
                    'required' => false,
                    'mapped' => false,
                ],
                $options,
            ),
        );
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
