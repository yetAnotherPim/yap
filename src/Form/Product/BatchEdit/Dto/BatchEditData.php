<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Dto;

use App\Entity\Product\Field\Type\FieldType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BatchEditData
{
    /** @var Collection<string, BatchEditFieldData> */
    private Collection $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * @return Collection<string, BatchEditFieldData>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(BatchEditFieldData $field): void
    {
        $fieldType = $field->getFieldType();

        if (null === $fieldType || $this->hasField($fieldType)) {
            return;
        }

        $this->fields->set($fieldType->getCode(), $field);
    }

    public function removeField(BatchEditFieldData $field): void
    {
        $fieldType = $field->getFieldType();

        if (null === $fieldType) {
            return;
        }

        $this->fields->remove($fieldType->getCode());
    }

    public function hasField(FieldType $fieldType): bool
    {
        return $this->fields->containsKey($fieldType->getCode());
    }
}
