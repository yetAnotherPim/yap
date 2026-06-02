<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait;

use PHPUnit\Framework\Assert;

trait SetterGetterTrait
{
    /**
     * @phpstan-param null|class-string $expectedException
     */
    protected function performSetterGetterTesting(string $setter, string $getter, mixed $value, ?string $expectedException = null): void
    {
        $targetClass = $this->getTargetClass();
        $target = new $targetClass();

        if (null === $expectedException) {
            $target->$setter($value);
            Assert::assertEquals($value, $target->$getter());

            return;
        }

        try {
            $target->$setter($value);
        } catch (\Throwable $exception) {
            Assert::assertInstanceOf($expectedException, $exception);
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function setByReflection(object $entity, string $property, mixed $value): void
    {
        $class = new \ReflectionClass($entity);
        $property = $class->getProperty($property);
        $property->setValue($entity, $value);
    }

    abstract protected function getTargetClass(): string;
}
