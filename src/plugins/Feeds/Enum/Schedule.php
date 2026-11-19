<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Schedule: string implements TranslatableInterface
{
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';

    public function trans(
        TranslatorInterface $translator,
        ?string $locale = null,
    ): string {
        return $translator->trans(
            'schedule.'.$this->value,
            [],
            'feeds',
            $locale,
        );
    }
}
