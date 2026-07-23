<?php

declare(strict_types=1);

namespace App\Entity\Product\Type\FieldConfiguration;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\ProductType;
use App\Repository\ProductTypeFieldConfigurationRepository;
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
    // TODO: avoid duplicates
    // TODO: add default translation

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

    /** @phpstan-var array<mixed>|null  */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private string $defaultValue = '';

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

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(string $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }
}
