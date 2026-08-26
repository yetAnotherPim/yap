<?php

declare(strict_types=1);

namespace App\Entity\Translation\Title;

use App\Entity\Locale\Locale;

interface TitleTranslationInterface
{
    public function getLocale(): Locale;

    public function getValue(): ?string;
}
