<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Resolver;

use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Form\Product\Fields\Resolver\FieldValueTranslationResolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FieldValueTranslationResolver::class)]
#[UsesClass(FieldValueTranslation::class)]
#[UsesClass(TimestampableEntity::class)]
class FieldValueTranslationResolverTest extends MockeryTestCase
{
    public function testResolveReturnsExistingTranslation(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $translation = \Mockery::mock(FieldValueTranslation::class);

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('de_DE')
            ->andReturn($translation);

        $resolver = new FieldValueTranslationResolver();

        $result = $resolver->resolve($field, $locale);

        $this->assertSame($translation, $result);
    }

    public function testResolveCreatesTranslationWhenNoneExists(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('de_DE')
            ->andReturn(null);

        $resolver = new FieldValueTranslationResolver();

        $result = $resolver->resolve(
            $field,
            $locale,
            'Standardwert',
        );

        $this->assertSame($field, $result->getField());
        $this->assertSame($locale, $result->getLocale());
        $this->assertSame('Standardwert', $result->getValue());
    }

    public function testResolveCreatesTranslationWithNullDefaultValue(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('fr_FR');

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('fr_FR')
            ->andReturn(null);

        $resolver = new FieldValueTranslationResolver();

        $result = $resolver->resolve($field, $locale);

        $this->assertSame($field, $result->getField());
        $this->assertSame($locale, $result->getLocale());
        $this->assertNull($result->getValue());
    }
}
