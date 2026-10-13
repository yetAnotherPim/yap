<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Resolver;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use App\Repository\FieldTypeRepository;

class FieldResolver
{
    public function __construct(private readonly FieldTypeRepository $fieldTypeRepository)
    {
    }

    public function resolve(Product $product, FieldTypeIdentifier $fieldTypeIdentifier): ?Field
    {
        $field = $product
            ->getFields()
            ->findFirst(
                static fn (int $key, Field $field): bool => match ($fieldTypeIdentifier->getType()) {
                    FieldTypeIdentifierType::ID => (string) $field->getType()->getId() === $fieldTypeIdentifier->getValue(),
                    FieldTypeIdentifierType::CODE => $field->getType()->getCode() === $fieldTypeIdentifier->getValue(),
                },
            );

        if ($field instanceof Field) {
            return $field;
        }

        $fieldType = $this->fieldTypeRepository->findByIdentifier($fieldTypeIdentifier);

        if (null === $fieldType) {
            return null;
        }

        if (false === $this->isConfigured($product, $fieldType)) {
            return null;
        }

        $field = new Field()
            ->setType($fieldType);

        $product->addField($field);

        return $field;
    }

    private function isConfigured(Product $product, FieldType $fieldType): bool
    {
        return $product
            ->getProductType()
            ->getProductTypeFieldConfigurations()
            ->exists(
                static fn (int $key, ProductTypeFieldConfiguration $configuration): bool => $configuration->getFieldType()?->getId()->equals($fieldType->getId()) ?? false,
            );
    }
}
