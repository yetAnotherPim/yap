<?php

declare(strict_types=1);

namespace App\Tests\unit\Localisation\Exception;

use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultLocaleIsNotDefinedException::class)]
class DefaultLocaleIsNotDefinedExceptionTest extends TestCase
{
    public function testInstantiation(): void
    {
        $exception = new DefaultLocaleIsNotDefinedException();

        $this->assertSame('Default locale is not configured!', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
