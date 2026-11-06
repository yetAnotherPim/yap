<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Synchronizer;

use App\DemoData\Synchronizer\TitleTranslationSynchronizer;
use App\Entity\Locale\Locale;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TitleTranslationSynchronizer::class)]
class TitleTranslationSynchronizerTest extends MockeryTestCase
{
    public function testSynchronizeCreatesAndAddsMissingTranslations(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(TitleTranslationInterface::class);
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $entity
            ->shouldReceive('getTitleTranslations')
            ->once()
            ->andReturn(new ArrayCollection());

        $entity
            ->shouldReceive('createTitleTranslation')
            ->once()
            ->andReturn($translation);

        $translation
            ->shouldReceive('setLocale')
            ->once()
            ->with($locale);

        $entity
            ->shouldReceive('addTitleTranslation')
            ->once()
            ->with($translation);

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with('Product');

        $synchronizer = new TitleTranslationSynchronizer();

        $synchronizer->synchronize(
            $entity,
            ['en_GB' => 'Product'],
            ['en_GB' => $locale],
        );
    }

    public function testSynchronizeUpdatesExistingTranslation(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(TitleTranslationInterface::class);
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $locale->shouldReceive('getId')->once()->andReturn('en_GB');

        $translation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($locale);

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with('Updated product');

        $entity
            ->shouldReceive('getTitleTranslations')
            ->once()
            ->andReturn(new ArrayCollection([$translation]));

        $entity->shouldNotReceive('createTitleTranslation');
        $entity->shouldNotReceive('addTitleTranslation');

        $synchronizer = new TitleTranslationSynchronizer();

        $synchronizer->synchronize(
            $entity,
            ['en_GB' => 'Updated product'],
            ['en_GB' => $locale],
        );
    }

    public function testSynchronizeIgnoresUnknownLocales(): void
    {
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $entity->shouldNotReceive('getTitleTranslations');
        $entity->shouldNotReceive('createTitleTranslation');
        $entity->shouldNotReceive('addTitleTranslation');

        $synchronizer = new TitleTranslationSynchronizer();

        $synchronizer->synchronize(
            $entity,
            ['de_DE' => 'Produkt'],
            [],
        );
    }

    public function testSynchronizeProcessesOnlyLocalesThatExist(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(TitleTranslationInterface::class);
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $entity
            ->shouldReceive('getTitleTranslations')
            ->once()
            ->andReturn(new ArrayCollection());

        $entity
            ->shouldReceive('createTitleTranslation')
            ->once()
            ->andReturn($translation);

        $translation
            ->shouldReceive('setLocale')
            ->once()
            ->with($locale);

        $entity
            ->shouldReceive('addTitleTranslation')
            ->once()
            ->with($translation);

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with('Product');

        $synchronizer = new TitleTranslationSynchronizer();

        $synchronizer->synchronize(
            $entity,
            [
                'en_GB' => 'Product',
                'de_DE' => 'Produkt',
            ],
            ['en_GB' => $locale],
        );
    }
}
