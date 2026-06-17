<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Type;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Repository\FieldTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FieldTypeRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class FieldType extends TimestampableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\Column]
    private bool $translatable;

    #[ORM\Column]
    private bool $filterable;

    #[ORM\Column]
    private Enum\InputType $inputType;

    #[ORM\ManyToOne(inversedBy: 'fieldTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private FieldSection $section;

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getSection(): FieldSection
    {
        return $this->section;
    }

    public function setSection(FieldSection $section): static
    {
        $this->section = $section;

        return $this;
    }
}
