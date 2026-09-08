<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Synchronizer;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Resolver\FieldResolver;
use App\Form\Product\Fields\Synchronizer\Extractor\FieldValueExtractor;
use App\Form\Product\Fields\Synchronizer\Translation\FieldTranslationSynchronizer;

class FieldSynchronizer
{
    public function __construct(
        private readonly FieldResolver $fieldResolver,
        private readonly FieldValueExtractor $fieldValueExtractor,
        private readonly FieldTranslationSynchronizer $fieldTranslationSynchronizer,
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
        // -> 2.fields aren't doubled

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
                    $this->fieldValueExtractor->extract(
                        $fieldType,
                        $requestData,
                        $uploadedFilePaths,
                    ),
                );
            }

            $this->fieldTranslationSynchronizer->synchronizeTranslation($field, $requestData, $uploadedFilePaths);

            $product->addField($field);
        }
    }
}
