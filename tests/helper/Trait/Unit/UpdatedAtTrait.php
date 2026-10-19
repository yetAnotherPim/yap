<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait\Unit;

use App\Entity\Abstract\TimestampableEntity;
use PHPUnit\Framework\Assert;

trait UpdatedAtTrait
{
    public function testUpdateUpdatedAt(): void
    {
        $targetClass = $this->getTargetClass();
        /** @phpstan-var TimestampableEntity $target */
        $target = new $targetClass();

        Assert::assertNull($target->getUpdatedAt());

        $target->updateUpdatedAt();

        Assert::assertNotNull($target->getUpdatedAt());
    }

    abstract protected function getTargetClass(): string;
}
