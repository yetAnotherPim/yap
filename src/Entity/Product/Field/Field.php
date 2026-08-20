<?php

declare(strict_types=1);

namespace App\Entity\Product\Field;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Repository\FieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FieldRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
#[Index(name: 'index_product_field_type', fields: ['type'])]
#[Index(name: 'index_product_field_product', fields: ['product'])]
#[Index(name: 'index_product_field_value', fields: ['value'])]
#[ORM\UniqueConstraint(name: 'unique_product_field_product_type', fields: ['type', 'product'])]
#[Patch(
    denormalizationContext: ['groups' => ['field:write']],
    normalizationContext: ['groups' => ['field:read']]
)]
#[Post(denormalizationContext: ['groups' => ['field:write']])]
#[Get]
#[GetCollection]
#[Delete]
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
    #[Groups(['product:read', 'product:write', 'field:write', 'field:read'])]
    private FieldType $type;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['field:write'])]
    private Product $product;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['product:read', 'product:write', 'field:write', 'field:read'])]
    #[Context([
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ])]
    private ?string $value = null;

    /**
     * @var Collection<int, FieldValueTranslation>
     */
    #[ORM\OneToMany(targetEntity: FieldValueTranslation::class, mappedBy: 'field', cascade: ['all'], orphanRemoval: true)]
    #[Groups(['product:read', 'product:write', 'field:write', 'field:read'])]
    private Collection $translations;

    public function __construct()
    {
        parent::__construct();
        $this->translations = new ArrayCollection();
    }

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

    #[ApiProperty(
        types: ['integer', 'float', 'boolean', 'string']
    )]
    #[Context([
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
    ])]
    #[Groups(['product:read', 'field:read'])]
    public function getTypedValue(): string|int|bool|float|null
    {
        if (false === isset($this->value)) {
            return null;
        }

        return InputType::getTypedValue($this->value, $this->getType()->getInputType());
    }

    /**
     * @return Collection<int, FieldValueTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(FieldValueTranslation $fieldValueTranslation): static
    {
        if (!$this->translations->contains($fieldValueTranslation)) {
            $this->translations->add($fieldValueTranslation);
            $fieldValueTranslation->setField($this);
        }

        return $this;
    }

    public function removeTranslation(FieldValueTranslation $fieldValueTranslation): static
    {
        $this->translations->removeElement($fieldValueTranslation);

        return $this;
    }
}
