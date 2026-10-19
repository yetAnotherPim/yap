<?php

declare(strict_types=1);

namespace App\Entity\Abstract;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\UpdatedAtTrait;

class TimestampableEntity
{
    use CreatedAtTrait;
    use UpdatedAtTrait;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
