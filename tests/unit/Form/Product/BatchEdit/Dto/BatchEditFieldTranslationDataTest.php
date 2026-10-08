<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Dto;

use App\Entity\Locale\Locale;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldTranslationData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BatchEditFieldTranslationData::class)]
class BatchEditFieldTranslationDataTest extends MockeryTestCase
{
    public function testGetLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $data = new BatchEditFieldTranslationData($locale);

        $this->assertSame(
            $locale,
            $data->getLocale(),
        );
    }

    public function testGetLocaleReturnsNull(): void
    {
        $data = new BatchEditFieldTranslationData(null);

        $this->assertNull($data->getLocale());
    }

    public function testGetValue(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $data = new BatchEditFieldTranslationData(
            $locale,
            'Test value',
        );

        $this->assertSame(
            'Test value',
            $data->getValue(),
        );
    }

    public function testGetValueReturnsNullByDefault(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $data = new BatchEditFieldTranslationData($locale);

        $this->assertNull($data->getValue());
    }
}
