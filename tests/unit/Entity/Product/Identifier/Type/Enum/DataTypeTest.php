<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Identifier\Type\Enum;

use App\Entity\Product\Identifier\Type\Enum\DataType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataType::class)]
class DataTypeTest extends TestCase
{
    public function testTrans(): void
    {
        $dataType = DataType::String;
        $translatorMock = \Mockery::mock(\Symfony\Contracts\Translation\TranslatorInterface::class);

        $translatorMock
            ->shouldReceive('trans')
            ->once()
            ->with('identifier_type.data_type.string', [], 'admin', 'de_DE')
            ->andReturn('Zeichenkette');

        $this->assertEquals('Zeichenkette', $dataType->trans($translatorMock, 'de_DE'));
    }
}
