<?php

declare(strict_types=1);

namespace App\Controller\Provider\Dto;

final readonly class DashboardStatistic
{
    public function __construct(
        public int $value,
        public ?string $color = null,
    ) {
    }
}
