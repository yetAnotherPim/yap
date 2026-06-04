<?php

declare(strict_types=1);

namespace App\Entity\Identifier\Type;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Identifier\Type\Enum\DataType;
use App\Repository\IdentifierTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: IdentifierTypeRepository::class)]
#[ORM\UniqueConstraint(name: 'identifier_type_title', columns: ['title'])]
#[ApiResource]
class IdentifierType
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 255, unique: true)]
    private string $title;

    #[ORM\Column(enumType: DataType::class)]
    private DataType $dataType = DataType::String;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDataType(): ?DataType
    {
        return $this->dataType;
    }

    public function setDataType(DataType $dataType): static
    {
        $this->dataType = $dataType;

        return $this;
    }
}
