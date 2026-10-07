<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Dto;

use App\Entity\Locale\Locale;

class BatchEditFieldTranslationData
{
    public function __construct(
        private ?Locale $locale,
        private ?string $value = null,
    ) {
    }

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
