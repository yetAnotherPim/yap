<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Locale;

use App\Entity\Locale\Locale;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Locale::class)]
class LocaleTest extends MockeryTestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new Locale()
            ->setId('de_DE');

        $this->assertEquals('de_DE', $entity->getId());
    }

    /**
     * @param ?class-string $expectedException
     */
    #[DataProvider('setterGetterProvider')]
    public function testSetterGetter(string $setter, string $getter, mixed $value, ?string $expectedException = null): void
    {
        $this->performSetterGetterTesting($setter, $getter, $value, $expectedException);
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
            ['setContentTranslationEnabled', 'isContentTranslationEnabled', true],
            ['setContentTranslationEnabled', 'isContentTranslationEnabled', null, \TypeError::class],
        ];
    }
}
