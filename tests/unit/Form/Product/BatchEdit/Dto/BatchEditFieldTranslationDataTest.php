<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Dto;

use App\Entity\Locale\Locale;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldTranslationData;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(BatchEditFieldTranslationData::class)]
class BatchEditFieldTranslationDataTest extends MockeryTestCase
{
    use SetterGetterTrait;

    public function testDefaultValues(): void
    {
        $data = new BatchEditFieldTranslationData();

        $this->assertNull($data->getLocale());
        $this->assertNull($data->getValue());
    }

    /**
     * @param ?class-string $expectedException
     */
    #[DataProvider('setterGetterProvider')]
    public function testSetterGetter(
        string $setter,
        string $getter,
        mixed $value,
        ?string $expectedException = null,
    ): void {
        $this->performSetterGetterTesting($setter, $getter, $value, $expectedException);
    }

    protected function getTargetClass(): string
    {
        return BatchEditFieldTranslationData::class;
    }

    /**
     * @return array<array{0: string, 1: string, 2: mixed, 3?: class-string}>
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setLocale', 'getLocale', new Locale()],
            ['setLocale', 'getLocale', null],
            ['setValue', 'getValue', 'Translated value'],
            ['setValue', 'getValue', null],
        ];
    }
}
