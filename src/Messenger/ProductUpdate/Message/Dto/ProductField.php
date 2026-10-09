<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductField
{
    /** @var Collection<int, ProductTranslation> */
    #[Assert\Valid]
    private Collection $translations;

    public function __construct(
        #[Assert\Valid]
        private FieldIdentifier $identifier,
        private ?string $value = null,
    ) {
        $this->translations = new ArrayCollection();
    }

    public function getIdentifier(): FieldIdentifier
    {
        return $this->identifier;
    }

    public function setIdentifier(FieldIdentifier $identifier): self
    {
        $this->identifier = $identifier;

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

    /**
     * @return Collection<int, ProductTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param Collection<int, ProductTranslation> $translations
     */
    public function setTranslations(Collection $translations): self
    {
        $this->translations = $translations;

        return $this;
    }
}
