<?php

declare(strict_types=1);

namespace App\Entity\User\Configuration;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
#[ORM\UniqueConstraint(
    name: 'unique_product_column_configuration_user_field_type',
    fields: ['user', 'fieldType'],
)]
#[ORM\Index(name: 'index_product_column_configuration_user', fields: ['user'])]
#[ORM\Index(name: 'index_product_column_configuration_field_type', fields: ['fieldType'])]
class ProductColumnConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private FieldType $fieldType;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): ProductColumnConfiguration
    {
        $this->user = $user;
        return $this;
    }

    public function getFieldType(): FieldType
    {
        return $this->fieldType;
    }

    public function setFieldType(FieldType $fieldType): ProductColumnConfiguration
    {
        $this->fieldType = $fieldType;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): ProductColumnConfiguration
    {
        $this->position = $position;
        return $this;
    }
}
