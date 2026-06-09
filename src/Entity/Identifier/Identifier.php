<?php

declare(strict_types=1);

namespace App\Entity\Identifier;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use App\Entity\Identifier\Type\Enum\DataType;
use App\Entity\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\IdentifierRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: IdentifierRepository::class)]
#[Index(name: 'index_type_id', fields: ['type'])]
#[Index(name: 'index_product_id', fields: ['product'])]
#[ORM\UniqueConstraint(name: 'identifier_product_type', fields: ['type', 'product'])]
#[Get]
#[GetCollection]
#[Post]
#[Patch]
#[Delete]
#[GetCollection(
    uriTemplate: 'filter/identifiers',
    openapi: new Operation(summary: 'Filter products by identifier and it\'s type', tags: ['filter']),
    normalizationContext: ['groups' => ['identifier:filter']],
    parameters: [
        'value' => new QueryParameter(filter: new ExactFilter(), property: 'value', required: true),
        'type' => new QueryParameter(filter: new ExactFilter(), property: 'type.title', required: true),
    ]
)]
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
    #[Groups(['product:read', 'identifier:filter'])]
    private IdentifierType $type;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Groups(['product:read', 'identifier:filter'])]
    private string $value;

    #[ORM\ManyToOne(cascade: ['all'], inversedBy: 'identifiers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['identifier:filter'])]
    private Product $product;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    #[ApiProperty(
        openapiContext: [
            'example' => '040005255656',
        ],
        types: ['integer', 'string']
    )]
    #[Groups(['product:read', 'identifier:filter'])]
    public function getTypedValue(): string|int
    {
        if (true === isset($this->type) && DataType::Integer === $this->getType()->getDataType()) {
            return (int) $this->value;
        }

        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
