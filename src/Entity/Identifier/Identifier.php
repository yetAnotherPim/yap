<?php

declare(strict_types=1);

namespace App\Entity\Identifier;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\IdentifierRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: IdentifierRepository::class)]
#[Index(name: 'index_type_id', fields: ['type'])]
#[Index(name: 'index_product_id', fields: ['product'])]
#[ORM\UniqueConstraint(name: 'identifier_product_type', fields: ['type', 'product'])]
#[ApiResource]
class Identifier
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private IdentifierType $type;

    // TODO: convert to int or string
    #[ORM\Column(length: 255)]
    #[NotBlank]
    private string $value;

    #[ORM\ManyToOne(inversedBy: 'identifiers')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): IdentifierType
    {
        return $this->type;
    }

    public function setType(IdentifierType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    // TODO: route products/{gtin}/54653456546 + test

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
