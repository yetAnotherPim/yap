<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Form\Field\FieldTypes\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FieldTypesType extends AbstractType
{
    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options,
    ): void {
        $view->vars['field_types'] = $options['field_types'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'field_types' => [],
            'mapped' => false,
            'required' => false,
        ]);

        $resolver->setAllowedTypes('field_types', 'array');
    }

    public function getParent(): ?string
    {
        return FormType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'feed_field_types';
    }
}
