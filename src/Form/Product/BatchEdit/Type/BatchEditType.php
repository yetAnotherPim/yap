<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Type;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class BatchEditType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $locale = $this->translator->getLocale();

        $builder
            ->add('fieldType', EntityType::class, [
                'class' => FieldType::class,
                'mapped' => false,
                'choice_label' => static fn (
                    FieldType $fieldType,
                ): string => $fieldType->getTranslatedTitleByLocale($locale),
                'choice_filter' => static fn (
                    ?FieldType $fieldType,
                ): bool => null !== $fieldType
                    && InputType::Section !== $fieldType->getInputType(),
                'required' => false,
            ])
            ->add('fields', CollectionType::class, [
                'entry_type' => BatchEditFieldType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BatchEditData::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'batch_edit';
    }
}
