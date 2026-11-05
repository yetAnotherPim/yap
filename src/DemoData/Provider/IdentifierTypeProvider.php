<?php

declare(strict_types=1);

namespace App\DemoData\Provider;

use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Repository\IdentifierTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class IdentifierTypeProvider
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IdentifierTypeRepository $identifierTypeRepository,
    ) {
    }

    /**
     * @return array<string, IdentifierType>
     */
    public function getIdentifierTypes(): array
    {
        return [
            'sku' => $this->getOrCreate('SKU'),
            'gtin' => $this->getOrCreate('GTIN'),
        ];
    }

    private function getOrCreate(string $title): IdentifierType
    {
        $identifierType = $this->identifierTypeRepository->findOneBy([
            'title' => $title,
        ]);

        if ($identifierType instanceof IdentifierType) {
            return $identifierType;
        }

        $identifierType = new IdentifierType()
            ->setTitle($title)
            ->setDataType(DataType::String);

        $this->entityManager->persist($identifierType);

        return $identifierType;
    }
}
