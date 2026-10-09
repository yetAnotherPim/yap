<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate\Message\Dto;

use App\Messenger\ProductUpdate\Message\Dto\FieldIdentifier;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\Dto\ProductTranslation;
use App\Messenger\ProductUpdate\Message\Enum\ProductFieldIdentifierType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Validator\Validation;

#[CoversClass(ProductField::class)]
#[UsesClass(FieldIdentifier::class)]
#[UsesClass(ProductTranslation::class)]
class ProductFieldTest extends MockeryTestCase
{
    private const string ID = '550e8400-e29b-41d4-a716-446655440000';

    public function testConstructorInitializesProperties(): void
    {
        $identifier = \Mockery::mock(FieldIdentifier::class);
        $field = new ProductField($identifier, 'Example');

        $this->assertSame($identifier, $field->getIdentifier());
        $this->assertSame('Example', $field->getValue());
        $this->assertInstanceOf(ArrayCollection::class, $field->getTranslations());
        $this->assertCount(0, $field->getTranslations());
    }

    public function testConstructorDefaultsValueToNull(): void
    {
        $identifier = \Mockery::mock(FieldIdentifier::class);
        $field = new ProductField($identifier);

        $this->assertSame($identifier, $field->getIdentifier());
        $this->assertNull($field->getValue());
    }

    public function testSetIdentifier(): void
    {
        $field = new ProductField(
            \Mockery::mock(FieldIdentifier::class),
        );
        $identifier = \Mockery::mock(FieldIdentifier::class);

        $result = $field->setIdentifier($identifier);

        $this->assertSame($field, $result);
        $this->assertSame($identifier, $field->getIdentifier());
    }

    public function testSetValue(): void
    {
        $field = new ProductField(
            \Mockery::mock(FieldIdentifier::class),
        );

        $result = $field->setValue('Description');

        $this->assertSame($field, $result);
        $this->assertSame('Description', $field->getValue());

        $field->setValue(null);

        $this->assertNull($field->getValue());
    }

    public function testSetTranslations(): void
    {
        $field = new ProductField(
            \Mockery::mock(FieldIdentifier::class),
        );
        $translations = \Mockery::mock(Collection::class);

        $result = $field->setTranslations($translations);

        $this->assertSame($field, $result);
        $this->assertSame($translations, $field->getTranslations());
    }

    public function testGetTranslationsReturnsConfiguredTranslations(): void
    {
        $field = new ProductField(
            \Mockery::mock(FieldIdentifier::class),
        );
        $translation = \Mockery::mock(ProductTranslation::class);
        /** @var Collection<int, ProductTranslation> $translations */
        $translations = new ArrayCollection([$translation]);

        $field->setTranslations($translations);

        $this->assertSame($translations, $field->getTranslations());
        $this->assertSame($translation, $field->getTranslations()->first());
    }

    public function testValidationCascadesToIdentifier(): void
    {
        $field = new ProductField(
            new FieldIdentifier(ProductFieldIdentifierType::CODE, ''),
        );

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($field);

        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationCascadesToTranslations(): void
    {
        $field = new ProductField(
            new FieldIdentifier(ProductFieldIdentifierType::ID, self::ID),
        );
        $field->setTranslations(new ArrayCollection([
            new ProductTranslation(''),
        ]));

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($field);

        $this->assertGreaterThan(0, $violations->count());
    }
}
