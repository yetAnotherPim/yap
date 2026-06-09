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
use App\Entity\Identifier\Identifier;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

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
#[Post]
#[Patch]
#[Delete]
class Product
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
    #[ORM\OneToMany(targetEntity: Identifier::class, mappedBy: 'product', orphanRemoval: true)]
    #[Groups(['product:read'])]
    private Collection $identifiers;

    #[ORM\Column]
    #[Groups(['product:read', 'identifier:filter'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read', 'identifier:filter'])]
    private ?\DateTimeImmutable $updatedAt = null;

    // TODO: set created at and updted at -> integration test

    public function __construct()
    {
        $this->identifiers = new ArrayCollection();
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
