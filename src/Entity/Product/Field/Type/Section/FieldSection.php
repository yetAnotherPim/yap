<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Type\Section;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Type\FieldType;
use App\Repository\FieldSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FieldSectionRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class FieldSection extends TimestampableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $title;

    /**
     * @var Collection<int, FieldType>
     */
    #[ORM\OneToMany(targetEntity: FieldType::class, mappedBy: 'section', orphanRemoval: false)]
    private Collection $fieldTypes;

    public function __construct()
    {
        $this->fieldTypes = new ArrayCollection();

        parent::__construct();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

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
        if ($this->fieldTypes->contains($fieldType)) {
            $this->fieldTypes->removeElement($fieldType);
            $fieldType->setSection(null);
        }

        return $this;
    }

    // TODO: remove translations on locale deletion -> api-test + integration test
}
