<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Type;

use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\Fields\Renderer\Registry\BatchEditFieldRendererInterface;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchEditFieldType extends AbstractType
{
    public function __construct(
        private readonly RendererRegistry $rendererRegistry,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {
                $data = $event->getData();

                if (!$data instanceof BatchEditFieldData) {
                    return;
                }

                $fieldType = $data->getFieldType();

                if (null === $fieldType) {
                    return;
                }

                $renderer = $this->rendererRegistry->getRenderer($fieldType);

                if (!$renderer instanceof BatchEditFieldRendererInterface) {
                    return;
                }

                $formType = $renderer->getBatchEditFormType($fieldType);

                $options = [
                    'required' => false,
                    'label' => 'general.labels.value',
                    'translation_domain' => 'admin',
                ];

                if (TextareaType::class === $formType) {
                    $options['attr'] = [
                        'rows' => 4,
                        'class' => 'batch-edit-textarea',
                    ];
                }

                $event->getForm()->add('value', $formType, $options);

                if (!$fieldType->isTranslatable()) {
                    return;
                }

                $event->getForm()->add('translations', CollectionType::class, [
                    'entry_type' => BatchEditFieldTranslationType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required' => false,
                    'entry_options' => [
                        'label' => false,
                        'form_type' => $formType,
                    ],
                    'prototype_name' => '__translation__',
                ]);
            },
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BatchEditFieldData::class,
        ]);
    }
}
