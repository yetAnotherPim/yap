<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Title\Translation\Resolver;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Form\Title\Translation\Resolver\TitleTranslationResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TitleTranslationResolver::class)]
class TitleTranslationResolverTest extends MockeryTestCase
{
    private TitleTranslationResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new TitleTranslationResolver();

        parent::setUp();
    }

    public function testResolveReturnsNullWhenEntityIsNull(): void
    {
        $locale = \Mockery::mock(Locale::class);

        /** @var TranslatableTitleInterface<FieldTypeTitleTranslation>|null $entity */
        $entity = null;

        $result = $this->resolver->resolve(
            $entity,
            $locale,
        );

        $this->assertNull($result);
    }

    public function testResolveReturnsExistingTranslation(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $translation = \Mockery::mock(FieldTypeTitleTranslation::class);
        $translation
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn($locale);

        /** @var TranslatableTitleInterface<FieldTypeTitleTranslation>&MockInterface $entity */
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $entity
            ->shouldReceive('getTitleTranslations')
            ->once()
            ->andReturn(
                new ArrayCollection([
                    $translation,
                ]),
            );

        $entity
            ->shouldNotReceive('createTitleTranslation');

        $result = $this->resolver->resolve(
            $entity,
            $locale,
        );

        $this->assertSame($translation, $result);
    }

    public function testResolveCreatesTranslationWhenNoneExists(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $translation = \Mockery::mock(FieldTypeTitleTranslation::class);

        $translation
            ->shouldReceive('setLocale')
            ->once()
            ->with($locale)
            ->andReturnSelf();

        /** @var TranslatableTitleInterface<FieldTypeTitleTranslation>&MockInterface $entity */
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $entity
            ->shouldReceive('getTitleTranslations')
            ->once()
            ->andReturn(new ArrayCollection());

        $entity
            ->shouldReceive('createTitleTranslation')
            ->once()
            ->andReturn($translation);

        $result = $this->resolver->resolve(
            $entity,
            $locale,
        );

        $this->assertSame($translation, $result);
    }
}
