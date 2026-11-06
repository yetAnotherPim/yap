<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Provider;

use App\DemoData\Definition\LocaleDefinitions;
use App\DemoData\Provider\LocaleProvider;
use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(LocaleProvider::class)]
#[UsesClass(Locale::class)]
class LocaleProviderTest extends MockeryTestCase
{
    public function testGetLocalesReturnsRequiredLocales(): void
    {
        $repository = \Mockery::mock(LocaleRepository::class);
        $locales = [];

        foreach (LocaleDefinitions::REQUIRED_LOCALES as $localeCode) {
            $locale = new Locale()->setId($localeCode);
            $locales[$localeCode] = $locale;

            $repository
                ->shouldReceive('find')
                ->once()
                ->with($localeCode)
                ->andReturn($locale);
        }

        $provider = new LocaleProvider($repository);

        $this->assertSame($locales, $provider->getLocales());
    }

    public function testGetLocalesEnablesContentTranslationsForConfiguredLocales(): void
    {
        $repository = \Mockery::mock(LocaleRepository::class);
        $locales = [];

        foreach (LocaleDefinitions::REQUIRED_LOCALES as $localeCode) {
            $locale = \Mockery::mock(Locale::class);

            $locale->shouldReceive('getId')
                ->andReturn($localeCode);

            if (\in_array($localeCode, LocaleDefinitions::ENABLE_TRANSLATIONS, true)) {
                $locale->shouldReceive('isContentTranslationEnabled')
                    ->once()
                    ->andReturn(false);

                $locale->shouldReceive('setContentTranslationEnabled')
                    ->once()
                    ->with(true);
            }

            $locales[$localeCode] = $locale;

            $repository
                ->shouldReceive('find')
                ->once()
                ->with($localeCode)
                ->andReturn($locale);
        }

        $provider = new LocaleProvider($repository);

        $result = $provider->getLocales();

        $this->assertSame($locales, $result);
    }

    public function testGetLocalesDoesNotDisableAlreadyEnabledTranslations(): void
    {
        $repository = \Mockery::mock(LocaleRepository::class);
        $locales = [];

        foreach (LocaleDefinitions::REQUIRED_LOCALES as $localeCode) {
            $locale = new Locale()
                ->setId($localeCode)
                ->setContentTranslationEnabled(true);

            $locales[$localeCode] = $locale;

            $repository
                ->shouldReceive('find')
                ->once()
                ->with($localeCode)
                ->andReturn($locale);
        }

        $provider = new LocaleProvider($repository);

        $provider->getLocales();

        foreach ($locales as $locale) {
            $this->assertTrue($locale->isContentTranslationEnabled());
        }
    }

    public function testGetLocalesThrowsExceptionWhenRequiredLocaleDoesNotExist(): void
    {
        $repository = \Mockery::mock(LocaleRepository::class);

        $missingLocale = LocaleDefinitions::REQUIRED_LOCALES[0];

        $repository
            ->shouldReceive('find')
            ->once()
            ->with($missingLocale)
            ->andReturnNull();

        $provider = new LocaleProvider($repository);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Required locale "%s" does not exist. Run the migrations first.',
                $missingLocale,
            ),
        );

        $provider->getLocales();
    }
}
