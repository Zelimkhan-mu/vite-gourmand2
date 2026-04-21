<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findByFilters(?string $maxPrice, ?string $minPrice, array $themeIds, array $regimeIds, ?string $minPersons): array
    {
        $qb = $this->createQueryBuilder('m');

        if ($maxPrice !== null && $maxPrice !== '') {
            $qb->andWhere('m.basePrice <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        if ($minPrice !== null && $minPrice !== '') {
            $qb->andWhere('m.basePrice >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if (!empty($themeIds)) {
            $qb->andWhere('m.theme IN (:themeIds)')
                ->setParameter('themeIds', $themeIds);
        }

        if (!empty($regimeIds)) {
            $qb->andWhere('m.regime IN (:regimeIds)')
                ->setParameter('regimeIds', $regimeIds);
        }

        if ($minPersons !== null && $minPersons !== '') {
            $qb->andWhere('m.minPersons >= :minPersons')
                ->setParameter('minPersons', $minPersons);
        }

        return $qb->getQuery()->getResult();
    }
}
