<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Synchronizer;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Resolver\FieldResolver;

class FieldSynchronizer
{
    public function __construct(
        private readonly FieldResolver $fieldResolver,
    ) {
    }

    /**
     * @param array<mixed>|null $requestData
     */
    public function synchronizeFields(Product $product, ?array $requestData = null): void
    {
        // TODO: frontend test
        // -> 1.fields are created on creation with default value
        // -> 2.fields are updated correctly (every type) - value changed
        // -> 3.new fields will be added and displayed with default value

        // TODO: integration test
        // -> 1.fieldtype deletion will remove assigned fields

        foreach ($product->getProductType()->getProductTypeFieldConfigurations() as $configuration) {
            $fieldType = $configuration->getFieldType();

            if (null === $fieldType) {
                continue;
            }

            if (InputType::Section === $fieldType->getInputType()) {
                continue;
            }

            $field = $this->fieldResolver->resolve(
                $product,
                $fieldType,
                $configuration->getDefaultValue(),
            );

            if (null !== $requestData) {
                $field->setValue(
                    $this->getFieldValue(
                        $fieldType,
                        $requestData,
                    ),
                );
            }

            $product->addField($field);
        }
    }

    /**
     * @param array<mixed> $requestData
     */
    private function getFieldValue(FieldType $fieldType, array $requestData): ?string
    {
        // TODO: handle uploads
        $formFieldName = 'field_'.$fieldType->getCode();

        if (InputType::Boolean === $fieldType->getInputType()) {
            return (string) \array_key_exists($formFieldName, $requestData);
        }

        /** @var string|int|bool|float|null $value */
        $value = $requestData[$formFieldName] ?? null;

        return '' === $value ? null : (string) $value;
    }
}
