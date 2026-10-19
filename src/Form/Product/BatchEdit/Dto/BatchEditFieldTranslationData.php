<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Dto;

use App\Entity\Locale\Locale;

class BatchEditFieldTranslationData
{
    private ?Locale $locale = null;
    private ?string $value = null;

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function setLocale(?Locale $locale): BatchEditFieldTranslationData
    {
        $this->locale = $locale;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): BatchEditFieldTranslationData
    {
        $this->value = $value;

        return $this;
    }
}
