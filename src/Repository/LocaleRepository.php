<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Locale\Locale;
use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Locale>
 */
class LocaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Locale::class);
    }

    /**
     * @return Collection<int, Locale>
     */
    public function getAvailableLocales(): Collection
    {
        return new ArrayCollection($this->findBy([], ['default' => 'DESC']));
    }

    /**
     * @return string[]
     */
    public function getAvailableLocaleIds(): array
    {
        /** @var string[] $localeIds */
        $localeIds = $this->createQueryBuilder('locale')
            ->select('locale.id')
            ->where('locale.default = :default')
            ->setParameter('default', false)
            ->getQuery()
            ->getSingleColumnResult();

        return $localeIds;
    }

    /**
     * @throws DefaultLocaleIsNotDefinedException
     */
    public function getDefaultLocale(): Locale
    {
        $defaultLocale = $this->findOneBy(['default' => true]);

        if (null !== $defaultLocale) {
            return $defaultLocale;
        }

        throw new DefaultLocaleIsNotDefinedException();
    }

    public function setDefaultLocale(string $id): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->update(Locale::class, 'locale')
            ->set('locale.default', ':isDefault')
            ->setParameter('isDefault', false)
            ->getQuery()
            ->execute();

        $this->getEntityManager()
            ->createQueryBuilder()
            ->update(Locale::class, 'locale')
            ->set('locale.default', ':isDefault')
            ->where('locale.id = :id')
            ->setParameter('isDefault', true)
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
