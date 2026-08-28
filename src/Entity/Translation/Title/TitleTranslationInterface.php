<?php

declare(strict_types=1);

namespace App\Entity\Translation\Title;

use App\Entity\Locale\Locale;

interface TitleTranslationInterface
{
    public function setLocale(Locale $locale): static;

    public function getLocale(): Locale;

    public function setValue(?string $value): static;

    public function getValue(): ?string;
}
