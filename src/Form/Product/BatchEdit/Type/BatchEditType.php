<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Type;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<BatchEditData>
 */
class BatchEditType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
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

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event): void {
                $data = $event->getData();

                if (!\is_array($data)) {
                    return;
                }

                $fields = $data['fields'] ?? null;

                if (!\is_array($fields) || [] === $fields) {
                    return;
                }

                $fieldTypes = $this->entityManager
                    ->getRepository(FieldType::class)
                    ->findBy(['code' => array_keys($fields)]);

                $fieldTypesByCode = [];

                foreach ($fieldTypes as $fieldType) {
                    if (InputType::Section !== $fieldType->getInputType()) {
                        $fieldTypesByCode[$fieldType->getCode()] = $fieldType;
                    }
                }

                $form = $event->getForm()->get('fields');

                foreach ($fields as $code => $fieldData) {
                    if (
                        !\is_string($code)
                        || !\is_array($fieldData)
                        || !isset($fieldTypesByCode[$code])
                    ) {
                        unset($fields[$code]);
                        continue;
                    }

                    if (!$form->has($code)) {
                        $form->add($code, BatchEditFieldType::class, [
                            'data' => new BatchEditFieldData($fieldTypesByCode[$code]),
                            'label' => false,
                        ]);
                    }
                }

                $data['fields'] = $fields;
                $event->setData($data);
            },
            100,
        );
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
