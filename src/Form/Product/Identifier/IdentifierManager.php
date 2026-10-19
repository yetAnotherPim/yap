<?php

declare(strict_types=1);

namespace App\Form\Product\Identifier;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Product;
use App\Repository\IdentifierTypeRepository;

class IdentifierManager
{
    public function __construct(
        private readonly IdentifierTypeRepository $identifierTypeRepository,
    ) {
    }

    public function initializeIdentifiers(Product $product): void
    {
        $identifierTypes = $this->identifierTypeRepository->findAll();

        foreach ($identifierTypes as $identifierType) {
            $identifier = $product->getIdentifiers()->findFirst(
                static fn (int $key, Identifier $identifier): bool => $identifier->getType()->getId() === $identifierType->getId()
            );

            if (null !== $identifier) {
                continue;
            }

            $product->addIdentifier(
                new Identifier()
                    ->setType($identifierType)
            );
        }
    }

    public function clearUnsetIdentifiers(Product $product): void
    {
        foreach ($product->getIdentifiers() as $identifier) {
            if (true === empty($identifier->getValue())) {
                $product->removeIdentifier($identifier);
            }
        }
    }
}
