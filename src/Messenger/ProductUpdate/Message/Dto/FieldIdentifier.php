<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Dto;

use App\Messenger\ProductUpdate\Message\Enum\ProductFieldIdentifierType;
use Symfony\Component\Validator\Constraints as Assert;

class FieldIdentifier
{
    public function __construct(
        private ProductFieldIdentifierType $type,
        #[Assert\NotBlank]
        private string $value,
    ) {
    }

    public function getType(): ProductFieldIdentifierType
    {
        return $this->type;
    }

    public function setType(ProductFieldIdentifierType $type): self
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
