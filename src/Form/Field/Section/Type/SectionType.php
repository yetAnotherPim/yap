<?php

declare(strict_types=1);

namespace App\Form\Field\Section\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<null>
 */
class SectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'separator';
    }
}
