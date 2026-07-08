<?php

declare(strict_types=1);

namespace App\Entity\Product\Type;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use App\Repository\ProductTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'index_product_type_code', fields: ['code'])]
#[ORM\Entity(repositoryClass: ProductTypeRepository::class)]
#[Patch(
    denormalizationContext: ['groups' => ['product_type:write']],
    normalizationContext: ['groups' => ['product_type:read']]
)]
#[Post(denormalizationContext: ['groups' => ['product_type:write']])]
#[Get]
#[GetCollection]
#[Delete]
class ProductType extends TimestampableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\Column(length: 255, nullable: false)]
    #[NotBlank]
    #[Groups(['product:read', 'product_type:write', 'product_type:read'])]

    private string $code;

    #[ORM\Column(length: 255, nullable: false)]
    #[NotBlank]
    #[Groups(['product_type:write', 'product_type:read'])]
    private string $title;

    /**
     * @var Collection<int, ProductTypeTitleTranslation>
     */
    #[ORM\OneToMany(targetEntity: ProductTypeTitleTranslation::class, mappedBy: 'productType', cascade: ['all'], orphanRemoval: true)]
    #[Groups(['product_type:write', 'product_type:read'])]
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

    public function setCode(string $code): ProductType
    {
        $this->code = $code;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): ProductType
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, ProductTypeTitleTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProductTypeTitleTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setProductType($this);
        }

        return $this;
    }

    public function removeTranslation(ProductTypeTitleTranslation $translation): static
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
