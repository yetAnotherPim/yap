<?php

declare(strict_types=1);

namespace App\Tests\helper\Trait\Unit;

use App\Entity\Locale\Locale;
use App\Entity\Translation\Title\TitleTranslationInterface;
use App\Entity\Translation\Title\TranslatableTitleInterface;

/**
 * @template TTranslation of TitleTranslationInterface
 * @template TTarget of TranslatableTitleInterface<TTranslation>
 */
trait TitleTranslationTrait
{
    /**
     * @return class-string<TTarget>
     */
    abstract protected function getTargetClass(): string;

    abstract protected function getTargetSetter(): string;

    /**
     * @return class-string<TTranslation>
     */
    abstract protected function getTranslationClass(): string;

    public function testGetTranslatedTitleReturnsTranslationForLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        /** @var TTranslation&\Mockery\MockInterface $translation */
        $translation = \Mockery::mock(TitleTranslationInterface::class);
        $translation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($locale);

        $translation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('Deutscher Titel');

        $type = new ($this->getTargetClass())();
        $type->setTitle('Default title');

        $translation
            ->shouldReceive($this->getTargetSetter())
            ->once()
            ->with($this->getTargetClass());

        $type->addTitleTranslation($translation);

        $this->assertSame(
            'Deutscher Titel',
            $type->getTranslatedTitle('de_DE'),
        );
    }

    public function testGetTranslatedTitleReturnsTitleWhenTranslationDoesNotExist(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('fr_FR');

        /** @var TTranslation&\Mockery\MockInterface $translation */
        $translation = \Mockery::mock($this->getTranslationClass());
        $translation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($locale);

        $translation
            ->shouldReceive($this->getTargetSetter())
            ->once()
            ->with($this->getTargetClass());

        $translation
            ->shouldNotReceive('getValue');

        $type = new ($this->getTargetClass())();
        $type->setTitle('Default title');
        $type->addTitleTranslation($translation);

        $this->assertSame(
            'Default title',
            $type->getTranslatedTitle('de_DE'),
        );
    }

    public function testGetTranslatedTitleReturnsTitleWhenTranslationValueIsNull(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        /** @var TTranslation&\Mockery\MockInterface $translation */
        $translation = \Mockery::mock($this->getTranslationClass());
        $translation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($locale);

        $translation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(null);

        $translation
            ->shouldReceive($this->getTargetSetter())
            ->once()
            ->with($this->getTargetClass());

        $type = new ($this->getTargetClass())();
        $type->setTitle('Default title');
        $type->addTitleTranslation($translation);

        $this->assertSame(
            'Default title',
            $type->getTranslatedTitle('de_DE'),
        );
    }

    public function testCreateTitleTranslation(): void
    {
        $type = new ($this->getTargetClass())();
        $result = $type->createTitleTranslation();

        $this->assertInstanceOf(TitleTranslationInterface::class, $result);
    }
}
