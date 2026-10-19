<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductField
{
    /** @var Collection<int, FieldTranslation> */
    #[Assert\Valid]
    private Collection $translations;

    public function __construct(
        #[Assert\Valid]
        private FieldTypeIdentifier $identifier,
        private ?string $value = null,
    ) {
        $this->translations = new ArrayCollection();
    }

    public function getIdentifier(): FieldTypeIdentifier
    {
        return $this->identifier;
    }

    public function setIdentifier(FieldTypeIdentifier $identifier): self
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
     * @return Collection<int, FieldTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param Collection<int, FieldTranslation> $translations
     */
    public function setTranslations(Collection $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    public function addTranslation(FieldTranslation $translation): self
    {
        $this->translations->add($translation);

        return $this;
    }

    public function removeTranslation(FieldTranslation $translation): self
    {
        $this->translations->removeElement($translation);

        return $this;
    }
}
