<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Doctrine\Orm\Filter\ComparisonFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Identifier\Identifier;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

// TODO: add parent relation

#[ORM\Entity(repositoryClass: ProductRepository::class)]
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
    ]
)]
#[GetCollection(
    uriTemplate: 'filter/hasIdentifier',
    openapi: new Operation(tags: ['filter'], summary: 'Filter products that have/don\'t have identifiers'),
    normalizationContext: ['groups' => ['product:read']],
    parameters: [
        'value' => new QueryParameter(
            filter: new ExistsFilter(),
            property: 'identifiers',
            description: 'Filter products that have/don\'t have identifiers',
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

    // TODO: test section or fieldType deleted, but field assigned to product -> what happens?
    // TODO: test orphan removal via api deletion as in integration test

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
