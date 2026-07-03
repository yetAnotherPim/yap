<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Type\Translation;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Repository\FieldTypeTitleTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FieldTypeTitleTranslationRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'fieldtype_translation_locale_fieldtype', fields: ['locale', 'field_type'])]
class FieldTypeTitleTranslation extends RelatedTimestampableEntity
{
    protected array $updatableRelations = [
        FieldType::class => 'fieldType',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[Groups(['product:read', 'product:write', 'identifier:filter', 'field_type:write', 'field_type:read'])]
    #[ORM\JoinColumn(nullable: false)]
    private Locale $locale;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private FieldType $fieldType;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['product:read', 'product:write', 'identifier:filter', 'field_type:write', 'field_type:read'])]
    private ?string $value = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getFieldType(): FieldType
    {
        return $this->fieldType;
    }

    public function setFieldType(FieldType $fieldType): static
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): FieldTypeTitleTranslation
    {
        $this->value = $value;

        return $this;
    }
}
