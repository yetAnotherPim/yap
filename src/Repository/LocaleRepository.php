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
        return new ArrayCollection($this->findBy([], ['isDefault' => 'DESC']));
    }

    /**
     * @throws DefaultLocaleIsNotDefinedException
     */
    public function getDefaultLocale(): Locale
    {
        $defaultLocale = $this->findOneBy(['isDefault' => true]);

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
            ->set('locale.isDefault', ':isDefaultFalse')
            ->setParameter('isDefaultFalse', false)
            ->getQuery()
            ->execute();

        $this->getEntityManager()
            ->createQueryBuilder()
            ->update(Locale::class, 'locale')
            ->set('locale.isDefault', ':isDefaultTrue')
            ->where('locale.id = :id')
            ->setParameter('isDefaultTrue', true)
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
