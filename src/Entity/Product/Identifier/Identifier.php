<?php

declare(strict_types=1);

namespace App\Entity\Product\Identifier;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\IdentifierRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: IdentifierRepository::class)]
#[Index(name: 'index_product_identifier_type', fields: ['type'])]
#[Index(name: 'index_product_intendifier_product', fields: ['product'])]
#[ORM\UniqueConstraint(name: 'unique_product_identifier_product_type', fields: ['type', 'product'])]
#[Get]
#[GetCollection]
#[Post]
#[Patch]
#[Delete]
#[ORM\HasLifecycleCallbacks]
class Identifier extends RelatedTimestampableEntity
{
    protected array $updatableRelations = [
        Product::class => 'product',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product:read', 'product:read:without-translations', 'product:write'])]
    private IdentifierType $type;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Groups(['product:read', 'product:read:without-translations', 'product:write'])]
    private string $value = '';

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

    #[ApiProperty(
        openapiContext: [
            'example' => '040005255656',
        ],
        types: ['integer', 'string']
    )]
    #[Groups(['product:read', 'product:read:without-translations'])]
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

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
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
}
