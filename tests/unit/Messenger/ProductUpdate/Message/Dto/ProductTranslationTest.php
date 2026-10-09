<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate\Message\Dto;

use App\Messenger\ProductUpdate\Message\Dto\ProductTranslation;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Validation;

#[CoversClass(ProductTranslation::class)]
class ProductTranslationTest extends MockeryTestCase
{
    public function testConstructorInitializesProperties(): void
    {
        $translation = new ProductTranslation('de_DE', 'Beschreibung');

        $this->assertSame('de_DE', $translation->getLocaleId());
        $this->assertSame('Beschreibung', $translation->getValue());
    }

    public function testConstructorDefaultsValueToNull(): void
    {
        $translation = new ProductTranslation('de_DE');

        $this->assertSame('de_DE', $translation->getLocaleId());
        $this->assertNull($translation->getValue());
    }

    public function testSetLocaleId(): void
    {
        $translation = new ProductTranslation('de_DE');

        $result = $translation->setLocaleId('en_US');

        $this->assertSame($translation, $result);
        $this->assertSame('en_US', $translation->getLocaleId());
    }

    public function testSetValue(): void
    {
        $translation = new ProductTranslation('de_DE');

        $result = $translation->setValue('Beschreibung');

        $this->assertSame($translation, $result);
        $this->assertSame('Beschreibung', $translation->getValue());

        $translation->setValue(null);

        $this->assertNull($translation->getValue());
    }

    public function testValidationRejectsBlankLocaleId(): void
    {
        $translation = new ProductTranslation('');

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($translation);

        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationAcceptsNullValue(): void
    {
        $translation = new ProductTranslation('de_DE');

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($translation);

        $this->assertCount(0, $violations);
    }
}
