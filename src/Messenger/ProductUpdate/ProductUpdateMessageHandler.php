<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Product;
use App\Messenger\ProductUpdate\Message\Dto\FieldTranslation;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use App\Messenger\ProductUpdate\Resolver\FieldResolver;
use App\Messenger\ProductUpdate\Resolver\FieldValueTranslationResolver;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ProductUpdateMessageHandler
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly FieldResolver $fieldResolver,
        private readonly FieldValueTranslationResolver $fieldValueTranslationResolver,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[AsMessageHandler(sign: false)]
    public function handleProductUpdateMessage(ProductUpdateMessage $message): void
    {
        $product = $this->productRepository->find($message->getProductId());

        if (!$product instanceof Product) {
            return;
        }

        foreach ($message->getFields() as $productField) {
            $this->updateField($product, $productField);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    private function updateField(Product $product, ProductField $productField): void
    {
        $field = $this->fieldResolver->resolve(
            $product,
            $productField->getIdentifier(),
        );

        if (!$field instanceof Field) {
            return;
        }

        $field->setValue($productField->getValue());

        foreach ($productField->getTranslations() as $translationData) {
            $this->updateTranslation($field, $translationData);
        }
    }

    private function updateTranslation(
        Field $field,
        FieldTranslation $translationData,
    ): void {
        $translation = $this->fieldValueTranslationResolver->resolve(
            $field,
            $translationData->getLocaleId(),
        );

        if (null === $translation) {
            return;
        }

        $translation->setValue($translationData->getValue());
    }
}
