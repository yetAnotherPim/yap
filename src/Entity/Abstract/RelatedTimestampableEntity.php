<?php

declare(strict_types=1);

namespace App\Entity\Abstract;

abstract class RelatedTimestampableEntity extends TimestampableEntity
{
    /** @var array<class-string, string> */
    protected array $updatableRelations = [
    ];

    /**
     * @return array<class-string, string>
     */
    public function getUpdatableRelations(): array
    {
        return $this->updatableRelations;
    }
}
