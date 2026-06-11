<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\Abstract\AbstractEntity;
use App\Entity\Identifier\Identifier;
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
        'hasIdentifiers' => new QueryParameter(
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
class Product extends AbstractEntity
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

    // TODO: test section or fieldType deleted, but field assigned to product -> what happens?

    public function __construct()
    {
        $this->identifiers = new ArrayCollection();

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
}
