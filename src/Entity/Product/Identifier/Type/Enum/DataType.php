<?php

declare(strict_types=1);

namespace App\Entity\Product\Identifier\Type\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum DataType: string implements TranslatableInterface
{
    case Integer = 'integer';
    case String = 'string';

    public function trans(
        TranslatorInterface $translator,
        ?string $locale = null,
    ): string {
        return $translator->trans(
            'identifier_type.data_type.'.$this->value,
            [],
            'admin',
            $locale,
        );
    }
}
