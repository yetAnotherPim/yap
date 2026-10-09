<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProductTranslation
{
    public function __construct(
        #[Assert\NotBlank]
        private string $localeId,
        private ?string $value = null,
    ) {
    }

    public function getLocaleId(): string
    {
        return $this->localeId;
    }

    public function setLocaleId(string $localeId): self
    {
        $this->localeId = $localeId;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
