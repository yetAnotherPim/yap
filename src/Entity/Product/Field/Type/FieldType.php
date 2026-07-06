<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Type;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Repository\FieldTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: FieldTypeRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'fieldtype_code_section', fields: ['code', 'section'])]
#[Patch(
    denormalizationContext: ['groups' => ['field_type:write']],
    normalizationContext: ['groups' => ['field_type:read']]
)]
#[Post(denormalizationContext: ['groups' => ['field_type:write']])]
#[Get]
#[GetCollection]
#[Delete]
class FieldType extends TimestampableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\Column(length: 255, nullable: false)]
    #[NotBlank]
    #[Groups(['product:read', 'field:read', 'identifier:filter', 'field_type:write', 'field_type:read'])]
    private string $code;

    #[ORM\Column(length: 255, nullable: false)]
    #[NotBlank]
    #[Groups(['product:read', 'field:read', 'identifier:filter', 'field_type:write', 'field_type:read'])]
    private string $title;

    #[ORM\Column]
    #[Groups(['field_type:write', 'field_type:read'])]
    private bool $translatable;

    #[ORM\Column]
    #[Groups(['field_type:write', 'field_type:read'])]
    private bool $filterable;

    #[ORM\Column]
    #[Groups(['field_type:write', 'field_type:read'])]
    private Enum\InputType $inputType;

    #[ORM\ManyToOne(inversedBy: 'fieldTypes')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['field_type:write'])]
    private ?FieldSection $section;

    /**
     * @var Collection<int, FieldTypeTitleTranslation>
     */
    #[ORM\OneToMany(targetEntity: FieldTypeTitleTranslation::class, mappedBy: 'fieldType', cascade: ['all'], orphanRemoval: true)]
    #[Groups(['field_type:write', 'field_type:read'])]
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): FieldType
    {
        $this->code = $code;
        return $this;
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

    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    public function setTranslatable(bool $translatable): static
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable): static
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function getInputType(): Enum\InputType
    {
        return $this->inputType;
    }

    public function setInputType(Enum\InputType $inputType): void
    {
        $this->inputType = $inputType;
    }

    public function getSection(): ?FieldSection
    {
        return $this->section;
    }

    public function setSection(?FieldSection $section): static
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return Collection<int, FieldTypeTitleTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(FieldTypeTitleTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setFieldType($this);
        }

        return $this;
    }

    public function removeTranslation(FieldTypeTitleTranslation $translation): static
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
