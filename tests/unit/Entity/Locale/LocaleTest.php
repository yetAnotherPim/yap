<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Locale;

use App\Entity\Locale\Locale;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Locale::class)]
class LocaleTest extends MockeryTestCase
{
    public function testGetId(): void
    {
        $entity = new Locale()
            ->setId('de_DE');

        $this->assertEquals('de_DE', $entity->getId());
    }

    protected function getTargetClass(): string
    {
        return Locale::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setDefault', 'isDefault', true],
            ['setDefault', 'isDefault', null, \TypeError::class],
        ];
    }
}
