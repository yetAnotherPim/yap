<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Type;

use App\Entity\Locale\Locale;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldTranslationData;
use App\Repository\LocaleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchEditFieldTranslationType extends AbstractType
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $valueOptions = [
            'required' => false,
            'label' => 'general.labels.value',
            'translation_domain' => 'admin',
        ];

        if (TextareaType::class === $options['form_type']) {
            $valueOptions['attr'] = [
                'rows' => 4,
                'class' => 'batch-edit-textarea',
            ];
        }

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

        $resolver->setRequired('form_type');

        $resolver->setAllowedTypes('form_type', [
            'string',
        ]);
    }
}
