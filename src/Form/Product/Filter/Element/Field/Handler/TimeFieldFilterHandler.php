<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ComparisonFilterType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class TimeFieldFilterHandler extends AbstractComparableFieldFilterHandler
{
    public function supports(FieldType $fieldType): bool
    {
        return InputType::Time === $fieldType->getInputType();
    }

    public function getFormType(): string
    {
        return ComparisonFilterType::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormTypeOptions(): array
    {
        return [
            'value_type' => TimeType::class,
            'value_type_options' => [
                'input' => 'datetime',
                'widget' => 'single_text',
            ],
        ];
    }

    protected function normalizeValue(mixed $value, FieldType $fieldType): ?string
    {
        if (!$value instanceof \DateTimeInterface) {
            return null;
        }

        return $value->format('H:i:s');
    }
}
