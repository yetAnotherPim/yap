<?php

declare(strict_types=1);

namespace App\Entity\Abstract;

use Doctrine\ORM\Mapping as ORM;

abstract class RelatedTimestampableEntity extends TimestampableEntity
{
    /** @var array<class-string, string> */
    protected array $updatableRelations = [
    ];
    // TODO: handle remove
    // TODO: handle set via POST

    #[ORM\PreUpdate]
    public function updateRelated(): void
    {
        foreach ($this->updatableRelations as $type => $relation) {
            $getter = 'get'.ucfirst($relation);
            /** @phpstan-var TimestampableEntity $target */
            $target = $this->$getter();

            if (!$target instanceof $type) {
                return;
            }

            $target->setUpdatedAt(new \DateTimeImmutable());
        }
    }
}
