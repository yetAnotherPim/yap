<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\Locale\Locale;
use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
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
            ->with([], ['default' => 'DESC'])
            ->andReturn([$locale1, $locale2]);

        $result = $repository->getAvailableLocales();

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($locale1, $result->first());
        $this->assertSame($locale2, $result->last());
    }

    public function testGetAvailableLocaleIds(): void
    {
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $classMetadata = new ClassMetadata(Locale::class);
        $queryBuilderMock = \Mockery::mock(QueryBuilder::class);

        $repository = \Mockery::mock(LocaleRepository::class, [$registryMock])
            ->makePartial();

        $registryMock
            ->shouldReceive('getManagerForClass')
            ->once()
            ->andReturn($entityManagerMock);

        $entityManagerMock
            ->shouldReceive('getClassMetadata')
            ->once()
            ->andReturn($classMetadata);

        $entityManagerMock
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('select')
            ->once()
            ->with('locale')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('from')
            ->once()
            ->with(Locale::class, 'locale', null)
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('select')
            ->once()
            ->with('locale.id')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('where')
            ->once()
            ->with('locale.default = :default')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('setParameter')
            ->once()
            ->with('default', false)
            ->andReturnSelf();

        $queryMock = \Mockery::mock(Query::class);

        $queryBuilderMock
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($queryMock);

        $queryMock
            ->shouldReceive('getSingleColumnResult')
            ->once()
            ->andReturn(['de_DE', 'en_GB']);

        $result = $repository->getAvailableLocaleIds();

        $this->assertIsArray($result);
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
}
