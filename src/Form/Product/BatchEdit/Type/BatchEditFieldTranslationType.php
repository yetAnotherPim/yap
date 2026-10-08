<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Type;

use App\Entity\Locale\Locale;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldTranslationData;
use App\Repository\LocaleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<BatchEditFieldTranslationData>
 */
class BatchEditFieldTranslationType extends AbstractType
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    /**
     * @param array{
     *     form_type: class-string<FormTypeInterface<mixed>>,
     *     form_options: array<string, mixed>
     * } $options
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $valueOptions = [
            'required' => false,
            'label' => 'general.labels.value',
            'translation_domain' => 'admin',
            ...$options['form_options'],
        ];

        $builder
            ->add('locale', EntityType::class, [
                'class' => Locale::class,
                'choices' => $this->localeRepository
                    ->getAvailableContentTranslationLocales()
                    ->toArray(),
                'choice_label' => 'id',
                'label' => 'general.labels.locale',
                'translation_domain' => 'admin',
                'placeholder' => '',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('value', $options['form_type'], $valueOptions);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BatchEditFieldTranslationData::class,
        ]);

        $resolver->setRequired([
            'form_type',
            'form_options',
        ]);

        $resolver->setAllowedTypes('form_type', 'string');
        $resolver->setAllowedTypes('form_options', 'array');
    }
}
