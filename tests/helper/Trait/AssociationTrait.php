<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Assert;

trait AssociationTrait
{
    /**
     * @phpstan-param class-string $targetValue
     */
    protected function performOneToManyAssociationTesting(string $getter, string $adder, string $remover, string $targetValue, ?string $inverseSetter = null): void
    {
        $targetClass = $this->getTargetClass();
        $target = new $targetClass();

        Assert::assertEmpty($target->$getter());

        $value = \Mockery::mock($targetValue);
        $value2 = \Mockery::mock($targetValue);

        if (null !== $inverseSetter) {
            $value
                ->shouldReceive($inverseSetter)
                ->andReturn($value);

            $value2
                ->shouldReceive($inverseSetter)
                ->andReturn($value);
        }

        $target->$adder($value);

        /** @phpstan-var Collection<int,object> $values */
        $values = $target->$getter();
        Assert::assertNotEmpty($target->$getter());
        Assert::assertSame($values->first(), $value);
        Assert::assertCount(1, $values);

        $target->$adder($value2);

        /** @phpstan-var Collection<int,object> $values */
        $values = $target->$getter();
        Assert::assertNotEmpty($target->$getter());
        Assert::assertSame($values->first(), $value);
        Assert::assertSame($values->last(), $value2);
        Assert::assertCount(2, $values);

        $target->$remover($value);

        /** @phpstan-var Collection<int,object> $values */
        $values = $target->$getter();
        Assert::assertNotEmpty($target->$getter());
        Assert::assertSame($values->first(), $value2);
        Assert::assertCount(1, $values);
    }

    abstract protected function getTargetClass(): string;
}
