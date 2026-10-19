<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Translation;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Repository\FieldValueTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FieldValueTranslationRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'unique_product_field_translation_locale_field', fields: ['locale', 'field'])]
#[Index(name: 'index_product_field_translation_value', fields: ['value'])]
#[Index(name: 'index_product_field_translation_locale', columns: ['locale_id'])]
#[Index(name: 'index_product_field_translation_field', columns: ['field_id'])]
class FieldValueTranslation extends RelatedTimestampableEntity
{
    protected array $updatableRelations = [
        Field::class => 'field',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[Groups(['product:read', 'product:write', 'field:write', 'field:read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Locale $locale;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private Field $field;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['product:read', 'product:write', 'field:write', 'field:read'])]
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

    public function getField(): Field
    {
        return $this->field;
    }

    public function setField(Field $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): FieldValueTranslation
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

        return InputType::getTypedValue($this->value, $this->getField()->getType()->getInputType());
    }
}
