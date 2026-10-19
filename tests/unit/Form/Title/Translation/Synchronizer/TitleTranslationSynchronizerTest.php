<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Title\Translation\Synchronizer;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Entity\Translation\Title\TranslatableTitleInterface;
use App\Form\Title\Translation\Resolver\TitleTranslationResolver;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TitleTranslationSynchronizer::class)]
class TitleTranslationSynchronizerTest extends MockeryTestCase
{
    /** @var LocaleRepository&MockInterface */
    private LocaleRepository $localeRepositoryMock;

    /** @var TitleTranslationResolver&MockInterface */
    private TitleTranslationResolver $titleTranslationResolverMock;

    private TitleTranslationSynchronizer $synchronizer;

    protected function setUp(): void
    {
        $this->localeRepositoryMock = \Mockery::mock(LocaleRepository::class);
        $this->titleTranslationResolverMock = \Mockery::mock(
            TitleTranslationResolver::class,
        );

        $this->synchronizer = new TitleTranslationSynchronizer(
            $this->localeRepositoryMock,
            $this->titleTranslationResolverMock,
        );

        parent::setUp();
    }

    public function testSynchronizeDoesNothingWhenRequestDataIsNull(): void
    {
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $this->localeRepositoryMock
            ->shouldNotReceive('getAvailableInterfaceTranslationLocales');

        $this->titleTranslationResolverMock
            ->shouldNotReceive('resolve');

        $this->synchronizer->synchronizeTitleTranslations(
            $entity,
            null,
        );
    }

    public function testSynchronizeDoesNothingWhenResolverReturnsNull(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $locale)
            ->andReturn(null);

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $entity->shouldNotReceive('addTitleTranslation');
        $entity->shouldNotReceive('removeTitleTranslation');

        $this->synchronizer->synchronizeTitleTranslations(
            $entity,
            [
                'title-de_DE' => 'Deutscher Titel',
            ],
        );
    }

    public function testSynchronizeRemovesTranslationWhenValueIsEmpty(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(FieldTypeTitleTranslation::class);
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $locale)
            ->andReturn($translation);

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $translation
            ->shouldNotReceive('setValue');

        $entity
            ->shouldReceive('removeTitleTranslation')
            ->once()
            ->with($translation);

        $entity
            ->shouldNotReceive('addTitleTranslation');

        $this->synchronizer->synchronizeTitleTranslations(
            $entity,
            [
                'title-de_DE' => '',
            ],
        );
    }

    public function testSynchronizeSetsAndAddsTranslation(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(FieldTypeTitleTranslation::class);
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $locale)
            ->andReturn($translation);

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with('Deutscher Titel');

        $entity
            ->shouldReceive('addTitleTranslation')
            ->once()
            ->with($translation);

        $entity
            ->shouldNotReceive('removeTitleTranslation');

        $this->synchronizer->synchronizeTitleTranslations(
            $entity,
            [
                'title-de_DE' => 'Deutscher Titel',
            ],
        );
    }

    public function testSynchronizeTrimsValue(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(FieldTypeTitleTranslation::class);
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $locale)
            ->andReturn($translation);

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $translation
            ->shouldReceive('setValue')
            ->once()
            ->with('Deutscher Titel');

        $entity
            ->shouldReceive('addTitleTranslation')
            ->once()
            ->with($translation);

        $this->synchronizer->synchronizeTitleTranslations(
            $entity,
            [
                'title-de_DE' => '  Deutscher Titel  ',
            ],
        );
    }

    public function testSynchronizeRemovesTranslationWhenValueIsNotString(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $translation = \Mockery::mock(FieldTypeTitleTranslation::class);
        $entity = \Mockery::mock(TranslatableTitleInterface::class);

        $this->localeRepositoryMock
            ->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale]));

        $this->titleTranslationResolverMock
            ->shouldReceive('resolve')
            ->once()
            ->with($entity, $locale)
            ->andReturn($translation);

        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $translation
            ->shouldNotReceive('setValue');

        $entity
            ->shouldReceive('removeTitleTranslation')
            ->once()
            ->with($translation);

        $entity
            ->shouldNotReceive('addTitleTranslation');

        $this->synchronizer->synchronizeTitleTranslations(
            $entity,
            [
                'title-de_DE' => 123,
            ],
        );
    }
}
