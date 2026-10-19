<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Dto;

use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use Symfony\Component\Validator\Constraints as Assert;

class FieldTypeIdentifier
{
    public function __construct(
        private FieldTypeIdentifierType $type,
        #[Assert\NotBlank]
        private string $value,
    ) {
    }

    public function getType(): FieldTypeIdentifierType
    {
        return $this->type;
    }

    public function setType(FieldTypeIdentifierType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
