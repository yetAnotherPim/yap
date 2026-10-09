<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Dto;

use App\Entity\Product\Field\Type\FieldType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BatchEditFieldData
{
    /** @var Collection<int, BatchEditFieldTranslationData> */
    private Collection $translations;

    private ?string $value = null;

    public function __construct(
        private ?FieldType $fieldType,
    ) {
        $this->translations = new ArrayCollection();
    }

    public function getFieldType(): ?FieldType
    {
        return $this->fieldType;
    }

    public function setValue(?string $value): BatchEditFieldData
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return Collection<int, BatchEditFieldTranslationData>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(
        BatchEditFieldTranslationData $translation,
    ): void {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }
    }

    public function removeTranslation(
        BatchEditFieldTranslationData $translation,
    ): void {
        $this->translations->removeElement($translation);
    }
}
