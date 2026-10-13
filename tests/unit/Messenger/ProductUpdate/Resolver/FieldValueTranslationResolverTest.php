<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate\Resolver;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Messenger\ProductUpdate\Resolver\FieldValueTranslationResolver;
use App\Repository\LocaleRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FieldValueTranslationResolver::class)]
#[UsesClass(FieldValueTranslation::class)]
class FieldValueTranslationResolverTest extends MockeryTestCase
{
    public function testResolveReturnsNullWhenFieldIsNotTranslatable(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType->shouldReceive('isTranslatable')
            ->once()
            ->andReturnFalse();

        $field = \Mockery::mock(Field::class);
        $field->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field->shouldNotReceive('getTranslationByLocale');

        $localeRepository = \Mockery::mock(LocaleRepository::class);
        $localeRepository->shouldNotReceive('getContentTranslationLocaleById');

        $resolver = new FieldValueTranslationResolver($localeRepository);

        $this->assertNull($resolver->resolve($field, 'de_DE'));
    }

    public function testResolveReturnsExistingTranslation(): void
    {
        $translation = \Mockery::mock(FieldValueTranslation::class);

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $field = \Mockery::mock(Field::class);
        $field->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('de_DE')
            ->andReturn($translation);
        $field->shouldNotReceive('addTranslation');

        $localeRepository = \Mockery::mock(LocaleRepository::class);
        $localeRepository->shouldNotReceive('getContentTranslationLocaleById');

        $resolver = new FieldValueTranslationResolver($localeRepository);

        $this->assertSame($translation, $resolver->resolve($field, 'de_DE'));
    }

    public function testResolveReturnsNullWhenLocaleDoesNotExist(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $field = \Mockery::mock(Field::class);
        $field->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('de_DE')
            ->andReturnNull();
        $field->shouldNotReceive('addTranslation');

        $localeRepository = \Mockery::mock(LocaleRepository::class);
        $localeRepository->shouldReceive('getContentTranslationLocaleById')
            ->once()
            ->with('de_DE')
            ->andReturnNull();

        $resolver = new FieldValueTranslationResolver($localeRepository);

        $this->assertNull($resolver->resolve($field, 'de_DE'));
    }

    public function testResolveCreatesTranslationWhenLocaleExists(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType->shouldReceive('isTranslatable')
            ->once()
            ->andReturnTrue();

        $field = \Mockery::mock(Field::class);
        $field->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('de_DE')
            ->andReturnNull();
        $field->shouldReceive('addTranslation')
            ->once()
            ->with(\Mockery::on(
                static fn (FieldValueTranslation $translation): bool => $translation->getLocale() === $locale,
            ));

        $localeRepository = \Mockery::mock(LocaleRepository::class);
        $localeRepository->shouldReceive('getContentTranslationLocaleById')
            ->once()
            ->with('de_DE')
            ->andReturn($locale);

        $resolver = new FieldValueTranslationResolver($localeRepository);

        $result = $resolver->resolve($field, 'de_DE');

        $this->assertInstanceOf(FieldValueTranslation::class, $result);
        $this->assertSame($locale, $result->getLocale());
    }
}
