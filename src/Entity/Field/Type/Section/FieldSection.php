<?php

declare(strict_types=1);

namespace App\Entity\Field\Type\Section;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Field\Type\FieldType;
use App\Repository\FieldSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FieldSectionRepository::class)]
#[ApiResource]
class FieldSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $title;

    /**
     * @var Collection<int, FieldType>
     */
    #[ORM\OneToMany(targetEntity: FieldType::class, mappedBy: 'section', orphanRemoval: true)]
    private Collection $fieldTypes;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->fieldTypes = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    // TODO: add translation
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, FieldType>
     */
    public function getFieldTypes(): Collection
    {
        return $this->fieldTypes;
    }

    public function addFieldType(FieldType $fieldType): static
    {
        if (!$this->fieldTypes->contains($fieldType)) {
            $this->fieldTypes->add($fieldType);
            $fieldType->setSection($this);
        }

        return $this;
    }

    public function removeFieldType(FieldType $fieldType): static
    {
        $this->fieldTypes->removeElement($fieldType);

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
