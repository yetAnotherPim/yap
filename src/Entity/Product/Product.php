<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Doctrine\Orm\Filter\ComparisonFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Type\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

// TODO: add parent relation -> provide fieldTypes[] for distinction

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Index(name: 'index_product_producttype', columns: ['product_type_id'])]
#[Get(
    normalizationContext: ['groups' => ['product:read']]
)]
#[GetCollection(
    normalizationContext: ['groups' => ['product:read']],
    parameters: [
        'createdAt' => new QueryParameter(
            filter: new ComparisonFilter(new ExactFilter()),
            property: 'createdAt',
        ),
        'updatedAt' => new QueryParameter(
            filter: new ComparisonFilter(new ExactFilter()),
            property: 'updatedAt',
        ),
        'hasIdentifier' => new QueryParameter(
            filter: new ExistsFilter(),
            property: 'identifiers',
            description: 'Filter products that have/don\'t have identifiers',
        ),
        'identifierIs' => new QueryParameter(
            filter: new ExactFilter(),
            property: 'identifiers.value',
            description: 'Filter products by identifier matching value exactly',
        ),
        'identifierLike' => new QueryParameter(
            filter: new PartialSearchFilter(),
            property: 'identifiers.value',
            description: 'Filter products by identifier matching value partially',
        ),
        'identifierType' => new QueryParameter(
            filter: new ExactFilter(),
            property: 'identifiers.type.title',
        ),
        'fieldValueIs' => new QueryParameter(
            filter: new ExactFilter(),
            property: 'fields.value',
            description: 'Filter products by field matching value exactly',
        ),
        'fieldValueLike' => new QueryParameter(
            filter: new PartialSearchFilter(),
            property: 'fields.value',
            description: 'Filter products by field matching value partially',
        ),
        'fieldTypeCode' => new QueryParameter(
            filter: new ExactFilter(),
            property: 'fields.type.code',
        ),
        'fieldTranslatedValueIs' => new QueryParameter(
            filter: new ExactFilter(),
            property: 'fields.translations.value',
            description: 'Filter products by field matching translated value exactly',
        ),
        'fieldTranslatedValueLike' => new QueryParameter(
            filter: new PartialSearchFilter(),
            property: 'fields.translations.value',
            description: 'Filter products by field matching translated value partially',
        ),
        'locale' => new QueryParameter(
            filter: new ExactFilter(),
            property: 'fields.translations.locale.id'
        ),
    ]
)]
#[Post(denormalizationContext: ['groups' => ['product:write']])]
#[Patch(denormalizationContext: ['groups' => ['product:write']])]
#[Delete]
#[ORM\HasLifecycleCallbacks]
class Product extends TimestampableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product:read', 'product:write'])]
    private ProductType $productType;

    /**
     * @var Collection<int, Identifier>
     */
    #[ORM\OneToMany(targetEntity: Identifier::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[Groups(['product:read', 'product:write'])]
    private Collection $identifiers;

    /**
     * @var Collection<int, Field>
     */
    #[ORM\OneToMany(targetEntity: Field::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[Groups(['product:read', 'product:write'])]
    private Collection $fields;

    public function __construct()
    {
        $this->identifiers = new ArrayCollection();
        $this->fields = new ArrayCollection();

        parent::__construct();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getProductType(): ProductType
    {
        return $this->productType;
    }

    public function setProductType(ProductType $productType): static
    {
        $this->productType = $productType;

        return $this;
    }

    /**
     * @return Collection<int, Identifier>
     */
    public function getIdentifiers(): Collection
    {
        return $this->identifiers;
    }

    public function addIdentifier(Identifier $identifier): static
    {
        if (!$this->identifiers->contains($identifier)) {
            $this->identifiers->add($identifier);
            $identifier->setProduct($this);
        }

        return $this;
    }

    public function removeIdentifier(Identifier $identifier): static
    {
        $this->identifiers->removeElement($identifier);

        return $this;
    }

    /**
     * @return Collection<int, Field>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(Field $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setProduct($this);
        }

        return $this;
    }

    public function removeField(Field $field): static
    {
        $this->fields->removeElement($field);

        return $this;
    }
}
