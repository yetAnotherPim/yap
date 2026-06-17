<?php

declare(strict_types=1);

namespace App\Entity\Product\Field;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Repository\FieldRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FieldRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
#[Index(name: 'index_field_type_id', fields: ['type'])]
#[Index(name: 'index_field_product_id', fields: ['product'])]
#[Index(name: 'index_field_value', fields: ['value'])]
#[ORM\UniqueConstraint(name: 'field_product_type', fields: ['type', 'product'])]
class Field extends RelatedTimestampableEntity
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
    private FieldType $type;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $value = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    public function setType(FieldType $type): static
    {
        $this->type = $type;

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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
