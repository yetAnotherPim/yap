<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate\Message\Dto;

use App\Messenger\ProductUpdate\Message\Dto\FieldIdentifier;
use App\Messenger\ProductUpdate\Message\Enum\ProductFieldIdentifierType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Validation;

#[CoversClass(FieldIdentifier::class)]
class FieldIdentifierTest extends MockeryTestCase
{
    private const string ID = '550e8400-e29b-41d4-a716-446655440000';

    public function testConstructorInitializesProperties(): void
    {
        $identifier = new FieldIdentifier(
            ProductFieldIdentifierType::ID,
            self::ID,
        );

        $this->assertSame(ProductFieldIdentifierType::ID, $identifier->getType());
        $this->assertSame(self::ID, $identifier->getValue());
    }

    public function testSetType(): void
    {
        $identifier = new FieldIdentifier(
            ProductFieldIdentifierType::ID,
            self::ID,
        );

        $result = $identifier->setType(ProductFieldIdentifierType::CODE);

        $this->assertSame($identifier, $result);
        $this->assertSame(ProductFieldIdentifierType::CODE, $identifier->getType());
    }

    public function testSetValue(): void
    {
        $identifier = new FieldIdentifier(
            ProductFieldIdentifierType::ID,
            self::ID,
        );

        $result = $identifier->setValue('description');

        $this->assertSame($identifier, $result);
        $this->assertSame('description', $identifier->getValue());
    }

    public function testValidationRejectsBlankValue(): void
    {
        $identifier = new FieldIdentifier(
            ProductFieldIdentifierType::CODE,
            '',
        );

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($identifier);

        $this->assertGreaterThan(0, $violations->count());
    }

    public function testValidationAcceptsValidIdentifier(): void
    {
        $identifier = new FieldIdentifier(
            ProductFieldIdentifierType::ID,
            self::ID,
        );

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($identifier);

        $this->assertCount(0, $violations);
    }
}
