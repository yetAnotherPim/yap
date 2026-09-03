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
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TitleTranslationTrait;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Repository\FieldTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @implements TranslatableTitleInterface<FieldTypeTitleTranslation>
 */
#[ORM\Entity(repositoryClass: FieldTypeRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'unique_product_field_type_code', fields: ['code'])]
#[Patch(
    denormalizationContext: ['groups' => ['field_type:write']],
    normalizationContext: ['groups' => ['field_type:read']]
)]
#[Post(denormalizationContext: ['groups' => ['field_type:write']])]
#[Get]
#[GetCollection]
#[Delete]
class FieldType extends TimestampableEntity implements TranslatableTitleInterface
{
    use TitleTranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\Column(length: 255, nullable: false)]
    #[NotBlank]
    #[Groups(['product:read', 'product:read:without-translations', 'field:read', 'field_type:write', 'field_type:read'])]
    private string $code;

    #[ORM\Column(length: 255, nullable: false)]
    #[NotBlank]
    #[Groups(['product:read', 'product:read:without-translations', 'field:read', 'field_type:write', 'field_type:read'])]
    private string $title;

    #[ORM\Column]
    #[Groups(['field_type:write', 'field_type:read'])]
    private bool $filterable;

    #[ORM\Column]
    #[Groups(['field_type:write', 'field_type:read'])]
    private Enum\InputType $inputType;

    #[ORM\Column(nullable: true)]
    #[Groups(['field_type:write', 'field_type:read'])]
    private ?string $decimalSeparator = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Groups(['field_type:write', 'field_type:read'])]
    private bool $translatable;

    /**
     * @var Collection<int, FieldTypeTitleTranslation>
     */
    #[ORM\OneToMany(targetEntity: FieldTypeTitleTranslation::class, mappedBy: 'fieldType', cascade: ['all'], orphanRemoval: true)]
    #[Groups(['field_type:write', 'field_type:read'])]
    private Collection $titleTranslations;

    /**
     * @var Collection<int, ProductTypeFieldConfiguration>
     */
    #[ORM\OneToMany(targetEntity: ProductTypeFieldConfiguration::class, mappedBy: 'fieldType', orphanRemoval: true)]
    private Collection $productTypeFieldConfigurations;

    public function __construct()
    {
        parent::__construct();
        $this->titleTranslations = new ArrayCollection();
        $this->productTypeFieldConfigurations = new ArrayCollection();
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

    public function getDecimalSeparator(): ?string
    {
        return $this->decimalSeparator;
    }

    public function setDecimalSeparator(?string $decimalSeparator): FieldType
    {
        $this->decimalSeparator = $decimalSeparator;

        return $this;
    }

    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    public function setTranslatable(bool $translatable): FieldType
    {
        $this->translatable = $translatable;

        return $this;
    }

    /**
     * @return Collection<int, FieldTypeTitleTranslation>
     */
    public function getTitleTranslations(): Collection
    {
        return $this->titleTranslations;
    }

    /**
     * @param FieldTypeTitleTranslation $titleTranslation
     */
    public function addTitleTranslation(TitleTranslationInterface $titleTranslation): static
    {
        if (!$this->titleTranslations->contains($titleTranslation)) {
            $this->titleTranslations->add($titleTranslation);
            $titleTranslation->setFieldType($this);
        }

        return $this;
    }

    /**
     * @param FieldTypeTitleTranslation $titleTranslation
     */
    public function removeTitleTranslation(TitleTranslationInterface $titleTranslation): static
    {
        $this->titleTranslations->removeElement($titleTranslation);

        return $this;
    }

    /**
     * @return Collection<int, ProductTypeFieldConfiguration>
     */
    public function getProductTypeFieldConfigurations(): Collection
    {
        return $this->productTypeFieldConfigurations;
    }

    public function addProductTypeFieldConfiguration(ProductTypeFieldConfiguration $productTypeFieldConfiguration): static
    {
        if (!$this->productTypeFieldConfigurations->contains($productTypeFieldConfiguration)) {
            $this->productTypeFieldConfigurations->add($productTypeFieldConfiguration);
            $productTypeFieldConfiguration->setFieldType($this);
        }

        return $this;
    }

    public function removeProductTypeFieldConfiguration(ProductTypeFieldConfiguration $productTypeFieldConfiguration): static
    {
        $this->productTypeFieldConfigurations->removeElement($productTypeFieldConfiguration);

        return $this;
    }

    public function createTitleTranslation(): TitleTranslationInterface
    {
        return new FieldTypeTitleTranslation();
    }
}
