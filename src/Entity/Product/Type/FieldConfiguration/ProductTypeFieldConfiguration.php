<?php

declare(strict_types=1);

namespace App\Entity\Product\Type\FieldConfiguration;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\ProductType;
use App\Repository\ProductTypeFieldConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductTypeFieldConfigurationRepository::class)]
#[Index(name: 'index_product_type_field_configuration_field_type', columns: ['field_type_id'])]
#[Index(name: 'index_product_type_field_configuration_product_type', columns: ['product_type_id'])]
#[ApiResource]
class ProductTypeFieldConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\ManyToOne(inversedBy: 'productTypeFieldConfigurations')]
    #[ORM\JoinColumn(nullable: false)]
    private ProductType $productType;

    #[ORM\ManyToOne(inversedBy: 'productTypeFieldConfigurations')]
    #[ORM\JoinColumn(nullable: false)]
    // @phpstan-ignore doctrine.associationType
    private ?FieldType $fieldType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $defaultValue = null;

    #[ORM\Column(options: ['default' => 0])]
    private bool $required = false;

    /**
     * @phpstan-var string[]
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $options = [];

    /**
     * @var Collection<int, ProductTypeFieldConfigurationDefaultTranslation>
     */
    #[ORM\OneToMany(targetEntity: ProductTypeFieldConfigurationDefaultTranslation::class, mappedBy: 'productTypeFieldConfiguration', cascade: ['all'], orphanRemoval: true)]
    private Collection $productTypeFieldConfigurationDefaultTranslations;

    public function __construct()
    {
        $this->productTypeFieldConfigurationDefaultTranslations = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getProductType(): ProductType
    {
        return $this->productType;
    }

    public function setProductType(ProductType $productType): static
    {
        $this->productType = $productType;

        return $this;
    }

    public function getFieldType(): ?FieldType
    {
        return $this->fieldType;
    }

    public function setFieldType(FieldType $fieldType): static
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $isRequired): ProductTypeFieldConfiguration
    {
        $this->required = $isRequired;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string[] $options
     */
    public function setOptions(array $options): ProductTypeFieldConfiguration
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Collection<int, ProductTypeFieldConfigurationDefaultTranslation>
     */
    public function getProductTypeFieldConfigurationDefaultTranslations(): Collection
    {
        return $this->productTypeFieldConfigurationDefaultTranslations;
    }

    public function addProductTypeFieldConfigurationDefaultTranslation(ProductTypeFieldConfigurationDefaultTranslation $productTypeFieldConfigurationDefaultTranslation): static
    {
        if (!$this->productTypeFieldConfigurationDefaultTranslations->contains($productTypeFieldConfigurationDefaultTranslation)) {
            $this->productTypeFieldConfigurationDefaultTranslations->add($productTypeFieldConfigurationDefaultTranslation);
            $productTypeFieldConfigurationDefaultTranslation->setProductTypeFieldConfiguration($this);
        }

        return $this;
    }

    public function removeProductTypeFieldConfigurationDefaultTranslation(ProductTypeFieldConfigurationDefaultTranslation $productTypeFieldConfigurationDefaultTranslation): static
    {
        $this->productTypeFieldConfigurationDefaultTranslations->removeElement($productTypeFieldConfigurationDefaultTranslation);

        return $this;
    }
}
