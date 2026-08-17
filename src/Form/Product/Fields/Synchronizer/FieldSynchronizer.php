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
     * @param array<mixed>|null    $requestData
     * @param array<string, mixed> $uploadedFilePaths
     */
    public function synchronizeFields(Product $product, ?array $requestData = null, array $uploadedFilePaths = []): void
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
                        $uploadedFilePaths,
                    ),
                );
            }

            $product->addField($field);
        }
    }

    /**
     * @param array<mixed>         $requestData
     * @param array<string, mixed> $uploadedFilePaths
     */
    private function getFieldValue(FieldType $fieldType, array $requestData, array $uploadedFilePaths = []): ?string
    {
        $formFieldName = 'field_'.$fieldType->getCode();

        if (InputType::CheckBox === $fieldType->getInputType()) {
            return (string) \array_key_exists($formFieldName, $requestData);
        }

        if (InputType::File === $fieldType->getInputType() || InputType::Image === $fieldType->getInputType()) {
            return isset($uploadedFilePaths[$formFieldName])
                // @phpstan-ignore-next-line
                ? (string) $uploadedFilePaths[$formFieldName]
                : null;
        }

        /** @phpstan-var array<int,string>|string|int|bool|float|null $value */
        $value = $requestData[$formFieldName] ?? null;

        if (InputType::MultiSelect === $fieldType->getInputType()) {
            /** @phpstan-var array<int,string>|null $value */
            return (null !== $value) ? implode(',', $value) : null;
        }

        if (true === \is_array($value)) {
            return '';
        }

        return '' === $value ? null : (string) $value;
    }
}
