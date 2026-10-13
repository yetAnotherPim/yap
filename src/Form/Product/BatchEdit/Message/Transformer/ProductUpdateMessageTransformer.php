<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Message\Transformer;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Messenger\ProductUpdate\Message\Dto\FieldTranslation;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;

class ProductUpdateMessageTransformer
{
    public function transform(
        BatchEditData $data,
        string $productId,
    ): ProductUpdateMessage {
        $message = new ProductUpdateMessage($productId);

        foreach ($data->getFields() as $fieldData) {
            $fieldType = $fieldData->getFieldType();

            if (null === $fieldType) {
                continue;
            }

            $message->addField($this->mapField($fieldData, $fieldType));
        }

        return $message;
    }

    private function mapField(BatchEditFieldData $data, FieldType $fieldType): ProductField
    {
        $field = new ProductField(
            $this->mapIdentifier($fieldType),
            $data->getValue(),
        );

        if (false === $fieldType->isTranslatable()) {
            return $field;
        }

        $this->mapTranslations($data, $field);

        return $field;
    }

    private function mapTranslations(
        BatchEditFieldData $data,
        ProductField $field,
    ): void {
        foreach ($data->getTranslations() as $translationData) {
            $locale = $translationData->getLocale();

            if (null === $locale) {
                continue;
            }

            $field->addTranslation(
                new FieldTranslation(
                    $locale->getId(),
                    $translationData->getValue(),
                ),
            );
        }
    }

    private function mapIdentifier(FieldType $fieldType): FieldTypeIdentifier
    {
        return new FieldTypeIdentifier(
            FieldTypeIdentifierType::ID,
            (string) $fieldType->getId(),
        );
    }
}
