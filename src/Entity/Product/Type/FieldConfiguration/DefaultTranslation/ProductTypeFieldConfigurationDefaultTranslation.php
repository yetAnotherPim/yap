<?php

declare(strict_types=1);

namespace App\Entity\Product\Type\FieldConfiguration\DefaultTranslation;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Repository\ProductTypeFieldConfigurationDefaultTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductTypeFieldConfigurationDefaultTranslationRepository::class)]
#[Index(name: 'index_default_translation_locale_id', columns: ['locale_id'])]
#[Index(name: 'index_default_translation_product_type_field_configuration_id', columns: ['product_type_field_configuration_id'])]
#[ApiResource]
class ProductTypeFieldConfigurationDefaultTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Locale $locale;

    #[ORM\ManyToOne(inversedBy: 'productTypeFieldConfigurationDefaultTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ProductTypeFieldConfiguration $productTypeFieldConfiguration;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
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

    public function getProductTypeFieldConfiguration(): ProductTypeFieldConfiguration
    {
        return $this->productTypeFieldConfiguration;
    }

    public function setProductTypeFieldConfiguration(ProductTypeFieldConfiguration $productTypeFieldConfiguration): static
    {
        $this->productTypeFieldConfiguration = $productTypeFieldConfiguration;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): ProductTypeFieldConfigurationDefaultTranslation
    {
        $this->value = $value;

        return $this;
    }
}
