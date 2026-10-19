<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\Locale\Locale;
use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(LocaleRepository::class)]
#[UsesClass(DefaultLocaleIsNotDefinedException::class)]
class LocaleRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new LocaleRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(LocaleRepository::class, $repository);
    }

    public function testGetAvailableLocales(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $locale1 = \Mockery::mock(Locale::class);
        $locale2 = \Mockery::mock(Locale::class);

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findBy')
            ->once()
            ->with([], ['default' => 'DESC', 'id' => 'ASC'])
            ->andReturn([$locale1, $locale2]);

        $result = $repository->getAvailableLocales();

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($locale1, $result->first());
        $this->assertSame($locale2, $result->last());
    }

    public function testGetAvailableInterfaceTranslationLocales(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $locale1 = \Mockery::mock(Locale::class);
        $locale2 = \Mockery::mock(Locale::class);

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findBy')
            ->once()
            ->with(['default' => false], ['id' => 'ASC'])
            ->andReturn([$locale1, $locale2]);

        $result = $repository->getAvailableInterfaceTranslationLocales();

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(2, $result);
    }

    public function testGetAvailableContentTranslationLocales(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $locale1 = \Mockery::mock(Locale::class);
        $locale2 = \Mockery::mock(Locale::class);

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findBy')
            ->once()
            ->with(['contentTranslationEnabled' => true], ['id' => 'ASC'])
            ->andReturn([$locale1, $locale2]);

        $result = $repository->getAvailableContentTranslationLocales();

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(2, $result);
    }

    public function testGetDefaultLocale(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $locale = \Mockery::mock(Locale::class);

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['default' => true])
            ->andReturn($locale);

        $this->assertSame($locale, $repository->getDefaultLocale());
    }

    public function testGetDefaultLocaleThrowsException(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['default' => true])
            ->andReturnNull();

        $this->expectException(DefaultLocaleIsNotDefinedException::class);

        $repository->getDefaultLocale();
    }

    public function testSetDefaultLocale(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $entityManager->shouldReceive('createQueryBuilder')
            ->twice()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('update')->twice()->andReturnSelf();
        $queryBuilder->shouldReceive('set')->twice()->andReturnSelf();
        $queryBuilder->shouldReceive('where')->once()->andReturnSelf();
        $queryBuilder->shouldReceive('setParameter')->times(3)->andReturnSelf();
        $queryBuilder->shouldReceive('getQuery')->twice()->andReturn($query);

        $query
            ->shouldReceive('execute')
            ->twice();

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository->shouldAllowMockingProtectedMethods();
        $repository->shouldReceive('getEntityManager')
            ->twice()
            ->andReturn($entityManager);

        $repository->setDefaultLocale('de_DE');
    }

    public function testExtractLocaleIdsWithProvidedCollection(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $repository = new LocaleRepository($registryMock);

        $locale1 = \Mockery::mock(Locale::class);
        $locale1->shouldReceive('getId')->andReturn('de_DE');

        $locale2 = \Mockery::mock(Locale::class);
        $locale2->shouldReceive('getId')->andReturn('en_GB');

        /** @var ArrayCollection<int, Locale> $collection */
        $collection = new ArrayCollection([$locale1, $locale2]);

        $result = $repository->extractLocaleIds($collection);

        $this->assertSame(['de_DE', 'en_GB'], $result);
    }

    public function testExtractLocaleIdsWithoutProvidedCollection(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $locale1 = \Mockery::mock(Locale::class);
        $locale1->shouldReceive('getId')->andReturn('fr_FR');

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('getAvailableInterfaceTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([$locale1]));

        $result = $repository->extractLocaleIds();

        $this->assertSame(['fr_FR'], $result);
    }

    public function testGetContentTranslationLocaleById(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $locale = \Mockery::mock(Locale::class);

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findOneBy')
            ->once()
            ->with([
                'id' => 'de_DE',
                'contentTranslationEnabled' => true,
            ])
            ->andReturn($locale);

        $this->assertSame($locale, $repository->getContentTranslationLocaleById('de_DE'));
    }

    public function testGetContentTranslationLocaleByIdReturnsNullWhenNotFound(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findOneBy')
            ->once()
            ->with([
                'id' => 'de_DE',
                'contentTranslationEnabled' => true,
            ])
            ->andReturnNull();

        $this->assertNull($repository->getContentTranslationLocaleById('de_DE'));
    }
}
